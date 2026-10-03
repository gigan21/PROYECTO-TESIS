<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadimos si saltó la pregunta al registro individual
        Schema::table('student_question_answers', function (Blueprint $table) {
            $table->boolean('is_skipped')->default(false)->after('is_correct');
        });

        // 2. Añadimos la suma de aciertos y saltos al historial de la IA
        Schema::table('learning_logs', function (Blueprint $table) {
            $table->unsignedInteger('correct_attempts')->default(0)->after('attempts');
            $table->unsignedInteger('skipped_attempts')->default(0)->after('correct_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('student_question_answers', function (Blueprint $table) {
            $table->dropColumn('is_skipped');
        });

        Schema::table('learning_logs', function (Blueprint $table) {
            $table->dropColumn(['correct_attempts', 'skipped_attempts']);
        });
    }
};