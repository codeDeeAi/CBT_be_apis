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
            'settings.registration_status' => 'required|boolean'
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
}
