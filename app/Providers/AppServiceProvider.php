<?php

namespace App\Providers;

use App\Models\Planilla;
use App\Models\GastosExtra;
use App\Models\Egresos;
use App\Models\ControlGastos;
use App\Models\BalanceGeneral;
use App\Models\Proyectos; // Add this import
use App\Observers\PlanillaObserver;
use App\Observers\GastosExtraObserver;
use App\Observers\EgresosObserver;
use App\Observers\ControlGastosObserver;
use App\Observers\BalanceGeneralObserver;
use Illuminate\Support\Facades\Route; // ← ESTO FALTABA
use App\Observers\ProyectosObserver; // Add this import

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Planilla::observe(PlanillaObserver::class);
        GastosExtra::observe(GastosExtraObserver::class);
        Egresos::observe(EgresosObserver::class);
        ControlGastos::observe(ControlGastosObserver::class);
        BalanceGeneral::observe(BalanceGeneralObserver::class);
        Proyectos::observe(ProyectosObserver::class);
        // En AppServiceProvider@boot
URL::macro('withTabToken', function ($url = null, $parameters = []) {
    if (!auth()->check()) {
        return $url ? url($url, $parameters) : url()->current();
    }

    $token = session('tab_token_' . auth()->id());
    if (!$token) {
        return $url ? url($url, $parameters) : url()->current();
    }

    // Si pasas una ruta con nombre (ej: 'dashboard'), la convertimos a URL
    if (is_string($url) && Route::has($url)) {
        $baseUrl = route($url, [], false); // false = relativo
    } else {
        $baseUrl = $url ? url($url) : url()->current();
    }

    // Parsear URL actual o la pasada
    $parsed = parse_url($baseUrl);
    $path = $parsed['path'] ?? '/';
    $query = [];
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }

    // Añadir parámetros extras si los hay
    if (!empty($parameters) && is_array($parameters)) {
        $query = array_merge($query, $parameters);
    }

    // Añadir el token
    $query['t'] = $token;

    // Reconstruir URL
    $queryString = http_build_query($query);
    return $path . ($queryString ? '?' . $queryString : '');
});

    // Compartir el token en todas las vistas
    view()->share('tabToken', auth()->check() ? session('tab_token_' . auth()->id()) : null);
    }
}