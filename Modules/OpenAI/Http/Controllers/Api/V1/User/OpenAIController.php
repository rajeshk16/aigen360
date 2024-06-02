<?php

/**
 * @author TechVillage <support@techvill.org>
 *
 * @contributor Kabir Ahmed <[kabir.techvill@gmail.com]>
 *
 * @created 26-03-2023
 */

namespace Modules\OpenAI\Http\Controllers\Api\V1\User;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ModelTraits\Filterable;
use Modules\OpenAI\Entities\{
    Chat, ChatConversation, Content
};
use Modules\OpenAI\Http\Controllers\Customer\CodeController;
use Modules\OpenAI\Services\{
    UseCaseTemplateService,
    ContentService,
    ImageService,
    CodeService,
    ChatService,
    SpeechToTextService
};
use Modules\OpenAI\Http\Resources\{
    ChatConversationResource,
    ChatResource,
    ContentResource
};
use Modules\OpenAI\Http\Requests\ToggleFavoriteBookmarkRequest;

class OpenAIController extends Controller
{
    /**
     * Use Filterable trait.
     */
    use Filterable;

    /**
     * Content Service
     *
     * @var object
     */
    protected $contentService;

    /**
     * Constructor.
     *
     * @param  ContentService  $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * List all content.
     *
     * @param  Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $configs        = $this->initialize([], $request->all());
        $contentServices = $this->contentService;
        $contents = $contentServices->model()->orderBy('id', 'desc');

        if (auth('api')->user()->role()->type !== 'admin') {
            $contents = $contents->where('user_id', auth('api')->user()->id);
        }

        if (count(request()->query()) > 0) {
            $contents = $contents->filter();
        }

        $contents = $contents->paginate($configs['rows_per_page']);
        $responseData = ContentResource::collection($contents)->response()->getData(true);

        return $this->response($responseData);
    }

    /**
     * Display a content view.
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function view($slug)
    {
        $contents = $this->contentService->contentBySlug($slug);
        if ($contents) {
            return $this->okResponse(new ContentResource($contents));
        }

        return $this->notFoundResponse([], __('No :x found.', ['x' => __('Content')]));
    }

     /**
     * Update a content.
     *
     * @param  Request  $request
     * @param  string  $slug
     * @return JsonResponse
     */
    public function update(Request $request, $slug)
    {
        $contents = $this->contentService->model();
        $content = $contents->where('slug', $slug)->whereNull('parent_id')->first();

        if (empty($content)) {
            return $this->notFoundResponse([], __('No :x found.', ['x' => __('Content')]));
        }

        if ($this->contentService->createVersion($content, $request->only('content'))) {
            $content = $this->contentService->model()->where('slug', $slug)->latest()->first();

            return $this->okResponse(new ContentResource($content), __('Content Updated successfully'));
        }
    }

    /**
     * Delete a content.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        if (! is_numeric($id)) {
            return $this->forbiddenResponse([], __('Invalid Request!'));
        }

        $delete =  json_decode(json_encode($this->contentService->delete($id)));

        return $delete->original->status == 'fail' ? $this->badRequestResponse([], $delete->original->message) : $this->okResponse([], $delete->original->message);
    }

    /**
     * Send a request to the API.
     *
     * @param  Request  $request
     * @param  ContentService  $contentService
     * @return JsonResponse
     */
    public function ask(Request $request, ContentService $contentService)
    {
        $subscription = null;
        $userId = $contentService->getCurrentMemberUserId('meta', null);
        if (! subscription('isAdminSubscribed')) {
            $userStatus = $contentService->checkUserStatus($userId, 'meta');
            if ($userStatus['status'] == 'fail') {
                return $this->unprocessableResponse([
                    'response' => $userStatus['message'],
                    'status' => 'failed',
                ]);
            }
            $validation = subscription('isValidSubscription', $userId, 'word', $request->useCase);
            $subscription = subscription('getUserSubscription', $userId);
            if ($validation['status'] == 'fail' && ! auth()->user()->hasCredit('word')) {
                return $this->unprocessableResponse([
                    'response' => $validation['message'],
                    'status' => 'failed',
                ]);
            }
        }
        $request = app('Modules\OpenAI\Http\Requests\ContentStoreRequest')->safe();
        $useCase = $contentService->useCasebySlug($request->useCase);
        $templateService = new UseCaseTemplateService($useCase->prompt);
        $templateService->setVariables(json_decode($request->questions, true));
        $model = preference('ai_model');

        return request()->hasHeader('stream-data')
            ? $contentService->streamResponse($model, $contentService, $templateService, $subscription, $useCase, $userId)
            : $contentService->generalResponse($model, $contentService, $templateService, $subscription, $useCase, $userId);
    }

