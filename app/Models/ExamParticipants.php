<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamParticipants extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'exam_id',
        'name',
        'email',
        'token',
        'settings'
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
        'settings' => 'array'
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
}
