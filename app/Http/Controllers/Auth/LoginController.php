<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AllowedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Siempre mostramos la vista de login SIN cerrar sesión global.
        // Esto permite que otra pestaña se quede en login sin afectar la pestaña principal.
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
            return redirect()->route('login')->withErrors(['login' => 'Este correo no tiene acceso autorizado o está desactivado.']);
        }

        \Log::info('Intento de login', ['field' => 'email', 'value' => $email]);

        if (Auth::attempt(['email' => $email, 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            $token = bin2hex(random_bytes(32));
            session(["tab_token_" . auth()->id() => $token]);

            // Redirigir SIEMPRE con token en la URL desde el inicio
            return redirect()->to(URL::withTabToken('dashboard'));
        }

        return redirect()->route('login')->withErrors(['login' => 'Credenciales incorrectas.']);
    }
    protected function authenticated(Request $request, $user)
    {
        $intended = session('intended_url');
        session()->forget('intended_url');

        // Siempre generamos/actualizamos token al salir del flujo de autenticación
        $token = bin2hex(random_bytes(32));
        session(["tab_token_" . $user->id => $token]);

        // Si había URL previa, podrías adaptarlo; por simplicidad mandamos siempre al dashboard con token
        return redirect()->to(URL::withTabToken('dashboard'));
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
        \Log::info('Iniciando logout', ['user_id' => auth()->id() ?? 'none']);
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        \Session::forget('oauth_token'); // Para Google
        \Session::save();
        \Log::info('Sesión invalidada completamente');
        return redirect()->route('login')
            ->with('status', 'Sesión cerrada exitosamente.')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Clear-Site-Data', '"cache", "cookies", "storage"');
    }
}