    /**
     * Create an image from a prompt.
     *
     * @param  Request  $request
     * @param  ImageService  $imageService
     * @return JsonResponse
     */
    public function image(Request $request, ImageService $imageService)
    {
        $subscription = null;
        $userId = $this->contentService->getCurrentMemberUserId('meta', null);
        if (! subscription('isAdminSubscribed')) {
            $userStatus = $this->contentService->checkUserStatus($userId, 'meta');
            if ($userStatus['status'] == 'fail') {
                return $this->unprocessableResponse([
                    'response' => $userStatus['message'],
                    'status' => 'failed',
                ]);
            }
            $validation = subscription('isValidSubscription', $userId, 'image');
            $subscription = subscription('getUserSubscription', $userId);
            if ($validation['status'] == 'fail' && ! auth()->user()->hasCredit('image')) {
                return $this->unprocessableResponse([
                    'response' => $validation['message'],
                    'status' => 'failed',
                ]);
            }
            if (filled($request->resulation) && ! subscription('isValidResolution', $userId, $request->resulation)
                && ! auth()->user()->hasCredit('image')) {
                return $this->unprocessableResponse([
                    'response' => __('This resolution is not available in your plan.'),
                    'status' => 'failed',
                ]);
            }
        }

        try {
            $imageUrls = $imageService->createImage($request->all());
            if (isset($imageUrls['status']) && $imageUrls['status'] == 'failed' || is_null($imageUrls)) {
                return $this->unprocessableResponse($imageUrls);
            } else {
                $data['balanceReduce'] = 'onetime';
                if (! subscription('isAdminSubscribed') || auth()->user()->hasCredit('image')) {
                    $variant = $request->variant ?? 1;
                    $increment = subscription('usageIncrement', $subscription?->id, 'image', $variant, $userId);
                    if ($increment && $userId != auth()->user()->id) {
                        $imageService->storeTeamMeta($variant);
                    }
                    $data['balanceReduce'] = app('user_balance_reduce');
                }
                $data['imageUrls'] = $imageUrls;

                return $this->successResponse($data);
            }
        } catch (Exception $e) {
            $data = [
                'response' => $e->getMessage(),
                'status' => 'failed',
            ];

            return $this->unprocessableResponse($data);
        }
    }

