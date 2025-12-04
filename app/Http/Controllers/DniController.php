<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DniController extends Controller
{
    public function consultar($dni)
    {
        // Validar DNI
        if (!preg_match('/^\d{8}$/', $dni)) {
            return response()->json(['error' => 'DNI inválido'], 400);
        }

        // Cache 24 horas
        $cacheKey = "dni_data_{$dni}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($dni) {

            $data = [
                'dni' => $dni,
                'nombres' => null,
                'apellido_paterno' => null,
                'apellido_materno' => null,
            ];

            try {

                // 1️⃣ CONSULTAR DECOLECTA (API oficial)
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.decolecta.key'),
                    'Accept' => 'application/json',
                ])->get("https://api.decolecta.com/v1/reniec/dni", [
                    'numero' => $dni
                ]);

                Log::info("Respuesta Decolecta:", $response->json());

                if ($response->successful()) {
                    $data['nombres']          = $response['first_name'] ?? null;
                    $data['apellido_paterno'] = $response['first_last_name'] ?? null;
                    $data['apellido_materno'] = $response['second_last_name'] ?? null;
                }

                return response()->json($data);

            } catch (\Exception $e) {
                Log::error("Error Decolecta DNI: {$dni} | " . $e->getMessage());
                return response()->json([
                    'dni' => $dni,
                    'error' => 'No se pudo consultar el DNI'
                ], 500);
            }
        });
    }
}
