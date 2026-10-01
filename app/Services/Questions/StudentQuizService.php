<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentQuizService
{
    public function unansweredActiveQuestions(User $student, int $limit = 5): Collection
    {
        /*$answeredIds = $student->questionAnswers()->pluck('question_id'); */

       // Obtener solo las IDs de las preguntas que el estudiante YA respondió CORRECTAMENTE
        $masteredQuestionIds = $student->questionAnswers()
            ->where('is_correct', true)
            ->pluck('question_id');

        // Retornar preguntas activas excluyendo únicamente las que ya dominó
        return Question::query()
            ->active()
            ->with(['topic', 'options'])
            ->whereNotIn('id', $masteredQuestionIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
