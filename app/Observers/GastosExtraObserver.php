<?php

namespace App\Observers;

use App\Models\GastosExtra;
use App\Models\Egresos;
use Illuminate\Support\Facades\DB;

class GastosExtraObserver
{
    public function saved(GastosExtra $gastosExtra)
    {
        // Calcular el total de gastos extra como suma de columnas
        $totales = GastosExtra::where('id_proyecto', $gastosExtra->id_proyecto)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(alimentacion_general),0) as alim, COALESCE(SUM(hospedaje),0) as hosp, COALESCE(SUM(pasajes),0) as pas')
            ->first();

        $gastosExtraTotal = (float)($totales->alim ?? 0) + (float)($totales->hosp ?? 0) + (float)($totales->pas ?? 0);

        // Actualizar la última fila de egresos del proyecto SIN disparar eventos del modelo
        $ultimo = DB::table('egresos')
            ->where('id_proyecto', $gastosExtra->id_proyecto)
            ->orderByDesc('id_egreso')
            ->first();

        if ($ultimo) {
            DB::table('egresos')
                ->where('id_egreso', $ultimo->id_egreso)
                ->update([
                    'gastos_extra' => $gastosExtraTotal,
                    'updated_at' => now(),
                ]);
        }
    }
}
