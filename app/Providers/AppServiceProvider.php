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
use App\Observers\ProyectosObserver; // Add this import

use Illuminate\Support\ServiceProvider;

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
    }
}