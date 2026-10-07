<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\CoinTransaction;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoinService
{
    /**
     * Suma monedas al perfil del usuario y registra la transacción.
     *
     * @return int Nuevo saldo
     */
    public function addCoins(
        User $user,
        int $amount,
        string $source,
        ?string $description = null,
        array $metadata = []
    ): int {
        if ($amount <= 0) {
            throw new RuntimeException('El monto a sumar debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($user, $amount, $source, $description, $metadata) {
            $profile = $this->lockProfile($user);

            $profile->coins = ($profile->coins ?? 0) + $amount;
            $profile->save();

            $this->logTransaction(
                user: $user,
                amount: $amount,
                source: $source,
                description: $description,
                balanceAfter: $profile->coins,
                metadata: $metadata
            );

            return $profile->coins;
        });
    }

    /**
     * Gasta monedas del perfil. Lanza excepción si no alcanza.
     *
     * @return int Nuevo saldo
     */
    public function spendCoins(
        User $user,
        int $amount,
        string $source,
        ?string $description = null,
        array $metadata = []
    ): int {
        if ($amount <= 0) {
            throw new RuntimeException('El monto a gastar debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($user, $amount, $source, $description, $metadata) {
            $profile = $this->lockProfile($user);

            if (($profile->coins ?? 0) < $amount) {
                throw new RuntimeException('Monedas insuficientes.');
            }

            $profile->coins -= $amount;
            $profile->save();

            $this->logTransaction(
                user: $user,
                amount: -$amount,
                source: $source,
                description: $description,
                balanceAfter: $profile->coins,
                metadata: $metadata
            );

            return $profile->coins;
        });
    }

    /**
     * Devuelve el saldo actual del usuario (0 si no tiene perfil).
     */
    public function getBalance(User $user): int
    {
        return (int) ($user->studentProfile?->coins ?? 0);
    }

    /**
     * Historial de transacciones del usuario.
     */
    public function getHistory(User $user, int $limit = 20): Collection
    {
        return CoinTransaction::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Bloquea el perfil para evitar condiciones de carrera.
     */
    private function lockProfile(User $user): StudentProfile
    {
        $profile = StudentProfile::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if ($profile === null) {
            // Crear si no existe
            $profile = StudentProfile::query()->create([
                'user_id' => $user->id,
                'xp_points' => 0,
                'coins' => 0,
            ]);

            // Volver a bloquear tras crear
            $profile = StudentProfile::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
        }

        return $profile;
    }

    private function logTransaction(
        User $user,
        int $amount,
        string $source,
        ?string $description,
        int $balanceAfter,
        array $metadata
    ): void {
        CoinTransaction::query()->create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source' => $source,
            'description' => $description,
            'balance_after' => $balanceAfter,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}