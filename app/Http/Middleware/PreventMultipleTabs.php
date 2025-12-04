<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventMultipleTabs
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $userId = auth()->id();
        $sessionKey   = "tab_token_{$userId}";
        $sessionToken = session($sessionKey);
        $queryToken   = $request->query('t');

        // Caso 1: primera carga o navegación con ?t= correcto
        if ($queryToken && $queryToken === $sessionToken) {
            return $next($request);
        }

        // Caso 2: navegación interna sin ?t, pero hay token en sesión (clics internos, AJAX, etc.)
        if ($sessionToken && !$queryToken) {
            return $next($request);
        }

        // Caso 3: URL inválida, sin sesión o token distinto → forzar login
        if (!$sessionToken || ($queryToken && $queryToken !== $sessionToken)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login.form')
                ->with('error', 'Sesión expirada o acceso no autorizado. Inicia sesión nuevamente.');
        }

        return $next($request);
    }
}