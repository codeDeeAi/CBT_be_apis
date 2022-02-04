<?php

use App\Http\Controllers\DefaultAuthenticationController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamParticipantAnswerController;
use App\Http\Controllers\ExamParticipantsController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\UserController;
use App\Models\Exam;
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

Route::get('/test', function () {
    $exam = Exam::first();
    return $exam->settings;
});
Route::group([], function () {

    // Authentication
    Route::post('/signup', [UserController::class, 'create']);
    Route::post('/login', [DefaultAuthenticationController::class, 'login']);

    // Exams
    Route::post('/exam/register', [ExamParticipantsController::class, 'register']);
    Route::get('/exam/start', [ExamParticipantsController::class, 'take_examination']);
    Route::post('/exam/submit', [ExamParticipantAnswerController::class, 'create']);
    Route::get('/exam/result', [ExamParticipantAnswerController::class, 'check_result']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Examination
    Route::post('/exam', [ExamController::class, 'create']);
    Route::post('/exam/questions/{exam_id}', [ExamQuestionController::class, 'create']);
    Route::get('/exam/{exam_id}', [ExamController::class, 'index']);
});
