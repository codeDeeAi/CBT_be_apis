<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamParticipants;
use App\Models\ExamQuestion;
use Carbon\Carbon;
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

            $settings = [
                "disabled" => false,
                "suspension" => false,
                "suspension_time" => 0,
                "start_time" => 0,
                "attempts" => 0
            ];

            // Create participant
            ExamParticipants::create([
                'exam_id' => $exam->id,
                'name' => ($request->name) ? $request->name : '',
                'email' => $request->email,
                'token' => $token,
                'settings' => $settings
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


    // Participate in/ Take an Exam
    public function take_examination(Request $request)
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

        /**
         * Check if user meets the requirement for taking examination
         * ? 1. is user disabled by examiner
         */

        /**
         * Construct response
         * Exam Details
         * User Details
         * Time Details 
         * Questions
         * 
         */

        $exam_details = Exam::where('id', $exam->id)
            ->select(
                'id',
                'name',
                'settings'
            )->first();
        $questions = ExamQuestion::where('exam_id', $exam->id)
            ->select(
                'id',
                'exam_id',
                'question',
                'options'
            )
            ->inRandomOrder()->get();
        $student_details = ExamParticipants::where('exam_id', $exam->id)
            ->where('email', $user_email)
            ->where('token', $user_token)
            ->select(
                'id',
                'exam_id',
                'name',
                'token',
                'settings'
            )
            ->first();
        $now = Carbon::now();
        $start_time = ($student_details->settings["start_time"] !== 0) ? Carbon::parse($student_details->settings["start_time"]) :  Carbon::now();
        $control_start_time = Carbon::parse($start_time);
        $expiry_time = $control_start_time->addHours($exam_details->settings["duration"], true);
        $time_control = [
            'start_time' => $start_time,
            'current_time' => now(),
            'expires' => $expiry_time,
            'time_left' => $start_time->diffInSeconds($expiry_time, true)

        ];

        // dd($start_time->diffInSeconds($expiry_time, true));

        // Save time
        // New student settings
        // $student_settings = $student_details->settings;

        // $student_settings['start_time'] = $start_time;

        // ExamParticipants::where('exam_id', $exam->id)
        //     ->where('email', $user_email)
        //     ->where('token', $user_token)
        //     ->update([
        //         'settings' => $student_settings
        //     ]);


        $student_details = ExamParticipants::where('exam_id', $exam->id)
            ->where('email', $user_email)
            ->where('token', $user_token)
            ->select(
                'id',
                'exam_id',
                'name',
                'token',
                'settings'
            )
            ->first();

        return response()->json([
            'exam_details' => $exam_details,
            'student_details' => $student_details,
            'time_control' => $time_control,
            'questions' => $questions
        ], 200);
    }
}
