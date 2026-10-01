<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Enums\CrosswordLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crossword\SaveCrosswordProgressRequest;
use App\Models\CrosswordWord;
use App\Services\Crossword\CrosswordProgressService;
use App\Services\Crossword\CrosswordWordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrosswordPlayController extends Controller
{
    public function __construct(
        private readonly CrosswordWordService $wordService,
        private readonly CrosswordProgressService $progressService
    ) {}

    public function index(): View
    {
        return view('estudiante.juegos.crucigrama');
    }

    public function getLevelWords(Request $request): JsonResponse
    {
        $progress = $this->progressService->getOrCreate($request->user());
        $level = $progress->current_level;
        $words = $this->wordService->getRandomWordsForLevel($level, $request->user()->id);

        return response()->json([
            'level' => $level,
            'max_level' => CrosswordLevel::MAX_LEVEL,
            'words' => $words->map(fn (CrosswordWord $word) => [
                'id' => $word->id,
                'answer' => mb_strtoupper($word->answer, 'UTF-8'),
                'clue' => $word->clue,
            ])->values(),
        ]);
    }

    public function getProgress(Request $request): JsonResponse
    {
        $progress = $this->progressService->getOrCreate($request->user());

        return response()->json($this->progressService->toArray($progress));
    }

    public function saveProgress(SaveCrosswordProgressRequest $request): JsonResponse
    {
        $user = $request->user();
        $action = $request->string('action')->toString();
        $level = (int) $request->input('level', CrosswordLevel::MIN_LEVEL);
        $word = (string) $request->input('word', '');
        $timeSpent = (int) $request->input('time_spent', 0);

        $payload = match ($action) {
            'word_learned' => $this->progressService->recordWordLearned($user, $word, $level, $timeSpent),
            'wrong_attempt' => [
                'progress' => $this->progressService->toArray(
                    $this->progressService->recordWrongAttempt($user, $word, $level)
                ),
                'coins' => $this->progressService->getOrCreate($user)->coins_earned,
                'xp' => 0,
                'coins_delta' => 0,
            ],
            'advance_level' => [
                'progress' => $this->progressService->toArray(
                    $this->progressService->advanceLevel($user, $level)
                ),
                'coins' => $this->progressService->getOrCreate($user)->coins_earned,
                'xp' => 0,
                'coins_delta' => 0,
            ],
            'reset' => [
                'progress' => $this->progressService->toArray($this->progressService->reset($user)),
                'coins' => 0,
                'xp' => 0,
                'coins_delta' => 0,
            ],
            default => ['progress' => [], 'coins' => 0, 'xp' => 0, 'coins_delta' => 0],
        };

        return response()->json($payload);
    }

    public function resetProgress(Request $request): JsonResponse
    {
        $progress = $this->progressService->reset($request->user());

        return response()->json($this->progressService->toArray($progress));
    }
}
