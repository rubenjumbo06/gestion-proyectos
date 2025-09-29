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
        // Calcular totales reales y actualizar por DB para evitar recursión de eventos
        $planillaSumRow = Planilla::where('id_proyecto', $egresos->id_proyecto)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador),0) AS total')
            ->first();
        $planillaTotal = (float) ($planillaSumRow->total ?? 0);

        $gastos = GastosExtra::where('id_proyecto', $egresos->id_proyecto)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(alimentacion_general),0) as alim, COALESCE(SUM(hospedaje),0) as hosp, COALESCE(SUM(pasajes),0) as pas')
            ->first();

        $gastosExtraTotal = (float) ($gastos->alim ?? 0) + (float) ($gastos->hosp ?? 0) + (float) ($gastos->pas ?? 0);

        DB::table('egresos')
            ->where('id_egreso', $egresos->id_egreso)
            ->update([
                'planilla' => $planillaTotal,
                'gastos_extra' => $gastosExtraTotal,
                'updated_at' => now(),
            ]);
    }
}
