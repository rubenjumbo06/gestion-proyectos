<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class NoCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo aplicamos no-cache si NO es una descarga de archivo (Excel, PDF, ZIP, etc.)
        if (! $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            // 'Unload' no existe como header, era un invento que no hace nada
        }

        // Tu lógica anti-bfcache (esto sí lo puedes dejar)
        if (Auth::check() && (!session()->isStarted() || session()->getId() === '')) {
            Auth::logout();
            return redirect()->route('login.form');
        }

        return $response;
    }
}