<?php

/**
 * @package AIController
 * @author TechVillage <support@techvill.org>
 * @contributor Md. Khayeruzzaman <[shakib.techvill@gmail.com]>
 * @created 26-07-2023
 */

namespace Modules\OpenAI\Http\Controllers\Admin;

Use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\OpenAI\Entities\ChatCategory;
use Modules\OpenAI\DataTables\ChatCategoriesDataTable;
use Modules\OpenAI\Exports\ChatCategoryExport;


class ChatCategoriesController extends Controller
{
    /**
     * Company Setting Constructor
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        //this middleware should be for POST request only
        if ($request->isMethod('post')) {
            $this->middleware('checkForDemoMode')->only('create', 'edit');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  ChatCategoriesDataTable  $dataTable
     * @return mixed
     */
    public function index(ChatCategoriesDataTable $dataTable)
    {
        return $dataTable->render('openai::admin.chat-category.index');
    }

     /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
        if (request()->isMethod('POST')) {
            $this->store();
            return to_route('admin.chat.category.list');
        }

        return view('openai::admin.chat-category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        $response = ['status' => 'fail', 'message' => __('Something went wrong!')];
        $request = app('Modules\OpenAI\Http\Requests\ChatCategoryRequest')->safe();

        if (ChatCategory::create($request->all())) {
            $response = ['status' => 'success', 'message' => __('The :x has been successfully created.', ['x' => __('Chat Category')])];
        }

        $this->setSessionValue($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return mixed
     */
    public function edit($id)
    {
        if (!is_numeric($id)) {
            abort(404);
        }

        if (request()->isMethod('POST')) {
            $this->update($id);
            return to_route('admin.chat.category.list');
        }

        if ($chatCategory = ChatCategory::find($id)) {
            return view('openai::admin.chat-category.edit', compact('chatCategory'));
        }

        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function update($id)
    {
        try {
            $request = app('Modules\OpenAI\Http\Requests\ChatCategoryRequest')->safe();
            $useCaseCategory = ChatCategory::find($id);

            if (!$useCaseCategory) {
                throw new \Exception(__('Chat category not found'));
            }

            if ($useCaseCategory->slug == 'others') {
                unset($request['slug']);
            }

            $useCaseCategory->update($request->all());

            $this->setSessionValue(['status' => 'success', 'message' => __('The :x has been successfully updated.', ['x' => __('Chat Category')])]);
        } catch (\Exception $e) {
            $this->setSessionValue(['status' => 'fail', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function destroy($id)
    {

        if (!is_numeric($id)) {
            abort(404);
        }

        $chatCategory = ChatCategory::find($id);

        if ($chatCategory->slug === "others") {
            return redirect()->back()->withFail(__('Invalid Request!'));
        }
        
        if ($chatCategory) {

            try {

                DB::beginTransaction();
                $id = ChatCategory::where('slug', 'others')->value('id');
                $chatCategory->chatBots()->update(['chat_category_id' => $id]);
                $chatCategory->delete();
                DB::commit();

                $response = ['status' => 'success', 'message' => __('The :x has been successfully deleted.', ['x' => __('Chat Category')])];

            } catch (\Exception $e) {
                DB::rollBack();
                $response = ['status' => 'fail', 'message' => __('Something went wrong.')];
            }

            $this->setSessionValue($response);
            return to_route('admin.chat.category.list');

        }

        abort(404);
    }

    /**
     * Chat Categories list pdf
     *
     * @return mixed
     */
    public function pdf()
    {
        $data['chatCategories'] = ChatCategory::get();

        return printPDF($data, 'chat_category_list_' . time() . '.pdf', 'openai::admin.chat-category.chat_category_list_pdf', view('openai::admin.chat-category.chat_category_list_pdf', $data), 'pdf');
    }

    /**
     * Chat Categories list csv
     *
     * @return mixed
     */
    public function csv()
    {
        return Excel::download(new ChatCategoryExport(), 'chat_category_list_' . time() . '.csv');
    }
}
