<?php

namespace App\Observers;

use App\Models\ControlGastos;
use App\Models\Egresos;

class ControlGastosObserver
{
    public function saved(ControlGastos $controlGastos)
    {
        $egresos = Egresos::where('id_proyecto', $controlGastos->id_proyecto)->first();
        $totalEgresos = $egresos ? $egresos->total_egresos : 0;

        $controlGastos->total_quedante_utilidad = $controlGastos->monto_inicial - $totalEgresos;
        $controlGastos->alerta = $controlGastos->total_quedante_utilidad > $controlGastos->monto_minimo;
        $controlGastos->save();
    }
}
