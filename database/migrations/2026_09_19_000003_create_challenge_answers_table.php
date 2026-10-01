<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->constrained('question_options')->restrictOnDelete();
            $table->boolean('is_correct');
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedInteger('response_time_seconds');
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->unique(['challenge_room_id', 'student_id', 'question_id'], 'challenge_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_answers');
    }
};
