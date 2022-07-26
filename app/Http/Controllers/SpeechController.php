<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Panda\Yandex\SpeechKitSdk;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\RespondsWithHttpStatus;

class SpeechController extends Controller
{
    use RespondsWithHttpStatus;

    // https://github.com/itpanda-llc/yandex-speechkit-sdk

    // Get API key: https://developer.tech.yandex.ru/

    // docs: http://api.yandex.ru/speechkit/cloud-api/doc/index.xml

    public function getTest(Request $request)
    {
        $text = "";

        $validator = Validator::make($request->all(), [
            'path' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'error' => 'Bad Request',
                'errors' => $validator->messages(),
            ], 400);
        }

        $exists = Storage::disk('local')->exists($request->path);

        if (!$exists) {
            return response([
                'success' => false,
                'error' => 'Not Found',
                'errors' => 'File ' . $request->path . ' not found',
            ], 404);
        }

        $url = Storage::url($request->path);

        // $headers = array(
        //     'Content-Type: audio/ogg',
        // );

        // return Storage::download($request->path, 'audio.ogg', $headers);


        // return response()->download($request->path);

        

        // $contents = Storage::get($request->path);

        // return response()->download($contents, 'audio.ogg', $headers);

        // return response([
        //     'success' => true,
        //     'path' => $contents
        // ]);

        try {
            // API-ключ
            $cloud = SpeechKitSdk\Cloud::createApi('apiKey');
        } catch (SpeechKitSdk\Exception\ClientException $e) {
            return response([
                'success' => false,
                'error' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }

        try {
            // Аудио-файл
            $recognize = new SpeechKitSdk\Recognize($url);
        } catch (SpeechKitSdk\Exception\ClientException $e) {
            return response([
                'success' => false,
                'error' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }

        try {
            $text = $cloud->request($recognize);
        } catch (SpeechKitSdk\Exception\ClientException $e) {
            return response([
                'success' => false,
                'error' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }

        return response([
            'success' => true,
            'text' => $text
        ]);
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audio' => 'required|mimetypes:audio/ogg'
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'error' => 'Bad Request',
                'errors' => $validator->messages(),
            ], 400);
        }

        $file = $request->file('audio');

        try {
            $path = Storage::disk('local')->put('audio', $file);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'error' => 'Internal Server Error',
                'errors' => $th->getMessage(),
            ], 500);
        }
        
        return response([
            'success' => true,
            'path' => $path
        ]);
    }

    public function recognize(Request $request)
    {
        $data = [];

        $validator = Validator::make($request->all(), [
            'audio' => 'required|mimetypes:audio/ogg'
        ]);

        if ($validator->fails()) {
            return $this->validateFailure($validator);
        }

        $file = $request->file('audio');

        $save_files = config('yandexspeech.filesystem.save_files');

        if ( $save_files ) {
            
            $path_files = config('yandexspeech.filesystem.path');

            try {
                $path = Storage::disk('local')->put($path_files, $file);
            } catch (\Throwable $th) {
                return $this->exceptionFailure($th);
            }
        }
        
        $token = config('yandexspeech.tokens.api');

        try {
            // API Key
            $cloud = SpeechKitSdk\Cloud::createApi($token);

            // Audio File Recognize
            $recognize = new SpeechKitSdk\Recognize($file);

            // Get Data
            $text = $cloud->request($recognize);

            $data = json_decode($text, true);

        } catch (SpeechKitSdk\Exception\ClientException $e) {
            return $this->exceptionFailure($e);
        }

        if( !isset( $data['result'] ) ){
            return $this->failure();
        }

        return $this->success($data['result'], $data);
    }

    public function synthesize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFailure($validator);
        }

        return $this->success();
    }
}
