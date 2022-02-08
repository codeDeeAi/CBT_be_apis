<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\ExamParticipantRequest;
use App\Models\Exam;
use App\Models\ExamParticipantAnswer;
use App\Models\ExamParticipants;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExamParticipantAnswerController extends Controller
{
    // Submit answers
    public function create(ExamParticipantRequest $request)
    {
        // Get exam token
        $exam_token = ($request->query('exam_token')) ? $request->query('exam_token') : '';
        $user_email = ($request->query('email')) ? $request->query('email') : '';
        $user_token = ($request->query('token')) ? $request->query('token') : '';

        // Get Exam
        $exam = Exam::where('token', $exam_token)->first();

        // Get Exam user
        $exam_participant = ExamParticipants::where('exam_id', $exam->id)->where('email', $user_email)->where('token', $user_token)->first();

        // Check if user has submitted answers for this exam
        if (ExamParticipantAnswer::where('exam_id', $exam->id)->where('exam_participant_id', $exam_participant->id)->exists()) {
            return response()->json([
                'message' => 'Cannot take exam twice !'
            ], 400);
        }

        // Validate request
        $this->validate($request, [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answers' => 'nullable|array',
        ]);

        // Store and mark response submitted
        DB::beginTransaction();
        try {
            foreach ($request->answers as $question) {
                if (ExamQuestion::where('id', $question["question_id"])->exists()) {
                    ExamParticipantAnswer::create([
                        'exam_id' => $exam->id,
                        'exam_participant_id' => $exam_participant->id,
                        'exam_question_id' => $question["question_id"],
                        'question_answers' => $question["answers"]
                    ]);
                }
            }

            // Check Score
            $results = $this->calc_score($exam->id, $exam_participant->id);

            DB::commit();
            return response()->json([
                'passed' => $results["passed"],
                'total' => $results["total"],
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response('Failed !', 500);
        }
    }

    public function check_result(ExamParticipantRequest $request)
    {
        // Get exam token
        $exam_token = ($request->query('exam_token')) ? $request->query('exam_token') : '';
        $user_email = ($request->query('email')) ? $request->query('email') : '';
        $user_token = ($request->query('token')) ? $request->query('token') : '';

        // Get Exam
        $exam = Exam::where('token', $exam_token)->first();

        // Get Exam user
        $exam_participant = ExamParticipants::where('exam_id', $exam->id)->where('email', $user_email)->where('token', $user_token)->first();

        // Use cache if exists
        if (Cache::has('exam_result.' . $exam_token . '.' . $user_token)) {
            $cache_result = Cache::get('exam_result.' . $exam_token . '.' . $user_token);

            return response()->json([
                $cache_result
            ], 200);
        }
        // Calc score
        $results = $this->calc_score($exam->id, $exam_participant->id);

        // Get User Results to enable printing
        $exam_det = Exam::where('id', $exam->id)
            ->select(
                'id',
                'name',
                'token',
                'settings'
            )->get();
        $exam_sheet = ExamParticipantAnswer::where('exam_id', $exam->id)
            ->where('exam_participant_id', $exam_participant->id)
            ->select('id', 'exam_id', 'exam_participant_id', 'exam_question_id', 'question_answers', 'created_at')
            ->with([
                'exam_question' => function ($query) {
                    $query->select(
                        'id',
                        'exam_id',
                        'question',
                        'options',
                        'answers',
                        'mark_manually'
                    );
                }
            ])->get();
        $result_template = [
            'result' => $results,
            'exam_details' => $exam_det,
            'exam_participant' => $exam_participant,
            'exam_sheet' => $exam_sheet
        ];
        // Update cache
        Cache::put('exam_result.' . $exam_token . '.' . $user_token, $result_template, 60 * 10);
        return response()->json([
            $result_template
        ], 200);
    }

    public function calc_score($exam_id, $exam_participant_id)
    {
        // Get all exam questions
        $questions = ExamQuestion::where('exam_id', $exam_id)->get();

        $total_score = 0;
        $total_scorable_points = ExamQuestion::where('exam_id', $exam_id)->count();
        $manually_marked = [];

        foreach ($questions as $question) {
            if (!$question->mark_manually) {
                $participant_answers = ExamParticipantAnswer::where('exam_id', $exam_id)
                    ->where('exam_question_id', $question->id)
                    ->where('exam_participant_id', $exam_participant_id)
                    ->first();
                $question_answers = $question->answers;
                $q_scorable = count($question_answers);
                $qp_score = 0;
                foreach ($question_answers as $q_key => $q_value) {
                    if (is_array($participant_answers->question_answers) && array_key_exists($q_key, $participant_answers->question_answers) && $participant_answers->question_answers[$q_key] == $q_value) {
                        $qp_score += 1;
                    }
                }

                $actual_score = $qp_score / $q_scorable;

                $total_score += $actual_score;
            } else {
                array_push($manually_marked, $question->id);
            }
        }

        return [
            'total' => $total_scorable_points,
            'passed' => $total_score,
            'exceptions' => $manually_marked,
            'failed' => $total_scorable_points - $total_score
        ];
    }
}
