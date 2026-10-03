<?php

namespace App\Http\Controllers\Controllers_Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsuarios = User::count();

        $totalDocentes = User::where('role', 'docente')->count();

        $totalEstudiantes = User::where('role', 'estudiante')->count();

        $estudiantes4A = User::where('role', 'estudiante')
            ->whereHas('studentProfile', function ($query) {
                $query->where('classroom', '4A');
            })
            ->count();

        $estudiantes4B = User::where('role', 'estudiante')
            ->whereHas('studentProfile', function ($query) {
                $query->where('classroom', '4B');
            })
            ->count();

        $estudiantes4C = User::where('role', 'estudiante')
            ->whereHas('studentProfile', function ($query) {
                $query->where('classroom', '4C');
            })
            ->count();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalDocentes',
            'totalEstudiantes',
            'estudiantes4A',
            'estudiantes4B',
            'estudiantes4C'
        ));
    }
}