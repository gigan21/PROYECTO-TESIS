<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChartRace\ChartRaceRewardRequest;
use App\Models\ChartRaceSession;
use App\Models\User;
use App\Services\Gamification\CoinService;
use App\Services\Gamification\XpAwardService;
use App\Services\Games\ChartRaceRewardCalculator;
use App\Services\Games\ChartRaceSessionRecorder;
use App\Services\Games\GamesDailyCoinCapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChartRaceGameController extends Controller
{
    public function __construct(
        private readonly CoinService $coins,
        private readonly XpAwardService $xp,
        private readonly ChartRaceRewardCalculator $calculator,
        private readonly ChartRaceSessionRecorder $recorder,
        private readonly GamesDailyCoinCapService $dailyCap,
    ) {}

    public function reward(ChartRaceRewardRequest $request): JsonResponse
    {
        $request->validated();

        $user = $request->user();
        /** @var array<string, mixed> $telemetry */
        $telemetry = $request->input('telemetry', []);
        $sessionId = (string) ($telemetry['sessionId'] ?? '');

        $duplicate = ChartRaceSession::query()->where('session_id', $sessionId)->exists();

        try {
            $this->recorder->upsert($user, $telemetry);
            $events = $telemetry['events'] ?? [];
            if (is_array($events)) {
                $this->recorder->syncEvents($user, $sessionId, $events);
            }
        } catch (InvalidArgumentException) {
            return $this->jsonPayload($user, 0, 0, false, false);
        }

        if ($duplicate) {
            return $this->jsonPayload($user, 0, 0, false, true);
        }

        [$requestedCoins, $requestedXp] = $this->calculator->calculate($telemetry);

        $cap = $this->dailyCap->applyCap($user->id, $requestedCoins);
        $toAward = $cap['to_award'];
        $capped = (bool) $cap['capped'];

        if ($requestedXp > 0) {
            $this->xp->award($user, $requestedXp);
        }

        if ($toAward > 0) {
            $this->coins->addCoins(
                user: $user,
                amount: $toAward,
                source: 'chart_race',
                description: 'Carrera de Gráficas — partida ('.$sessionId.')',
                metadata: [
                    'session_id' => $sessionId,
                    'hits' => (int) ($telemetry['hits'] ?? 0),
                    'misses' => (int) ($telemetry['misses'] ?? 0),
                ],
            );
        }

        return $this->jsonPayload($user, $toAward, $requestedXp, $capped, false);
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

    private function jsonPayload(User $user, int $coinsDelta, int $xpDelta, bool $capped, bool $duplicate): JsonResponse
    {
        $user->loadMissing('studentProfile');
        $earned = $this->dailyCap->earnedToday($user->id);
        $cap = $this->dailyCap->dailyCap();
        $balance = $this->coins->getBalance($user);

        return response()->json([
            'coins_delta' => $coinsDelta,
            'xp_delta' => $xpDelta,
            'total_coins' => $balance,
            'coins' => $balance,
            'xp' => (int) ($user->studentProfile?->xp_points ?? 0),
            'capped' => $capped,
            'duplicate' => $duplicate,
            'daily_earned' => $earned,
            'daily_cap' => $cap,
        ]);
    }
}
