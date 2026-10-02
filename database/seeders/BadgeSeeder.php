<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\CrosswordProgress;
use App\Models\StudentProfile;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['slug' => 'level_1', 'name' => 'Nivel 1', 'image' => 'insignia.png', 'category' => 'level', 'requirement_text' => 'Alcanza el nivel 1', 'rule_key' => 'level:1', 'sort_order' => 1],
            ['slug' => 'level_2', 'name' => 'Nivel 2', 'image' => 'insignia2.png', 'category' => 'level', 'requirement_text' => 'Alcanza el nivel 2', 'rule_key' => 'level:2', 'sort_order' => 2],
            ['slug' => 'level_3', 'name' => 'Nivel 3', 'image' => 'insignia3.png', 'category' => 'level', 'requirement_text' => 'Alcanza el nivel 3', 'rule_key' => 'level:3', 'sort_order' => 3],
            ['slug' => 'level_4', 'name' => 'Nivel 4', 'image' => 'insignia4.png', 'category' => 'level', 'requirement_text' => 'Alcanza el nivel 4', 'rule_key' => 'level:4', 'sort_order' => 4],
            ['slug' => 'level_5', 'name' => 'Nivel 5', 'image' => 'insignia5.png', 'category' => 'level', 'requirement_text' => 'Alcanza el nivel 5', 'rule_key' => 'level:5', 'sort_order' => 5],
            ['slug' => 'questions_easy_15', 'name' => 'Maestro Fácil', 'image' => 'insigniaPreguntasFacil.png', 'category' => 'questions', 'requirement_text' => 'Completa 15 preguntas fáciles', 'rule_key' => 'easy_correct:15', 'sort_order' => 6],
            ['slug' => 'questions_medium_20', 'name' => 'Estratega Medio', 'image' => 'insigniaPreguntasMedio.png', 'category' => 'questions', 'requirement_text' => 'Completa 20 preguntas medias', 'rule_key' => 'medium_correct:20', 'sort_order' => 7],
            ['slug' => 'questions_hard_25', 'name' => 'Genio Difícil', 'image' => 'insigniaPreguntasDificil.png', 'category' => 'questions', 'requirement_text' => 'Completa 25 preguntas difíciles', 'rule_key' => 'hard_correct:25', 'sort_order' => 8],
            ['slug' => 'placeholder_salas', 'name' => 'Batallas de Jefes', 'image' => 'insigniasalas.png', 'category' => 'placeholder', 'requirement_text' => 'Próximamente', 'rule_key' => null, 'sort_order' => 9],
            ['slug' => 'placeholder_ranking', 'name' => 'Ranking', 'image' => 'insigniaranking.png', 'category' => 'placeholder', 'requirement_text' => 'Próximamente', 'rule_key' => null, 'sort_order' => 10],
        ];

        foreach ($badges as $badge) {
            Badge::query()->updateOrCreate(['slug' => $badge['slug']], $badge);
        }

        CrosswordProgress::query()->each(function (CrosswordProgress $progress) {
            StudentProfile::query()
                ->where('user_id', $progress->user_id)
                ->update(['coins' => $progress->coins_earned]);
        });
    }
}