    /**
     * Create code from a prompt.
     *
     * @param  Request  $request
     * @param  CodeService  $codeService
     * @param  CodeController  $codeController
     * @return JsonResponse
     */
    public function code(Request $request, CodeService $codeService, CodeController $codeController)
    {
        $subscription = null;
        $userId = $this->contentService->getCurrentMemberUserId('meta', null);
        if (! subscription('isAdminSubscribed')) {
            $userStatus = $this->contentService->checkUserStatus($userId, 'meta');
            if ($userStatus['status'] == 'fail') {
                return $this->unprocessableResponse([
                    'response' => $userStatus['message'],
                    'status' => 'failed',
                ]);
            }
            $validation = subscription('isValidSubscription', $userId, 'word');
            $subscription = subscription('getUserSubscription', $userId);

            if ($validation['status'] == 'fail' && ! auth()->user()->hasCredit('word')) {
                return $this->unprocessableResponse([
                    'response' => $validation['message'],
                    'status' => 'failed',
                ]);
            }
        }

        try {
            $code = $codeService->createCode($request->all());
            if (! empty($code['error'])) {
                $message = '';

                if ($code['error']['message'] != '') {
                    $message = $code['error']['message'];
                } elseif ($code['error']['code']) {
                    $message = str_replace('_', ' ', $code['error']['code']);
                }

                return $this->unprocessableResponse([], $message);
            }
            $words = subscription('tokenToWord', $code['usage']['total_tokens']);
            $response = $codeController->saveCode($code);
            $response['usage']['words'] = $words;
            $response['balanceReduce'] = 'onetime';
            if (! subscription('isAdminSubscribed') || auth()->user()->hasCredit('word')) {
                $increment = subscription('usageIncrement', $subscription?->id, 'word', $words, $userId);
                $response['balanceReduce'] = app('user_balance_reduce');
                if ($increment  && $userId != auth()->user()->id) {
                    $codeService->storeTeamMeta($words);
                }
            }

            return $this->successResponse($response);

        } catch (Exception $e) {
            $data = [
                'response' => $e->getMessage(),
                'status' => 'failed',
            ];

            return $this->unprocessableResponse($data);
        }
    }

    /**
     * Start a chat session.
     *
     * @param  Request  $request
     * @param  ChatService  $chatService
     * @param  ChatController  $chatController
     * @return JsonResponse
     */
    public function chat(Request $request, ChatService $chatService, ChatController $chatController)
    {
        $subscription = null;
        $userId = $this->contentService->getCurrentMemberUserId('meta', null);

        $request['botId'] = (isset($request->botId) && ! empty($request->botId)) ? $request->botId : $chatService->assistant()->id;

        if (! subscription('isAdminSubscribed')) {
            $userStatus = $this->contentService->checkUserStatus($userId, 'meta');
            if ($userStatus['status'] == 'fail') {
                return $this->unprocessableResponse([
                    'response' => $userStatus['message'],
                    'status' => 'failed',
                ]);
            }

            $validation = subscription('isValidSubscription', $userId, 'word', null, $request->botId);
            $subscription = subscription('getUserSubscription', $userId);
            if ($validation['status'] == 'fail' && ! auth()->user()->hasCredit('word')) {
                return $this->unprocessableResponse([
                    'response' => $validation['message'],
                    'status' => 'failed',
                ]);
            }
        }
        app('Modules\OpenAI\Http\Requests\ChatRequest')->safe();

        return request()->hasHeader('stream-data') 
            ? $chatService->streamResponse($request->all(), $subscription, $chatController, $chatService, $userId)
            : $chatService->generalResponse($request->all(), $subscription, $chatController, $chatService, $userId);
    }

    /**
     * Retrieve chat conversation data.
     *
     * @param  Request  $request
     * @param  ChatService  $chatService
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function chatConversation(Request $request, ChatService $chatService)
    {
        $configs = $this->initialize([], $request->all());

        if ($request->allBots === 'true') {
            $chatConversation = ChatConversation::with(['user', 'user.metas'])->where('user_id', auth('api')->user()->id)->orderBy('id', 'DESC');
        } else {
            $botId = $request->botId ?? $chatService->assistant()->id;
            $chatConversation = ChatConversation::with(['user', 'user.metas'])->where('user_id', auth('api')->user()->id)->where('bot_id', $botId)->orderBy('id', 'DESC');
        }

        if (! $chatConversation->count()) {
            return $this->okResponse([], __('No data found'));
        }

        return $this->response(ChatConversationResource::collection($chatConversation->paginate($configs['rows_per_page']))
            ->response()->getData(true));
    }

    /**
     * View chat history.
     *
     * @param  Request  $request
     * @param  int  $chatConversationId
     * @return array
     */
    public function history(Request $request, $chatConversationId)
    {
        $configs = $this->initialize([], $request->all());
        $chatInfo = [];
        $chat = Chat::with(['user', 'chatBot', 'user.metas'])->where('chat_conversation_id', $chatConversationId)->where('user_id', auth('api')->user()->id)->exists();

        if ($chat) {

            $chat = Chat::with(['user', 'chatBot', 'user.metas'])->where('chat_conversation_id', $chatConversationId)->orderBy('id', 'DESC');
            $botId = ChatConversation::with(['user', 'user.metas'])->where('id', $chatConversationId)->value('bot_id');

            $chatInfo = [
                'chatId' => (int) $chatConversationId,
                'botId' => $botId,
            ];

        } else {

            $chat = [];

        }

        if (empty($chat)) {
            return $this->notFoundResponse();
        }

        return $this->response(ChatResource::collection($chat->paginate($configs['rows_per_page']))->response()->getData(true) + ['chatInfo' => $chatInfo]);
    }

