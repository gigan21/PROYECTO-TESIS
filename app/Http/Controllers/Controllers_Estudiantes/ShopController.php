<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Models\ShopItem;
use App\Services\Shop\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shop,
    ) {}

    /**
     * Muestra la tienda principal.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Todos los ítems activos, agrupados por categoría
        $catalog = $this->shop->getCatalog();
        $grouped = $catalog->groupBy('category');

        // Qué ítems tiene ya el estudiante
        $ownedIds = $this->shop->ownedItemIds($user);

        // Banner y avatar actualmente equipados
        $equippedBanner = $this->shop->getEquipped($user, 'banner');

               // Mascotas (desde config, no desde shop_items)
               $pets = \App\Support\PetCatalog::allForShop();
               $ownedPets = \App\Support\PetCatalog::ownedIdsFor($user);
               $activePet = \App\Support\PetCatalog::activeFor($user);
       
               return view('estudiante.tienda.index', [
                   'user' => $user,
                   'banners' => $grouped->get('banner', collect()),
                   'avatars' => $grouped->get('avatar', collect()),
                   'pets' => $pets,
                   'ownedIds' => $ownedIds,
                   'equippedBanner' => $equippedBanner,
                   'ownedPets' => $ownedPets,
                   'activePet' => $activePet,
               ]);
    }

    /**
     * Compra un ítem.
     */
    public function buy(Request $request, ShopItem $item): JsonResponse
    {
        $user = $request->user();

        try {
            $this->shop->purchase($user, $item);

            return response()->json([
                'ok' => true,
                'message' => "¡Compraste {$item->name}!",
                'coins' => $user->studentProfile->fresh()->coins,
                'coins_delta' => -$item->price,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Equipa un ítem.
     */
    public function equip(Request $request, ShopItem $item): JsonResponse
    {
        $user = $request->user();

        try {
            $this->shop->equip($user, $item);

            return response()->json([
                'ok' => true,
                'message' => "¡{$item->name} equipado!",
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Desequipa un ítem (por ahora, solo banners).
     */
    public function unequip(Request $request, ShopItem $item): JsonResponse
    {
        $user = $request->user();

        try {
            $this->shop->unequip($user, $item);

            return response()->json([
                'ok' => true,
                'message' => "Has quitado {$item->name}.",
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}