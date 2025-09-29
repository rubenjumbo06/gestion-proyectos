<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;
use App\Jobs\UploadImageJob;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('perfiles', compact('user'));
    }

   public function update(Request $request)
{
    $user = Auth::user();
    $startTime = microtime(true);

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date',
        'img' => 'nullable|image|max:2048',
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8|confirmed',
    ]);

    $updateData = [
        'name' => $data['name'],
        'telefono' => $data['telefono'],
        'fecha_nacimiento' => $data['fecha_nacimiento'],
    ];
    $user->update($updateData);
    \Log::info('Basic fields updated', ['time' => microtime(true) - $startTime]);

    if ($request->filled('new_password')) {
        if (!$user->password) {
            if ($request->input('new_password') !== $request->input('new_password_confirmation')) {
                return response()->json(['success' => false, 'message' => 'La nueva contraseña y la confirmación no coinciden.'], 422);
            }
            $user->update(['password' => Hash::make($data['new_password'])]);
        } else {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'La contraseña actual es incorrecta.'], 422);
            }
            $user->update(['password' => Hash::make($data['new_password'])]);
        }
    }
    \Log::info('Password updated', ['time' => microtime(true) - $startTime]);

    if ($request->hasFile('img')) {
        $client = new Client();
        try {
            $response = $client->post('https://api.imgur.com/3/image', [
                'headers' => ['Authorization' => 'Bearer ' . env('IMGUR_ACCESS_TOKEN')],
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => file_get_contents($request->file('img')->getRealPath()),
                        'filename' => 'profile_' . time() . '_' . $user->id . '.' . $request->file('img')->extension()
                    ],
                    ['name' => 'privacy', 'contents' => 'hidden'],
                    ['name' => 'title', 'contents' => 'Profile Image'],
                    ['name' => 'description', 'contents' => 'User profile image']
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['data']['link'])) {
                $user->update(['img' => $data['data']['link']]);
                \Log::info('Image updated in DB', ['new_img' => $data['data']['link'], 'time' => microtime(true) - $startTime]);
            }
        } catch (\Exception $e) {
            \Log::error('Error uploading to Imgur', ['message' => $e->getMessage(), 'time' => microtime(true) - $startTime]);
            return response()->json(['success' => false, 'message' => 'Error al subir la imagen: ' . $e->getMessage()], 422);
        }
    }

    // Registrar actividad general si hay cambios
    $hasChanges = $request->input('name') !== $user->getOriginal('name') ||
                  $request->input('telefono') !== $user->getOriginal('telefono') ||
                  $request->input('fecha_nacimiento') !== $user->getOriginal('fecha_nacimiento') ||
                  $request->filled('new_password') ||
                  $request->hasFile('img');

    if ($hasChanges) {
        Activity::create([
            'user_id' => $user->id,
            'description' => 'Se actualizó el Perfil.',
        ]);
        \Log::info('Activity created', ['user_id' => $user->id, 'description' => 'Se actualizó el Perfil.', 'time' => microtime(true) - $startTime]);
    }

    \Log::info('Total update time', ['time' => microtime(true) - $startTime]);
    return response()->json(['success' => true, 'message' => 'Perfil actualizado con éxito.']);
}
}