    /**
     * Convert speech to text.
     *
     * @param  Request  $request
     * @param  SpeechToTextService  $speechToTextService
     * @return mixed
     */
    public function speechToText(Request $request, SpeechToTextService $speechToTextService)
    {
        $subscription = null;
        $userId = $this->contentService->getCurrentMemberUserId('meta', null);
        if (! subscription('isAdminSubscribed')) {
            $userStatus = $this->contentService->checkUserStatus($userId, 'meta');
            if ($userStatus['status'] == 'fail') {
                return $this->unprocessableResponse([
                    'response' => $userStatus['message'],
                    'status' => 'failed',
                ]);
            }
            $validation = subscription('isValidSubscription', $userId, 'minute');
            $subscription = subscription('getUserSubscription', $userId);

            if ($validation['status'] == 'fail' && ! auth()->user()->hasCredit('minute')) {
                return $this->unprocessableResponse([
                    'response' => $validation['message'],
                    'status' => 'failed',
                ]);
            }
        }

        try {
            $generateText = $speechToTextService->generateText($request->all());
            if (empty($generateText['error'])) {

                $seconds = $request->duration;
                $minutes = $seconds / 60;

                $response = $speechToTextService->save($generateText);
                $response['usage']['minutes'] = $minutes;

                $response['balanceReduce'] = 'onetime';
                if (! subscription('isAdminSubscribed') || auth()->user()->hasCredit('minute')) {
                    $increment = subscription('usageIncrement', $subscription?->id, 'minute', $minutes, $userId);
                    $response['balanceReduce'] = app('user_balance_reduce');
                    if ($increment  && $userId != auth()->user()->id) {
                        $speechToTextService->storeTeamMeta($minutes);
                    }
                }

                return $this->successResponse($response);
            }

            return $this->unprocessableResponse([
                'response' => $generateText['error']['message'],
                'status' => 'failed',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Toggle content bookmark.
     *
     * @param  ToggleFavoriteBookmarkRequest  $request
     * @return mixed
     */
    public function contentTogglebookmark(ToggleFavoriteBookmarkRequest $request): mixed
    {
        $authUser = auth()->user();
        $bookmarksArray = $authUser->document_bookmarks_openai ?? [];
        $allContentIds = Content::where('user_id', auth('api')->user()->id)->pluck('id')->toArray();

        try {
            if (! in_array($request->content_id, $allContentIds)) {
                throw new Exception();
            }

            if ($request->toggle_state == 'true') {
                $bookmarksArray = array_unique(array_merge($bookmarksArray, [$request->content_id]), SORT_NUMERIC);
                $message = __('Successfully bookmarked!');
            } else {
                $bookmarksArray = array_diff($bookmarksArray, [$request->content_id]);
                $message = __('Successfully removed from bookmarks!');
            }

            $authUser->document_bookmarks_openai = $bookmarksArray;
            $authUser->save();
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __('Failed to update bookmarks! Please try again later.'));
        }

        return $this->okResponse([], $message);
    }
}
