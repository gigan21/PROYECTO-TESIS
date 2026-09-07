<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendResetPinMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class ResetPasswordPinController extends Controller
{
    // 1. Muestra el formulario para pedir el correo
    public function showEmailForm()
    {
        return view('auth.forgot-pin-email');
    }

    // 2. Genera y envía el PIN por correo
    public function sendPin(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Generar PIN de 6 dígitos numéricos
        $pin = random_int(100000, 999999);

        // Guardar o reemplazar en la BD
        DB::table('password_reset_pins')->where('email', $request->email)->delete();
        DB::table('password_reset_pins')->insert([
            'email' => $request->email,
            'pin' => $pin,
            'created_at' => Carbon::now()
        ]);

        // Enviar por Mail
        Mail::to($request->email)->send(new SendResetPinMail($pin));

        return redirect()->route('password.pin.verify.form', ['email' => $request->email])
            ->with('status', 'Hemos enviado un PIN de 6 dígitos a tu correo electrónico.');
    }

    // 3. Muestra la vista para ingresar el PIN y la nueva contraseña
    public function showVerifyForm(Request $request)
    {
        return view('auth.forgot-pin-verify', ['email' => $request->email]);
    }

    // 4. Valida el PIN y cambia la contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'pin' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Buscar registro del PIN
        $resetRecord = DB::table('password_reset_pins')
            ->where('email', $request->email)
            ->where('pin', $request->pin)
            ->first();

        // Validar si existe y si no expiró (más de 15 minutos)
        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            return back()->withErrors(['pin' => 'El PIN ingresado es incorrecto o ha expirado.']);
        }

        // Actualizar contraseña del usuario (sea docente o estudiante)
        $user = User::where('email', $request->email)->first();
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        // Eliminar el PIN usado
        DB::table('password_reset_pins')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', '¡Tu contraseña ha sido actualizada con éxito! Ya puedes iniciar sesión.');
    }
}