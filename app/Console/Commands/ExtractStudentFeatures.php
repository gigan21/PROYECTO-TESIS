<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StudentFeatureSnapshot;
use App\Models\User;
use App\Services\ML\FeatureExtractorService;
use App\Support\Classroom;
use Illuminate\Console\Command;

class ExtractStudentFeatures extends Command
{
    protected $signature = 'features:extract
                            {--user= : Solo procesar un user_id}
                            {--classroom= : Solo un paralelo (slug canónico)}';

    protected $description = 'Genera snapshots diarios de features ML por estudiante (idempotente por día)';

    public function handle(FeatureExtractorService $service): int
    {
        $users = $this->resolveUsers();
        if ($users->isEmpty()) {
            $this->warn('No hay estudiantes que procesar.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $count = 0;
        foreach ($users as $user) {
            $features = $service->extractForUser($user);
            StudentFeatureSnapshot::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'snapshot_date' => today(),
                ],
                [
                    'classroom' => $user->studentProfile?->classroom,
                    'features_json' => $features,
                    'features_version' => 'v1',
                ]
            );
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$count} snapshots generados.");

        return self::SUCCESS;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    private function resolveUsers()
    {
        if ($this->option('user')) {
            $user = User::query()->findOrFail((int) $this->option('user'));
            if (! $user->isStudent()) {
                $this->error('El usuario indicado no es estudiante.');

                return collect();
            }

            return collect([$user->load('studentProfile')]);
        }

        $query = User::query()
            ->where('role', 'estudiante')
            ->where('is_active', 1)
            ->with('studentProfile');

        if ($this->option('classroom')) {
            $canonical = Classroom::fromSlug((string) $this->option('classroom'));
            $stored = Classroom::storedValuesFor($canonical);
            $query->whereHas('studentProfile', fn ($q) => $q->whereIn('classroom', $stored));
        }

        return $query->orderBy('id')->get();
    }
}
