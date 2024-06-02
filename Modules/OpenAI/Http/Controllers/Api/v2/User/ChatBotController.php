<?php

namespace Modules\OpenAI\Http\Controllers\Api\v2\User;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\OpenAI\Transformers\Api\v2\CharacterBotChat\ChatBotResource;
use Modules\OpenAI\Services\v2\CharacterChatBotService;
use Modules\OpenAI\Entities\ChatBot;
use App\Http\Controllers\Controller;
use Illuminate\Http\{
    JsonResponse,
    Response
};

class ChatBotController extends Controller
{
    /**
     * Display a listing of the chatbots.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *         Returns a collection of chatbots wrapped in an AnonymousResourceCollection.
     */
    public function index(): AnonymousResourceCollection
    {
        return ChatBotResource::collection((new CharacterChatBotService)->bots());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $chatbot The ID of the chatbot to display.
     * @return \Illuminate\Http\JsonResponse|\Modules\OpenAI\Transformers\Api\v2\Chat\ChatBotResource
     *         Returns a JsonResponse with an error message if the chatbot does not exist, 
     *         otherwise returns a ChatBotResource.
     */
    public function show($chatbot): JsonResponse|ChatBotResource
    {
        $chatBot = ChatBot::find($chatbot);
        if ($chatBot) {
            return new ChatBotResource($chatBot);
        }
        return response()->json(['error' => __(':x does not exist.', ['x' => __('ChatBot')])], Response::HTTP_NOT_FOUND);
    }
}