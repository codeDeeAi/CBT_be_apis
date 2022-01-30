<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamParticipantAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_participant_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('exam_id');
            $table->bigInteger('exam_participant_id');
            $table->bigInteger('exam_question_id');
            $table->json('question_answers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_participant_answers');
    }
}
