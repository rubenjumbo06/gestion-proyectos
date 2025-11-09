<?php

namespace App\Observers;

use App\Models\Egresos;
use App\Models\Planilla;
use App\Models\GastosExtra;
use Illuminate\Support\Facades\DB;

class EgresosObserver
{
    public function saved(Egresos $egresos)
    {
        DB::transaction(function () use ($egresos) {
            $proyectoId = $egresos->id_proyecto;

            // 1. Planilla
            $planillaTotal = Planilla::where('id_proyecto', $proyectoId)
                ->whereNull('deleted_at')
                ->sum(DB::raw('pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador'));

            // 2. Gastos Extra
            $gastosExtra = GastosExtra::where('id_proyecto', $proyectoId)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(alimentacion_general + hospedaje + pasajes), 0) as total')
                ->first();
            $gastosExtraTotal = (float) ($gastosExtra->total ?? 0);

            // 3. Materiales (suma real)
            $materialesTotal = Materiales::where('id_proyecto', $proyectoId)
                ->whereNull('deleted_at')
                ->sum('monto_mat');

            // 4. SCR + Gastos Administrativos + Servicios (del último registro o 0)
            $scr = (float) ($egresos->scr ?? 0);
            $gastosAdmin = (float) ($egresos->gastos_administrativos ?? 0);
            $servicios = (float) ($egresos->servicios ?? 0);

            // 5. Actualizar egresos
            $egresos->update([
                'materiales' => $materialesTotal,
                'planilla' => $planillaTotal,
                'gastos_extra' => $gastosExtraTotal,
                'scr' => $scr,
                'gastos_administrativos' => $gastosAdmin,
                'servicios' => $servicios,
            ]);

            // Opcional: forzar recálculo del virtual
            $egresos->refresh();
        });
    }
}
