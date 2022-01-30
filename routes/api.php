<?php

use App\Http\Controllers\DefaultAuthenticationController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamParticipantsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([], function () {

    // Authentication
    Route::post('/signup', [UserController::class, 'create']);
    Route::post('/login', [DefaultAuthenticationController::class, 'login']);

    // Exams
    Route::post('/exam/register', [ExamParticipantsController::class, 'register']);

});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/exam', [ExamController::class, 'create']);
    Route::get('/exam', function () {
        return auth()->user();
    });
});
