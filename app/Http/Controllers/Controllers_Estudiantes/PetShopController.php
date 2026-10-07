<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Shop\PetShopService;
use App\Support\PetCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PetShopController extends Controller
{
    public function __construct(
        private readonly PetShopService $pets,
    ) {}

    /** Compra una mascota. */
    public function buy(Request $request, string $petId): JsonResponse
    {
        $user = $request->user();

        try {
            $this->pets->purchase($user, $petId);

            return response()->json([
                'ok' => true,
                'message' => '¡Mascota comprada!',
                'coins' => $user->studentProfile->fresh()->coins,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /** Equipa una mascota. */
    public function equip(Request $request, string $petId): JsonResponse
    {
        $user = $request->user();

        try {
            $this->pets->equip($user, $petId);

            return response()->json([
                'ok' => true,
                'message' => '¡Mascota equipada!',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /** Quita la mascota activa. */
    public function unequip(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $this->pets->unequip($user);

            return response()->json([
                'ok' => true,
                'message' => 'Mascota ocultada.',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}