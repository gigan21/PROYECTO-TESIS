<?php

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Gamification\XpAwardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
class GamificationController extends Controller
{
    public function __construct(
        private readonly XpAwardService $xpAwards
    ) {}

    /**
     * Otorga XP al estudiante al completar una lectura o actividad simple.
     */
    public function claimReadingXp(Request $request)
    {
        $rewardXp = 25;
        $this->xpAwards->award($request->user(), $rewardXp);

        return back()->with('gamification_status', "¡Felicidades! Ganaste +{$rewardXp} XP por completar la lectura de Cinemática.");
    }
    
// 

public function juegoProyectiles(): View
{
    return view('estudiante.juegos.proyectiles');
}
}