<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamParticipantAnswer extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'exam_id',
        'exam_participant_id',
        'exam_question_id',
        'question_answers'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'question_answers' => 'array'
    ];

    /**
     * Get the exam that owns the ExamParticipants
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam()
    {
        return $this->belongsTo('App\Models\Exam', 'exam_id', 'id');
    }

    /**
     * Get the exam_participant that owns the ExamParticipantAnswer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam_participant()
    {
        return $this->belongsTo('App\Models\ExamParticipant', 'exam_participant_id', 'id');
    }

    /**
     * Get the exam_question that owns the ExamParticipantAnswer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam_question()
    {
        return $this->belongsTo('App\Models\ExamQuestion', 'exam_question_id', 'id');
    }
}
