<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('speech')->group(function () {
    Route::post('/upload', [SpeechController::class, 'uploadFile']);
    Route::post('/recognize', [SpeechController::class, 'recognize']);
	Route::post('/synthesize', [SpeechController::class, 'synthesize']);
});
