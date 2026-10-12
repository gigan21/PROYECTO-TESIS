<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projectiles\ProjectileGameRewardRequest;
use App\Services\Gamification\CoinService;
use App\Services\Games\GamesDailyCoinCapService;
use App\Services\Projectiles\ProjectileCoinRewardCalculator;
use App\Services\Projectiles\ProjectileLevelRewardGuard;
use App\Services\Projectiles\ProjectileSessionRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectileGameController extends Controller
{
    public function __construct(
        private readonly CoinService $coins,
        private readonly ProjectileCoinRewardCalculator $rewardCalculator,
        private readonly ProjectileLevelRewardGuard $rewardGuard,
        private readonly ProjectileSessionRecorder $sessionRecorder,
        private readonly GamesDailyCoinCapService $dailyCap,
    ) {}

    public function reward(ProjectileGameRewardRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $level = (int) $validated['level'];
        $score = (int) $validated['score'];
        $finished = (bool) $validated['finished'];
        $isWin = (bool) $validated['is_win'];
        $sessionId = (string) $validated['session_id'];
        /** @var array<string, mixed>|null $telemetry */
        $telemetry = $validated['telemetry'] ?? null;

        $this->sessionRecorder->recordIfFinished(
            user: $user,
            finished: $finished,
            isWin: $isWin,
            level: $level,
            score: $score,
            telemetry: $telemetry,
        );

        $requestedCoins = $this->rewardCalculator->calculate($finished, $isWin);

        if ($requestedCoins <= 0) {
            return $this->jsonResponse($user, 0, false, false);
        }

        $dailyCap = $this->dailyCap->dailyCap();
        $alreadyEarnedToday = $this->dailyCap->earnedToday($user->id);
        $available = max(0, $dailyCap - $alreadyEarnedToday);
        $toAward = min($requestedCoins, $available);
        $capped = $toAward < $requestedCoins;

        if (! $this->rewardGuard->tryClaim($user, $sessionId, $level, $finished, $toAward)) {
            return response()->json([
                'coins' => $this->coins->getBalance($user),
                'coins_delta' => 0,
                'total_coins' => $this->coins->getBalance($user),
                'duplicate' => true,
                'daily_earned' => $alreadyEarnedToday,
                'daily_cap' => $dailyCap,
                'capped' => false,
            ]);
        }

        if ($toAward <= 0) {
            return $this->jsonResponse($user, 0, true, true);
        }

        $this->coins->addCoins(
            user: $user,
            amount: $toAward,
            source: 'projectiles',
            description: $finished
                ? "Simulador de proyectiles — partida final (nivel {$level})"
                : "Simulador de proyectiles — nivel {$level} superado",
            metadata: [
                'level' => $level,
                'score' => $score,
                'finished' => $finished,
                'is_win' => $isWin,
                'session_id' => $sessionId,
                'requested_coins' => $requestedCoins,
                'capped' => $capped,
            ]
        );

        return $this->jsonResponse($user, $toAward, $capped, false);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $dailyCap = $this->dailyCap->dailyCap();
        $earnedToday = $this->dailyCap->earnedToday($user->id);

        return response()->json([
            'coins' => $this->coins->getBalance($user),
            'daily_earned' => $earnedToday,
            'daily_cap' => $dailyCap,
            'daily_remaining' => max(0, $dailyCap - $earnedToday),
            'level_coins' => (int) config('projectiles.level_coins'),
            'win_bonus' => (int) config('projectiles.win_bonus'),
        ]);
    }

    private function jsonResponse($user, int $coinsDelta, bool $capped, bool $capOnly): JsonResponse
    {
        $dailyCap = $this->dailyCap->dailyCap();
        $earnedToday = $this->dailyCap->earnedToday($user->id);
        $balance = $this->coins->getBalance($user);

        return response()->json([
            'coins' => $balance,
            'coins_delta' => $coinsDelta,
            'total_coins' => $balance,
            'daily_earned' => $earnedToday,
            'daily_cap' => $dailyCap,
            'capped' => $capped || $capOnly,
            'message' => $capOnly
                ? 'Has alcanzado el tope diario de monedas en este juego.'
                : null,
        ]);
    }

}
