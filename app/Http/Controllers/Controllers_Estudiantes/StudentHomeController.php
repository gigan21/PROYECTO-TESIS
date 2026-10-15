<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Gamification\RankingService;
use App\Services\Gamification\StudentBadgeService;
use App\Services\Gamification\StudentProgressService;
use App\Services\Analytics\StudentStruggleDetector;
use App\Services\ML\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Support\PetCatalog;
use App\Models\LoreVideo;
use Throwable;
class StudentHomeController extends Controller
{
    public function __construct(
        private readonly StudentProgressService $progress,
        private readonly StudentBadgeService $badges,
        private readonly RankingService $ranking,
        private readonly RecommendationService $recommendations,
        private readonly StudentStruggleDetector $struggleDetector,
    ) {}
  
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->studentProfile;
        $loreVideos = LoreVideo::active()->get();
        // Si todavía no tiene perfil, lo creamos
        if (!$profile) {
            $profile = $user->studentProfile()->create([
                'xp_points' => 0,
                'coins' => 0,
                'classroom' => 'Sin asignar',
            ]);
        }

        $progress = $this->progress->forUser($user);
        $this->badges->syncForUser($user);

        // Datos del ranking
        $localRanking  = $this->ranking->local($profile->classroom ?? 'Sin asignar');
        $globalRanking = $this->ranking->global();
        $myLocal       = $this->ranking->positionLocal($profile);
        $myGlobal      = $this->ranking->positionGlobal($profile);
        $xpToNext      = $this->ranking->xpToNextLocal($profile);

        $recommendation = null;
        try {
            // 🔗 FASE 6
            $recommendation = $this->recommendations->getForUser($user);
        } catch (Throwable) {
            $recommendation = null;
        }

        return view('estudiante.inicio', [
            'progress'      => $progress,
            'badges'        => $this->badges->displayItems($user),
            'localRanking'  => $localRanking,
            'globalRanking' => $globalRanking,
            'myLocal'       => $myLocal,
            'myGlobal'      => $myGlobal,
            'xpToNext'      => $xpToNext,
            'activePetId' => PetCatalog::activeFor($user),
            'loreVideos' => $loreVideos,
            'recommendation' => $recommendation,
            'struggleAlerts' => $this->struggleDetector->detect($user),
        ]);
        

    }
}