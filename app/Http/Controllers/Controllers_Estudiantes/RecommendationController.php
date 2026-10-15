<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Analytics\StudentStruggleDetector;
use App\Services\ML\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendations,
        private readonly StudentStruggleDetector $struggleDetector,
    ) {}

    public function show(Request $request): View
    {
        $recommendation = null;
        try {
            $recommendation = $this->recommendations->getForUser($request->user());
        } catch (Throwable) {
            $recommendation = null;
        }

        return view('estudiante.recomendacion', [
            'recommendation' => $recommendation,
            'struggleAlerts' => $this->struggleDetector->detect($request->user()),
        ]);
    }

    public function refresh(Request $request): RedirectResponse
    {
        try {
            $recommendation = $this->recommendations->getForUser($request->user(), true);
        } catch (Throwable) {
            $recommendation = null;
        }

        if ($recommendation === null) {
            return back()->with('recommendation_notice', 'Vuelve pronto para ver tu recomendación.');
        }

        return back()->with('recommendation_notice', 'Recomendación actualizada.');
    }
}
