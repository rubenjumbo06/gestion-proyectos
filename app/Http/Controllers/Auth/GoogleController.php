<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AllowedUser;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use GuzzleHttp\Client;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    /**
     * Maneja el callback de Google después de la autenticación.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        \Log::info('Google Callback iniciado');

        // Cierra cualquier sesión existente
        if (Auth::check()) {
            Auth::logout();
            \Session::flush();
            \Log::info('Sesión existente cerrada antes del callback');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            \Log::info('Usuario de Google obtenido', ['email' => $googleUser->getEmail()]);
            $email = $googleUser->getEmail();

            // Verifica si el usuario está autorizado
            $allowedUser = AllowedUser::where('email', $email)->first();
            if (!$allowedUser || !$allowedUser->is_active) {
                \Log::warning('Acceso no autorizado', ['email' => $email]);
                return redirect()->route('login')->with('error', 'Este correo no tiene acceso autorizado o está desactivado.');
            }

            $client = new Client();
            $user = User::where('email', $email)->first();

            if ($user) {
                // Actualiza el nombre del usuario existente
                $user->update(['name' => $googleUser->getName()]);

                // Sube la imagen de perfil si no existe
                if (!$user->img) {
                    $imageContent = file_get_contents($googleUser->getAvatar());
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
                    \Log::info('Imgur response', ['data' => $data, 'privacy' => $data['data']['privacy'] ?? 'not_set']);

                    if (isset($data['data']['link'])) {
                        $user->update(['img' => $data['data']['link']]);
                        \Log::info('Image updated in DB', ['new_img' => $data['data']['link']]);
                    } else {
                        \Log::error('No link in Imgur response', ['response' => $data]);
                        throw new Exception('Error al subir la imagen a Imgur.');
                    }
                }
            } else {
                // Crea un nuevo usuario
                $user = User::create([
                    'email' => $email,
                    'name' => $googleUser->getName(),
                    'password' => null,
                ]);

                // Sube la imagen de perfil
                $imageContent = file_get_contents($googleUser->getAvatar());
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
                \Log::info('Imgur response', ['data' => $data, 'privacy' => $data['data']['privacy'] ?? 'not_set']);

                if (isset($data['data']['link'])) {
                    $user->update(['img' => $data['data']['link']]);
                    \Log::info('Image updated in DB', ['new_img' => $data['data']['link']]);
                } else {
                    \Log::error('No link in Imgur response', ['response' => $data]);
                    throw new Exception('Error al subir la imagen a Imgur.');
                }
            }

            // Inicia sesión con el usuario
            Auth::login($user, true);
            \Log::info('Login exitoso con Google', ['user_id' => $user->id]);
            return redirect()->route('dashboard');
        } catch (Exception $e) {
            \Log::error('Error en autenticación con Google', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('login')->with('error', 'Algo salió mal durante la autenticación con Google: ' . $e->getMessage());
        }
    }
}