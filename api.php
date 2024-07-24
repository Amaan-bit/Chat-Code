<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'v1'],function(){
    
    Route::post('/sent_message', [ChatController::class, 'sent_message'])->name('sentMessage');
    Route::post('/sent_image', [ChatController::class, 'sent_image'])->name('sentImage');
    Route::post('/get_message', [ChatController::class, 'get_message'])->name('getMessage');
    Route::get('/read_message', [ChatController::class, 'read_message'])->name('readMessage');
    Route::get('/get_chat', [ChatController::class, 'get_chat'])->name('getChat');
});


