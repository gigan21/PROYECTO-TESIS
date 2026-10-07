<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Models\User;
use App\Models\UserPet;
use App\Services\Gamification\CoinService;
use App\Support\PetCatalog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PetShopService
{
    public function __construct(
        private readonly CoinService $coins,
    ) {}

    public function owns(User $user, string $petId): bool
    {
        if (in_array($petId, PetCatalog::freeIds(), true)) {
            return true;
        }

        return UserPet::query()
            ->where('user_id', $user->id)
            ->where('pet_id', $petId)
            ->exists();
    }

    /**
     * Compra una mascota. Lanza excepción si algo falla.
     */
    public function purchase(User $user, string $petId): UserPet
    {
        $pet = PetCatalog::find($petId);

        if (! $pet) {
            throw new RuntimeException('Mascota no encontrada.');
        }

        if ($this->owns($user, $petId)) {
            throw new RuntimeException('Ya tienes esta mascota.');
        }

        $price = (int) ($pet['price'] ?? 0);

        if (! $user->studentProfile?->hasCoins($price)) {
            throw new RuntimeException('Monedas insuficientes.');
        }

        return DB::transaction(function () use ($user, $pet, $petId, $price) {
            // 1. Gastar monedas
            $this->coins->spendCoins(
                user: $user,
                amount: $price,
                source: 'shop',
                description: "Compra: Mascota {$pet['name']}",
                metadata: ['pet_id' => $petId, 'category' => 'pet'],
            );

            // 2. Añadir al inventario
            return UserPet::query()->create([
                'user_id' => $user->id,
                'pet_id' => $petId,
                'obtained_at' => now(),
            ]);
        });
    }

    /**
     * Equipa una mascota (solo una activa a la vez).
     */
    public function equip(User $user, string $petId): void
    {
        if (! $this->owns($user, $petId)) {
            throw new RuntimeException('No tienes esta mascota.');
        }

        if (! PetCatalog::find($petId)) {
            throw new RuntimeException('Mascota desconocida.');
        }

        $user->studentProfile->update(['active_pet_id' => $petId]);
    }

    /**
     * Quita la mascota activa (no muestra ninguna).
     */
    public function unequip(User $user): void
    {
        $user->studentProfile->update(['active_pet_id' => null]);
    }
}