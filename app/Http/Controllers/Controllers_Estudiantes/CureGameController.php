<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Cure\CureGameRewardRequest;
use App\Services\Gamification\CoinService;
use App\Services\Gamification\XpAwardService;
use App\Services\Games\CureCoinRewardCalculator;
use App\Services\Games\CureLevelRewardGuard;
use App\Services\Games\CureSessionRecorder;
use App\Services\Games\GamesDailyCoinCapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CureGameController extends Controller
{
    public function __construct(
        private readonly CoinService $coins,
        private readonly XpAwardService $xp,
        private readonly CureCoinRewardCalculator $calculator,
        private readonly CureSessionRecorder $recorder,
        private readonly CureLevelRewardGuard $levelRewardGuard,
        private readonly GamesDailyCoinCapService $dailyCap,
    ) {}

    public function reward(CureGameRewardRequest $request): JsonResponse
    {
        $request->validated();

        $user = $request->user();
        /** @var array<string, mixed> $telemetry */
        $telemetry = $request->input('telemetry', []);
        $sessionId = (string) ($telemetry['sessionId'] ?? '');

        $nivelesCompletados = (int) ($telemetry['nivelesCompletados'] ?? 0);
        $abandoned = filter_var($telemetry['abandoned'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($nivelesCompletados < 1) {
            return $this->jsonPayload($user, 0, 0, false, false, $abandoned);
        }

        $finished = filter_var($telemetry['finished'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            $this->recorder->upsert($user, $telemetry);
        } catch (InvalidArgumentException) {
            return $this->jsonPayload($user, 0, 0, false, false, $abandoned);
        }

        $coinsDelta = 0;
        $xpDelta = 0;
        $capped = false;
        $duplicate = false;

        if (! $finished) {
            [$coinsDelta, $xpDelta, $capped, $duplicate] = $this->awardLevelReward(
                $user,
                $sessionId,
                (int) ($telemetry['levelCompleted'] ?? $nivelesCompletados),
                (int) config('cure.coins_per_level', 1),
                (int) config('cure.xp_per_level', 1),
            );
        } elseif ($abandoned) {
            // Caso C: solo persistencia; recompensas ya otorgadas en parciales.
        } else {
            // Caso B: final normal (victoria o derrota).
            $lastLevel = (int) ($telemetry['levelCompleted'] ?? $nivelesCompletados);
            if ($lastLevel >= 1 && $lastLevel <= 4) {
                [$cPartial, $xPartial, $capPartial, $dupPartial] = $this->awardLevelReward(
                    $user,
                    $sessionId,
                    $lastLevel,
                    (int) config('cure.coins_per_level', 1),
                    (int) config('cure.xp_per_level', 1),
                );
                $coinsDelta += $cPartial;
                $xpDelta += $xPartial;
                $capped = $capped || $capPartial;
                $duplicate = $duplicate && $dupPartial;
            }

            if ($this->calculator->countsAsWin($telemetry)) {
                [$cBonus, $xBonus, $capBonus, $dupBonus] = $this->awardLevelReward(
                    $user,
                    $sessionId,
                    (int) config('cure.bonus_level', 5),
                    (int) config('cure.coins_win_bonus', 5),
                    (int) config('cure.xp_win_bonus', 5),
                );
                $coinsDelta += $cBonus;
                $xpDelta += $xBonus;
                $capped = $capped || $capBonus;
                if ($cBonus === 0 && $xBonus === 0 && $dupBonus) {
                    $duplicate = true;
                }
            }
        }

        return $this->jsonPayload($user, $coinsDelta, $xpDelta, $capped, $duplicate, $abandoned);
    }

    /**
     * @return array{0: int, 1: int, 2: bool, 3: bool} coins, xp, capped, duplicate
     */
    private function awardLevelReward(
        User $user,
        string $sessionId,
        int $level,
        int $requestedCoins,
        int $requestedXp,
    ): array {
        if ($requestedCoins <= 0 && $requestedXp <= 0) {
            return [0, 0, false, false];
        }

        $cap = $this->dailyCap->applyCap($user->id, $requestedCoins);
        $toAward = $cap['to_award'];
        $capped = (bool) $cap['capped'];

        if (! $this->levelRewardGuard->tryClaim($user, $sessionId, $level, $toAward, $requestedXp)) {
            return [0, 0, false, true];
        }

        if ($requestedXp > 0) {
            $this->xp->award($user, $requestedXp);
        }

        if ($toAward > 0) {
            $this->coins->addCoins(
                user: $user,
                amount: $toAward,
                source: 'cure',
                description: 'Lanza la Cura — nivel '.$level.' ('.$sessionId.')',
                metadata: [
                    'session_id' => $sessionId,
                    'level' => $level,
                ],
            );
        }

        return [$toAward, $requestedXp, $capped, false];
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->studentProfile;
        $earned = $this->dailyCap->earnedToday($user->id);
        $cap = $this->dailyCap->dailyCap();

        return response()->json([
            'coins' => $this->coins->getBalance($user),
            'xp' => (int) ($profile?->xp_points ?? 0),
            'daily_earned' => $earned,
            'daily_cap' => $cap,
            'daily_remaining' => max(0, $cap - $earned),
        ]);
    }

    private function jsonPayload(
        $user,
        int $coinsDelta,
        int $xpDelta,
        bool $capped,
        bool $duplicate,
        bool $abandoned = false,
    ): JsonResponse {
        $user->loadMissing('studentProfile');
        $earned = $this->dailyCap->earnedToday($user->id);
        $cap = $this->dailyCap->dailyCap();
        $balance = $this->coins->getBalance($user);

        if ($abandoned) {
            $coinsDelta = 0;
            $xpDelta = 0;
        }

        return response()->json([
            'coins_delta' => $coinsDelta,
            'xp_delta' => $xpDelta,
            'total_coins' => $balance,
            'coins' => $balance,
            'xp' => (int) ($user->studentProfile?->xp_points ?? 0),
            'capped' => $capped,
            'duplicate' => $duplicate,
            'abandoned' => $abandoned,
            'daily_earned' => $earned,
            'daily_cap' => $cap,
        ]);
    }
}
