<?php

/**
 * @package UseCasesController
 * @author TechVillage <support@techvill.org>
 * @contributor Soumik Datta <soumik.techvill@gmail.com>
 * @created 22-02-2023
 * @modified 13-03-2023
 */

namespace Modules\OpenAI\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Auth, DB};
use Maatwebsite\Excel\Facades\Excel;
use Modules\OpenAI\Entities\{UseCase, UseCaseCategory};
use Modules\MediaManager\Http\Models\ObjectFile;
use Modules\OpenAI\DataTables\UseCaseDataTable;
Use Modules\OpenAI\Entities\{Content, Option, OptionMeta};
use Modules\OpenAI\Exports\UseCaseExport;

class UseCasesController extends Controller
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
     * @param  UseCaseDataTable  $dataTable
     * @return mixed
     */
    public function index(UseCaseDataTable $dataTable)
    {
        $data['useCases'] = UseCase::where('status', 'Active')->select('id', 'name')->get();
        return $dataTable->render('openai::admin.use-case.index', $data);
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
            return to_route('admin.use_case.list');
        }

        $searchCategoryUrl = route('admin.use_case.category.search');

        return view('openai::admin.use-case.create', compact('searchCategoryUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        $response = ['status' => 'fail', 'message' => __('The :x has not been saved. Please try again.', ['x' => __('Use case')])];

        $request = app('Modules\OpenAI\Http\Requests\UseCaseRequest')->safe();

        $useCaseData = array_merge($request->except(['input_template', 'category_id_array', 'file_id']), [
            'creator_type' => Auth::user()->role()->type,
            'creator_id' => Auth::id(),
            'prompt' => $request->input_template
        ]);

        try {
            DB::beginTransaction();
            $useCase = UseCase::create($useCaseData);

            if ($useCase) {

                $types = array_filter($request->type);
                $names = array_filter($request->names);
                $descriptions = array_filter($request->descriptions);
                $variableNames = array_filter($request->variable_names);

                $options = [];
                
                $optionMetaKeys = [ 'required', 'label', 'placeholder' ];

                foreach ( $types as $key => $type ) {
                    $options = [
                        'use_case_id' => $useCase->id,
                        'type' => $type,
                        'key' => $variableNames[$key]
                    ];

                    $option = Option::insertGetId($options);
                    
                    $optionMetas = [];
                    foreach ($optionMetaKeys as $value) {
                        $optionMetas[] = [
                            'option_id' => $option,
                            'key' => $value,
                            'value' => $value === 'required' ? $value : ( $value === 'label' ? $names[$key] : $descriptions[$key] )
                        ];
                    }

                    OptionMeta::insert($optionMetas);
                }

                if ($request->has('file_id') && !empty($request->file_id)) {
                    ObjectFile::storeInObjectFiles($useCase->getTable(), $useCase->id, $request->file_id);
                }

                if ($request->has('category_id_array') && !empty($request->category_id_array)) {
                    $useCase->useCaseCategories()->attach($request->category_id_array);
                }
    
                if (empty($request->category_id_array)) {
                    $defaultCategory = UseCaseCategory::where('slug', 'others')->first();
    
                    if ($defaultCategory) {
                        $useCase->useCaseCategories()->attach([$defaultCategory->id]);
                    }
                }

                $response = ['status' => 'success', 'message' => __('Use case created successfully!')];
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }

        $this->setSessionValue($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function edit($id)
    {
        if (!is_numeric($id)) {
            abort(404);
        }

        if (request()->isMethod('POST')) {
            
            $this->update($id);
            return to_route('admin.use_case.list');
            
        }

        if ($useCase = UseCase::with(['useCaseCategories:id,name', 'option', 'option.metadata'])->find($id)) {
            $option = Option::where('use_case_id', $id)->first();
            $searchCategoryUrl = route('admin.use_case.category.search');
            return view('openai::admin.use-case.edit', compact('searchCategoryUrl', 'useCase', 'option'));
        }

        abort(404);
    }

    /**
     * Make Decision of changing value
     *
     * @param int $id
     * @return \Illuminate\Routing\Redirector
     */
    public function checkDynamicVariable($id)
    {
        $options = Option::where('use_case_id', $id)->get();
        // Regular expression pattern
        $pattern = "/\[\[(.*?)\]\]/";

        // Extract the content
        preg_match_all($pattern, request('input_template'), $matches);

        if (count($options) != count($matches[1])) {
            $response = ['status' => 'fail', 'message' => __('Dynamic variable can not be add or remove.')];
            $this->setSessionValue($response);
            return redirect()->back();
        }

        foreach($options as $option) {
            if (!in_array($option->key, $matches[1])) {
                $response = ['status' => 'fail', 'message' => __('Dynamic variable can not be change.')];
                $this->setSessionValue($response);
                return redirect()->back();
            }
        }

        return true;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return mixed
     */
    public function update($id)
    {
        $response = ['status' => 'fail', 'message' => __('The :x has not been saved. Please try again.', ['x' => __('Use case')])];

        $request = app('Modules\OpenAI\Http\Requests\UseCaseRequest')->safe();
        $useCase = UseCase::find($id);

        try {
            DB::beginTransaction();
            if ($useCase) {
                $useCaseData = array_merge($request->except(['input_template', 'category_id_array', 'file_id']), [
                    'prompt' => $request->input_template,
                    'creator_id' => Auth::id()
                ]);

                $useCase->update($useCaseData);
                $useCase->option()->delete();

                $types = array_filter($request->type);
                $names = array_filter($request->names);
                $descriptions = array_filter($request->descriptions);
                $variableNames = array_filter($request->variable_names);

                $options = [];
                $optionMetaKeys = [ 'required', 'label', 'placeholder' ];

                foreach ( $types as $key => $type ) {
                    $options = [
                        'use_case_id' => $useCase->id,
                        'type' => $type,
                        'key' => $variableNames[$key]
                    ];

                    $option = $useCase->option()->updateOrCreate($options);
                    $option->save();

                    $optionMetas = [];
                    foreach ($optionMetaKeys as $value) {
                        $optionMetas[] = [
                            'option_id' => $option->id,
                            'key' => $value,
                            'value' => $value === 'required' ? $value : ( $value === 'label' ? $names[$key] : $descriptions[$key] )
                        ];
                    }

                    OptionMeta::upsert($optionMetas, ['option_id'], [ 'key']);
                }

                if ($request->has('file_id') && !empty($request->file_id)) {
                    $useCase->updateFiles();
                } else {
                    $useCase->deleteFromMediaManager();
                }

                if ($request->has('category_id_array') && !empty($request->category_id_array)) {
                    $useCase->useCaseCategories()->sync($request->category_id_array);
                } else {
                    $id = UseCaseCategory::where('slug', 'others')->value('id');
                    $useCase->useCaseCategories()->sync($id);
                }

                $response = ['status' => 'success', 'message' => __('Use case updated successfully!')];
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }

        $this->setSessionValue($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return mixed
     */
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            abort(404);
        }

        if ($useCase = UseCase::find($id)) {


            if (Content::where('use_case_id', $id)->count()) {

                $response = ['status' => 'fail', 'message' => __("Use case can not be deleted. It's already being used.")];

            } else {
                DB::beginTransaction();

                try {
                    UseCase::clearFootprints($useCase);
                    UseCase::destroy($id);

                    DB::commit();
                    $response = ['status' => 'success', 'message' => __('The :x has been successfully deleted.', ['x' => __('Use Case')])];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $response = ['status' => 'fail', 'message' => $e->getMessage()];
                }
            }

            $this->setSessionValue($response);
            return to_route('admin.use_case.list');
        }

        abort(404);
    }

    /**
     * User list pdf
     *
     * @return mixed
     */
    public function pdf()
    {
        $data['useCases'] = UseCase::with(['user', 'user.metas'])->get();

        return printPDF($data, 'use_case_list_' . time() . '.pdf', 'openai::admin.use-case.use_case_list_pdf', view('openai::admin.use-case.use_case_list_pdf', $data), 'pdf');
    }

    /**
     * User list csv
     *
     * @return mixed
     */
    public function csv()
    {
        return Excel::download(new UseCaseExport(), 'use_case_list_' . time() . '.csv');
    }
}
