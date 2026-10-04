<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Gamification\RankingService;
use App\Services\Gamification\StudentBadgeService;
use App\Services\Gamification\StudentProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentHomeController extends Controller
{
    public function __construct(
        private readonly StudentProgressService $progress,
        private readonly StudentBadgeService $badges,
        private readonly RankingService $ranking
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->studentProfile;

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

        return view('estudiante.inicio', [
            'progress'      => $progress,
            'badges'        => $this->badges->displayItems($user),
            'localRanking'  => $localRanking,
            'globalRanking' => $globalRanking,
            'myLocal'       => $myLocal,
            'myGlobal'      => $myGlobal,
            'xpToNext'      => $xpToNext,
        ]);
    }
}