<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamParticipants;
use Illuminate\Support\Str;

class ExamParticipantsController extends Controller
{

    // Register for an exam
    public function register(Request $request)
    {

        // Validate request
        $this->validate($request, [
            'name' => 'nullable|string',
            'email' => 'required|email'
        ]);

        // Get exam token
        $exam_token = ($request->query('token')) ? $request->query('token') : '';

        if (Exam::where('token', $exam_token)->doesntExist()) {
            return response()->json([
                'message' => 'Examination link is invalid, pls check link and try again !'
            ], 404);
        }

        $exam = Exam::where('token', $exam_token)->first();

        $exam_settings = $exam->settings;

        // Check that user cannot register same exam twice
        $current_participants = ExamParticipants::where('exam_id', $exam->id)->select('email')->get();

        foreach ($current_participants as $participant) {
            if ($participant->email == $request->email) {
                return response()->json([
                    'message' => 'You cannot register twice for the same examination !'
                ], 400);
            }
        }

        if (key_exists('registration_status', $exam_settings) && $exam_settings['registration_status'] == true) {

            // Store Token
            $token = Str::random(25);

            // Create participant
            ExamParticipants::create([
                'exam_id' => $exam->id,
                'name' => ($request->name) ? $request->name : '',
                'email' => $request->email,
                'token' => $token,
                'settings' => json_encode([])
            ]);

            return response()->json([
                'message' => 'Success !',
                'token' => $token
            ], 200);
        }

        return response()->json([
            'message' => 'Registration closed, pls contact examiner !'
        ], 500);
    }
}
