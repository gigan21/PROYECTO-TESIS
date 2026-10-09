<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\QuestionBlockType;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class HardQuestionsBlockSeeder extends Seeder
{
    public function run(): void
    {
        $topic = Topic::query()->firstOrCreate(
            ['name' => 'Cinemática'],
            ['description' => 'Movimiento rectilíneo', 'is_active' => true]
        );

        $this->seedQuestion(
            $topic->id,
            'Un móvil recorre 120 m en 15 s en línea recta. Calcula la velocidad media.',
            [
                ['block_type' => QuestionBlockType::Text, 'content' => 'Identifica datos: distancia d y tiempo t.', 'sort_order' => 0],
                ['block_type' => QuestionBlockType::Formula, 'content' => 'v = \\frac{[120]}{[15]}', 'tolerance' => 0.0, 'sort_order' => 1],
                ['block_type' => QuestionBlockType::Input, 'content' => '8', 'unit' => 'm/s', 'tolerance' => 0.05, 'sort_order' => 2],
            ]
        );

        $this->seedQuestion(
            $topic->id,
            'Un objeto cae libremente durante 2 s (g ≈ 10 m/s²). ¿Qué distancia recorre?',
            [
                ['block_type' => QuestionBlockType::Text, 'content' => 'Usa cinemática vertical con velocidad inicial nula.', 'sort_order' => 0],
                ['block_type' => QuestionBlockType::Formula, 'content' => 'd = \\frac{[1]}{[2]} g [2]^2', 'tolerance' => 0.0, 'sort_order' => 1],
                ['block_type' => QuestionBlockType::Input, 'content' => '20', 'unit' => 'm', 'tolerance' => null, 'sort_order' => 2],
            ]
        );
    }

    /**
     * @param  list<array{block_type: QuestionBlockType, content: string, unit?: string|null, tolerance?: float|null, sort_order: int}>  $blocks
     */
    private function seedQuestion(int $topicId, string $text, array $blocks): void
    {
        $question = Question::query()->create([
            'topic_id' => $topicId,
            'difficulty' => QuestionDifficulty::Dificil,
            'type' => QuestionType::Calculo,
            'question_text' => $text,
            'xp_reward' => 25,
            'time_limit_seconds' => 120,
            'is_active' => true,
            'image_path' => null,
        ]);

        foreach ($blocks as $block) {
            QuestionOption::query()->create([
                'question_id' => $question->id,
                'option_text' => '',
                'block_type' => $block['block_type'],
                'content' => $block['content'],
                'unit' => $block['unit'] ?? null,
                'tolerance' => $block['tolerance'] ?? null,
                'sort_order' => $block['sort_order'],
                'is_correct' => $block['block_type'] === QuestionBlockType::Input,
            ]);
        }
    }
}
