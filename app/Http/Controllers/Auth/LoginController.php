<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AllowedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Retorna la vista con cabeceras de no-caché
        return response()->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $email = $credentials['login'];

        // Verifica si el email está autorizado
        $allowedUser = AllowedUser::where('email', $email)->first();
        if (!$allowedUser || !$allowedUser->is_active) {
            \Log::warning('Intento de login no autorizado', ['email' => $email]);
            return redirect()->route('login')->withErrors(['login' => 'Este correo no tiene acceso autorizado o está desactivado.']);
        }

        \Log::info('Intento de login', ['field' => 'email', 'value' => $email]);

        if (Auth::attempt(['email' => $email, 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            \Log::info('Login exitoso', ['user_id' => auth()->id()]);
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('login')->withErrors(['login' => 'Credenciales incorrectas.']);
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return redirect()->route('profile.show')->with('success', 'Contraseña actualizada con éxito.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // Especificar el guard 'web'
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Limpiar manualmente el token de Google si está presente
        \Session::forget('oauth_token');
        \Session::save();
        return redirect()->route('login')
            ->with('status', 'Sesión cerrada exitosamente.')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Clear-Site-Data', '"cache", "cookies", "storage"'); // Limpiar datos del cliente
    }
}