<?php

namespace App\Http\Requests\Exam;

use App\Models\Exam;
use App\Models\ExamParticipants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ExamParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
         // Get exam token
         $exam_token = ($request->query('exam_token')) ? $request->query('exam_token') : '';
         $user_email = ($request->query('email')) ? $request->query('email') : '';
         $user_token = ($request->query('token')) ? $request->query('token') : '';
 
         if (Exam::where('token', $exam_token)->doesntExist()) {
             return response()->json([
                 'message' => 'Examination link is invalid, pls check link and try again !'
             ], 404);
         }
 
         // Get Exam
         $exam = Exam::where('token', $exam_token)->first();
 
 
         // Check if user is an exam participant
         if (ExamParticipants::where('exam_id', $exam->id)->where('email', $user_email)->where('token', $user_token)->doesntExist()) {
             return response()->json([
                 'message' => 'Invalid credentials, pls try again/ contact examiner !'
             ], 400);
         }
        return [
            //
        ];
    }
}
