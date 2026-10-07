<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Models\ShopItem;
use App\Models\StudentItem;
use App\Models\User;
use App\Services\Gamification\CoinService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use RuntimeException;

class ShopService
{
    public function __construct(
        private readonly CoinService $coins,
    ) {}

    /**
     * Lista los ítems activos de la tienda, agrupados por categoría.
     */
    public function getCatalog(): Collection
    {
        return ShopItem::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Ítems que el estudiante YA tiene (por ID).
     *
     * @return array<int>
     */
    public function ownedItemIds(User $user): array
    {
        return StudentItem::query()
            ->where('user_id', $user->id)
            ->pluck('shop_item_id')
            ->all();
    }

    /**
     * ¿El usuario ya tiene este ítem?
     */
    public function ownsItem(User $user, ShopItem $item): bool
    {
        return StudentItem::query()
            ->where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->exists();
    }

    /**
     * Compra un ítem. Lanza excepción si algo falla.
     */
    public function purchase(User $user, ShopItem $item): StudentItem
    {
        if (! $item->is_active) {
            throw new RuntimeException('Este ítem no está disponible.');
        }

        if ($this->ownsItem($user, $item)) {
            throw new RuntimeException('Ya tienes este ítem.');
        }

        return DB::transaction(function () use ($user, $item) {
            // 1. Gastar monedas (esto ya guarda la transacción en coin_transactions)
            $this->coins->spendCoins(
                user: $user,
                amount: $item->price,
                source: 'shop',
                description: "Compra: {$item->name}",
                metadata: [
                    'item_id' => $item->id,
                    'slug' => $item->slug,
                    'category' => $item->category,
                ]
            );

            // 2. Añadir al inventario
            return StudentItem::query()->create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
                'obtained_at' => now(),
            ]);
        });
    }

    /**
     * Equipa un ítem (por ahora, solo banners y avatares).
     */
    public function equip(User $user, ShopItem $item): void
    {
        if (! $this->ownsItem($user, $item)) {
            throw new RuntimeException('No tienes este ítem.');
        }

        $profile = $user->studentProfile;

        if ($profile === null) {
            throw new RuntimeException('No tienes perfil de estudiante.');
        }

        match ($item->category) {
            'banner' => $profile->update(['banner_id' => $item->id]),
            'avatar' => $this->equipAvatar($user, $item),
            default => throw new RuntimeException("No se puede equipar un ítem de tipo {$item->category}."),
        };
    }

    /**
     * Desequipa un ítem (por ahora, solo banners).
     */
    public function unequip(User $user, ShopItem $item): void
    {
        $profile = $user->studentProfile;

        if ($profile === null) {
            return;
        }

        match ($item->category) {
            'banner' => $profile->update(['banner_id' => null]),
            default => null,
        };
    }

    /**
     * Equipa un avatar: copia el nombre del archivo al campo avatar_name.
     */
    private function equipAvatar(User $user, ShopItem $item): void
    {
        $fileName = basename($item->media_path); // "avatartienda1.jpg"
        $user->studentProfile->update(['avatar_name' => $fileName]);
    }

    /**
     * Devuelve el ítem equipado actualmente en una categoría dada.
     */
    public function getEquipped(User $user, string $category): ?ShopItem
    {
        $profile = $user->studentProfile;

        if ($profile === null) {
            return null;
        }

        return match ($category) {
            'banner' => $profile->banner,
            default => null,
        };
    }

    /**
     * Verifica si el usuario puede comprar el ítem (tiene monedas, no lo posee).
     *
     * @return array{can_buy: bool, reason: ?string}
     */
    public function canBuy(User $user, ShopItem $item): array
    {
        if (! $item->is_active) {
            return ['can_buy' => false, 'reason' => 'No disponible'];
        }

        if ($this->ownsItem($user, $item)) {
            return ['can_buy' => false, 'reason' => 'Ya lo tienes'];
        }

        if (! $user->studentProfile?->hasCoins($item->price)) {
            return ['can_buy' => false, 'reason' => 'Monedas insuficientes'];
        }

        return ['can_buy' => true, 'reason' => null];
    }
}