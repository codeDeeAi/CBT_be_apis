<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamQuestionController extends Controller
{
    // Create Questions
    public function create(Request $request, $exam_id)
    {
        if (Exam::where('id', $exam_id)->doesntExist()) {
            return response()->json(['message' => 'Examination not found !'], 404);
        }

        // Validate request
        $this->validate($request, [
            '*.question' => 'required|string',
            '*.options' => 'required|array',
            '*.answers' => 'required|array',
            '*.mark_manually' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Save questions
            foreach ($request->request as $question) {
                ExamQuestion::create([
                    'exam_id' => $exam_id,
                    'question' => $question["question"],
                    'options' => $question["options"],
                    'answers' => $question["answers"],
                    'mark_manually' => ($question["mark_manually"]) ? $question["mark_manually"] : false
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Success !'
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            abort(500, $th);
        }

        return response()->json([
            'message' => 'Error adding questions !'
        ], 500);;
    }
}
