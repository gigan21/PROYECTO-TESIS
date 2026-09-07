<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

  public function store(Request $request): RedirectResponse
    {
        // 1. Validamos los datos recibidos (incluyendo el paralelo)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'classroom' => ['required', 'string', 'in:4A,4B,4C'], // <-- Campo obligatorio
        ]);

        // 2. Transacción de base de datos
        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'estudiante',
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'classroom' => $validated['classroom'], // <-- AQUÍ SE CORRIGE EL ERROR
                'xp_points' => 0,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('estudiante.inicio');
    }
}


