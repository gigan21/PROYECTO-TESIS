<?php

namespace App\Console\Commands;

use App\Services\Questions\QuestionCsvImporter;
use Illuminate\Console\Command;

class ImportPhysicsQuestionsCommand extends Command
{
    protected $signature = 'questions:import {--path= : Ruta absoluta o relativa al CSV}';

    protected $description = 'Importa el banco de preguntas de Física desde database/data/physics_questions.csv';

    public function handle(QuestionCsvImporter $importer): int
    {
        $path = $this->option('path') ?: database_path('data/physics_questions.csv');

        try {
            $stats = $importer->import($path);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Importación completada.');
        $this->line("Temas creados: {$stats['topics_created']}");
        $this->line("Temas encontrados: {$stats['topics_found']}");
        $this->line("Preguntas importadas: {$stats['questions_imported']}");
        $this->line("Preguntas omitidas (ya existían): {$stats['questions_skipped']}");
        $this->line("Opciones creadas: {$stats['options_created']}");

        return self::SUCCESS;
    }
}
