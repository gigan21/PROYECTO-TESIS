<?php

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function edit(Request $request)
    {
        // Solo paralelos para 4to de Secundaria
        $classrooms = [
            '4to A',
            '4to B',
            '4to C',
        ];

        return view('estudiante.perfil', compact('classrooms'));
    }

    public function update(Request $request)
{
    // 1. Validar los datos enviados desde la vista de perfil
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'nickname' => ['nullable', 'string', 'max:50'],
        'avatar_name' => ['required', 'string'],
        'classroom' => ['required', 'string'],
    ]);

    $user = $request->user();

    // 2. Actualizar el nombre en la tabla 'users'
    $user->update([
        'name' => $request->name,
    ]);

    // 3. Actualizar o crear los datos gamificados en 'student_profiles'
    $user->studentProfile()->updateOrCreate(
        ['user_id' => $user->id],
        [
            'nickname' => $request->nickname ?? $request->name,
            'avatar_name' => $request->avatar_name, // <-- Aquí guardamos el avatar seleccionado (.jpg)
            'classroom' => $request->classroom,
        ]
    );

    return redirect()->route('student.profile')->with('status', 'profile-updated');
}
}