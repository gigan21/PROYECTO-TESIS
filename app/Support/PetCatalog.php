<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

class PetCatalog
{
    /** Devuelve todo el catálogo. */
    public static function all(): array
    {
        return config('pets.catalog', []);
    }

    /** Busca una mascota por ID. */
    public static function find(string $id): ?array
    {
        return self::all()[$id] ?? null;
    }

    /** IDs de mascotas gratuitas (price = 0). */
    public static function freeIds(): array
    {
        return array_keys(array_filter(
            self::all(),
            fn ($p) => (int) ($p['price'] ?? 0) === 0
        ));
    }

    /** IDs de mascotas que el usuario posee (gratis + compradas). */
    public static function ownedIdsFor(User $user): array
    {
        $bought = $user->pets()->pluck('pet_id')->all();

        return array_values(array_unique(array_merge(self::freeIds(), $bought)));
    }

    /** Mascota activa del usuario (o null). */
    public static function activeFor(User $user): ?string
    {
        $active = $user->studentProfile?->active_pet_id;

        if ($active && self::find($active)) {
            return $active;
        }

        // Fallback: la primera mascota que tenga
        $owned = self::ownedIdsFor($user);

        return $owned[0] ?? null;
    }

    /** Todas las mascotas como array listo para la tienda. */
    public static function allForShop(): array
    {
        return collect(self::all())
            ->map(fn ($pet, $id) => ['id' => $id] + $pet)
            ->values()
            ->all();
    }
}