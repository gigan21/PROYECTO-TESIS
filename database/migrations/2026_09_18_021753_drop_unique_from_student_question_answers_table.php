<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_question_answers', function (Blueprint $table) {
            // 1. Crear un índice ordinario para respaldar la Foreign Key
            $table->index(['student_id', 'question_id'], 'sqa_student_question_index');

            // 2. Ahora sí eliminar la restricción de unicidad
            $table->dropUnique('student_question_answers_student_id_question_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_question_answers', function (Blueprint $table) {
            $table->unique(['student_id', 'question_id'], 'student_question_answers_student_id_question_id_unique');
            $table->dropIndex('sqa_student_question_index');
        });
    }
};