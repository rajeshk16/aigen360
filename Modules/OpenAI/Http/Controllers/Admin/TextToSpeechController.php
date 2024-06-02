<?php

namespace Modules\OpenAI\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\OpenAI\Services\TextToSpeechService;
use Modules\OpenAI\DataTables\{
    TextToSpeechDataTable,
    VoiceDataTable
};
use Modules\OpenAI\Entities\Voice;
use Session;
use DB;
use Modules\OpenAI\Entities\Audio;
use Maatwebsite\Excel\Facades\Excel;
use Modules\OpenAI\Exports\TextToSpeechExport;
use Modules\OpenAI\Exports\VoiceExport;

class TextToSpeechController extends Controller
{
    /**
     * Service
     *
     * @var object
     */
    protected $textToSpeechService;

    /**
     * @param TextToSpeechService $textToSpeechService
     */
    public function __construct(TextToSpeechService $textToSpeechService)
    {
        $this->textToSpeechService = $textToSpeechService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(TextToSpeechDataTable $textToSpeechDataTable)
    {
        $data['audios'] = $this->textToSpeechService->getAll()->get();
        $data['users'] = $this->textToSpeechService->users();
        $data['omitLanguages'] = moduleConfig('openai.text_to_speech_language');
        $data['languages'] = $this->textToSpeechService->languages();
        return $textToSpeechDataTable->render('openai::admin.text-to-speech.index', $data);
    }

    /**
     * Show the specified resource.
     * @param int $slug
     * @return Renderable
     */
    public function show($slug)
    {
        $data['audio'] = $this->textToSpeechService->audioBySlug($slug);
        return view('openai::admin.text-to-speech.view', $data);
    }

     /**
     * Delete
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $data = [
            'status' => 'failed',
            'message' => __('The data you are looking for is not found')
        ];

        $service = $this->textToSpeechService->delete($request->audioId);
        if ($service) {
            $data = [
                'status' => 'success',
                'message' => __('Text to speech deleted successfully')
            ];
        }
        Session::flash($data['status'], $data['message']);
        return redirect()->route('admin.features.textToSpeech.lists');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function allVoices(VoiceDataTable $voiceDataTable)
    {
        $data['audios'] = $this->textToSpeechService->getAll()->get();
        $data['users'] = $this->textToSpeechService->users();
        $data['languages'] = $this->textToSpeechService->languages();
        $data['omitLanguages'] = moduleConfig('openai.text_to_speech_language');
        return $voiceDataTable->render('openai::admin.voice.index', $data);
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function voiceEdit($id)
    {
        if (!is_numeric($id)) {
            abort(404);
        }

        if (request()->isMethod('POST')) {
            
            $this->update($id);
            return to_route('admin.features.textToSpeech.voice.lists');
            
        }

        if ($voice = Voice::find($id)) {
            return view('openai::admin.voice.edit', compact('voice'));
        }

        abort(404);
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

        $request = app('Modules\OpenAI\Http\Requests\VoiceRequest')->safe();
        $voice = Voice::find($id);

        try {
            DB::beginTransaction();
            if ($this->textToSpeechService->updateVoice($request->only('name', 'status'), $id)) {
                $response['status'] = 'success';
                $response['message'] = __('The :x has been successfully updated.', ['x' => __('Voice')]);
            }
    
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }

        Session::flash($response['status'], $response['message']);
    }

    /**
     * Text To Speech list pdf
     *
     * @return mixed
     */
    public function pdf()
    {
        $data['textToSpeechs'] = Audio::with(['user:id,name', 'user.metas'])->get();

        return printPDF($data, 'text_to_speech_list_' . time() . '.pdf', 'openai::admin.text-to-speech.text_to_speech_list_pdf', view('openai::admin.text-to-speech.text_to_speech_list_pdf', $data), 'pdf');
    }

    /**
     * Text To Speech list csv
     *
     * @return mixed
     */
    public function csv()
    {
        return Excel::download(new TextToSpeechExport(), 'text_to_speech_list_' . time() . '.csv');
    }

    /**
     * Voices list pdf
     *
     * @return mixed
     */
    public function voicePdf()
    {
        $data['AiVoices'] = Voice::get();

        return printPDF($data, 'voice_list_' . time() . '.pdf', 'openai::admin.voice.voice_pdf', view('openai::admin.voice.voice_pdf', $data), 'pdf');
    }

    /**
     * Voices list csv
     *
     * @return mixed
     */
    public function voiceCsv()
    {
        return Excel::download(new VoiceExport(), 'voice__list_' . time() . '.csv');
    }
    
}
