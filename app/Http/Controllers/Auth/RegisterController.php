<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AllowedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use GuzzleHttp\Client;
use Exception;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Maneja el registro de un nuevo usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $email = $request->email;

        // Verifica si el email está autorizado
        $allowedUser = AllowedUser::where('email', $email)->first();
        if (!$allowedUser || !$allowedUser->is_active) {
            \Log::warning('Intento de registro no autorizado', ['email' => $email]);
            return redirect()->back()->with('error', 'Este correo no tiene acceso autorizado o está desactivado.');
        }

        try {
            // Crea el nuevo usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'auth_method' => 'email',
                'is_superadmin' => $allowedUser->is_superadmin ?? false, // Sincroniza desde allowed_user si aplica
            ]);

            // Sube la imagen a Imgur si se proporciona
            if ($request->hasFile('img')) {
                $client = new Client();
                $imageContent = file_get_contents($request->file('img')->getRealPath());
                $response = $client->post('https://api.imgur.com/3/image', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('IMGUR_ACCESS_TOKEN'),
                    ],
                    'multipart' => [
                        [
                            'name' => 'image',
                            'contents' => $imageContent,
                            'filename' => 'profile_' . time() . '_' . $user->id . '.jpg'
                        ],
                        ['name' => 'privacy', 'contents' => 'hidden'],
                        ['name' => 'title', 'contents' => 'Profile Image'],
                        ['name' => 'description', 'contents' => 'User profile image']
                    ]
                ]);

                $data = json_decode($response->getBody(), true);
                if (isset($data['data']['link'])) {
                    $user->update(['img' => $data['data']['link']]);
                    \Log::info('Imagen subida durante registro', ['user_id' => $user->id, 'img' => $data['data']['link']]);
                } else {
                    \Log::error('Error al subir imagen durante registro', ['response' => $data]);
                    // No fallar el registro por la imagen, solo loguear
                }
            }

            // Inicia sesión automáticamente
            Auth::login($user);
            \Log::info('Registro y login exitoso', ['user_id' => $user->id]);

            // Genera token de pestaña y redirige al dashboard con token en URL
            $token = bin2hex(random_bytes(32));
            session(["tab_token_" . $user->id => $token]);

            return redirect()->to(\Illuminate\Support\Facades\URL::withTabToken('dashboard'))
                ->with('success', 'Cuenta creada exitosamente.');
        } catch (Exception $e) {
            \Log::error('Error durante el registro', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Algo salió mal durante el registro: ' . $e->getMessage());
        }
    }
}