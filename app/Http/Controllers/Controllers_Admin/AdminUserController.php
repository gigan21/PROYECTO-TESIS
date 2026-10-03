<?php

namespace App\Http\Controllers\Controllers_Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('studentProfile')
            ->whereIn('role', ['docente', 'estudiante']);

        // Buscar
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtrar por rol
        if ($request->filled('role') &&
            in_array($request->role, ['docente', 'estudiante'])) {

            $query->where('role', $request->role);
        }

        // Filtrar por paralelo
        if ($request->filled('classroom') &&
            in_array($request->classroom, ['4A', '4B', '4C'])) {

            $query->where('role', 'estudiante')
                ->whereHas('studentProfile', function ($q) use ($request) {
                    $q->where('classroom', $request->classroom);
                });
        }

        $usuarios = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('admin.usuarios.create');
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                'in:docente,estudiante',
            ],

            'classroom' => [
                'nullable',
                'required_if:role,estudiante',
                'in:4A,4B,4C',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            if ($validated['role'] === 'estudiante') {
                StudentProfile::create([
                    'user_id' => $user->id,
                    'classroom' => $validated['classroom'],
                    'xp_points' => 0,
                    'coins' => 0,
                ]);
            }
        });

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        abort_if($usuario->role === 'admin', 403);

        $usuario->load('studentProfile');

        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        abort_if($usuario->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $usuario->id,
            ],

            'role' => [
                'required',
                'in:docente,estudiante',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'classroom' => [
                'nullable',
                'required_if:role,estudiante',
                'in:4A,4B,4C',
            ],
        ]);

        DB::transaction(function () use ($validated, $usuario) {

            $usuario->name = $validated['name'];
            $usuario->email = $validated['email'];
            $usuario->role = $validated['role'];

            if (!empty($validated['password'])) {
                $usuario->password = $validated['password'];
            }

            $usuario->save();

            if ($validated['role'] === 'estudiante') {

                StudentProfile::updateOrCreate(
                    ['user_id' => $usuario->id],
                    [
                        'classroom' => $validated['classroom'],
                    ]
                );

            } else {

                StudentProfile::where('user_id', $usuario->id)->delete();
            }
        });

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
{
    if ($usuario->role !== 'estudiante') {
        return back()->with(
            'error',
            'Solo se pueden eliminar cuentas de estudiantes.'
        );
    }

    $usuario->delete();

    return back()->with(
        'success',
        'El estudiante fue eliminado correctamente.'
    );
}

    public function activar(User $usuario)
{
    if ($usuario->role !== 'docente') {
        return back()->with('error', 'Solo se pueden activar cuentas de docentes.');
    }

    $usuario->update([
        'is_active' => true,
    ]);

    return back()->with(
        'success',
        'El docente fue activado correctamente.'
    );
}
public function desactivar(User $usuario) {
    if ($usuario->role !== 'docente') {
        return back()->with('error', 'Solo se pueden desactivar cuentas de docentes.');
    }

    $usuario->update([
        'is_active' => false,
    ]);

    return back()->with(
        'success',
        'El docente fue desactivado correctamente.'
    );
}

}