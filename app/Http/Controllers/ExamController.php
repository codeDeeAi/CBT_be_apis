<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    // Create Exam
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'is_active' => 'nullable|boolean',
            'settings' => 'required|array',
            'settings' => 'required',
            'settings.registration_status' => 'required|boolean',
            'settings.fixed' => 'required|array',
            'settings.fixed.status' => 'required|boolean',
            'settings.fixed.date' => 'required_if:settings.fixed.status,true|date',
            'settings.duration' => 'required|integer',
            'settings.attempts' => 'required|integer|min:1'
        ]);

        // Create new exam
        Exam::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'token' => Str::random(25),
            'is_active' => ($request->is_active) ? $request->is_active : false,
            'settings' => $request->settings
        ]);

        return response()->json([
            'message' => 'Success !'
        ], 200);
    }

    // Show Exam to Examiner
    public function index(Request $request, $exam_id)
    {
        if (Exam::where('id', $exam_id)->where('user_id', auth()->user()->id)->doesntExist()) {
            return response()->json(['message' => 'Examination not found !'], 404);
        }

        return Exam::where('id', $exam_id)->with(['questions' => function ($query) {
            $query->select(
                "id",
                "exam_id",
                "question",
                "options",
                "answers",
                "mark_manually",
                "created_at"
            );
        }, 'participants' => function ($query) {
            $query->select(
                "id",
                "exam_id",
                "name",
                "email",
                "settings",
                "created_at"
            );
        }])->select()->first();
    }
}
