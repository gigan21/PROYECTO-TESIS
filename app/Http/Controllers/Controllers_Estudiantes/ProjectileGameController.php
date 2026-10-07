<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Services\Gamification\CoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectileGameController extends Controller
{
    public function __construct(
        private readonly CoinService $coins,
    ) {}

    /**
     * Se llama al superar un nivel (o al terminar la partida ganando).
     *
     * Body esperado:
     *   - level:    int (1..5)  Nivel recién superado
     *   - score:    int         Puntos acumulados al terminar el nivel
     *   - finished: bool        true si es el nivel 5 (partida completa)
     *   - is_win:   bool        true si ganó, false si perdió (para estadística)
     */
    public function reward(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level'    => ['required', 'integer', 'min:1', 'max:5'],
            'score'    => ['required', 'integer', 'min:0'],
            'finished' => ['required', 'boolean'],
            'is_win'   => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $level = (int) $validated['level'];
        $score = (int) $validated['score'];
        $finished = (bool) $validated['finished'];
        $isWin = (bool) $validated['is_win'];

        // 1) Cuántas monedas corresponden por el rango de puntos
        $baseCoins = $this->coinsForScore($score);

        // 2) Bonus si terminó los 5 niveles con victoria
        $bonusCoins = ($finished && $isWin) ? (int) config('projectiles.win_bonus') : 0;

        // 3) Aplicar tope diario
        $dailyCap = (int) config('projectiles.daily_cap');
        $alreadyEarnedToday = $this->coinsEarnedToday($user->id);
        $available = max(0, $dailyCap - $alreadyEarnedToday);

        $requested = $baseCoins + $bonusCoins;
        $toAward = min($requested, $available);

        // 4) Si no se puede dar nada, responder sin error
        if ($toAward <= 0) {
            return response()->json([
                'coins' => $this->coins->getBalance($user),
                'coins_delta' => 0,
                'daily_earned' => $alreadyEarnedToday,
                'daily_cap' => $dailyCap,
                'capped' => true,
                'message' => 'Has alcanzado el tope diario de monedas en este juego.',
            ]);
        }

        // 5) Registrar las monedas con el CoinService
        $newBalance = $this->coins->addCoins(
            user: $user,
            amount: $toAward,
            source: 'projectiles',
            description: "Simulador de proyectiles — nivel {$level} ({$score} pts)",
            metadata: [
                'level' => $level,
                'score' => $score,
                'finished' => $finished,
                'is_win' => $isWin,
                'base_coins' => $baseCoins,
                'bonus_coins' => $bonusCoins,
                'capped' => $toAward < $requested,
            ]
        );

        return response()->json([
            'coins' => $newBalance,
            'coins_delta' => $toAward,
            'base_coins' => $baseCoins,
            'bonus_coins' => $bonusCoins,
            'daily_earned' => $alreadyEarnedToday + $toAward,
            'daily_cap' => $dailyCap,
            'capped' => $toAward < $requested,
        ]);
    }

    /**
     * Devuelve el estado de recompensas del usuario (para mostrarlo en el juego).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $dailyCap = (int) config('projectiles.daily_cap');
        $earnedToday = $this->coinsEarnedToday($user->id);

        return response()->json([
            'coins' => $this->coins->getBalance($user),
            'daily_earned' => $earnedToday,
            'daily_cap' => $dailyCap,
            'daily_remaining' => max(0, $dailyCap - $earnedToday),
            'rewards' => config('projectiles.rewards'),
            'win_bonus' => (int) config('projectiles.win_bonus'),
        ]);
    }

    /**
     * Calcula cuántas monedas corresponden por el puntaje.
     */
    private function coinsForScore(int $score): int
    {
        foreach (config('projectiles.rewards') as $range) {
            $min = (int) $range['min'];
            $max = $range['max'] === null ? PHP_INT_MAX : (int) $range['max'];

            if ($score >= $min && $score <= $max) {
                return (int) $range['coins'];
            }
        }

        return 0;
    }

    /**
     * Cuántas monedas ha ganado hoy el usuario en este juego.
     */
    private function coinsEarnedToday(int $userId): int
    {
        return (int) CoinTransaction::query()
            ->where('user_id', $userId)
            ->where('source', 'projectiles')
            ->where('amount', '>', 0)
            ->whereDate('created_at', today())
            ->sum('amount');
    }
}