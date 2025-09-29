<?php

namespace App\Observers;

use App\Models\BalanceGeneral;
use App\Models\Montopr;
use App\Models\Egresos;

class BalanceGeneralObserver
{
    public function saved(BalanceGeneral $balanceGeneral)
    {
        $montopr = Montopr::where('id_montopr', $balanceGeneral->proyecto->montopr_id)->first();
        $egresos = Egresos::where('id_proyecto', $balanceGeneral->id_proyecto)->first();

        $balanceGeneral->total_servicios = $montopr ? $montopr->monto_inicial : 0;
        $balanceGeneral->egresos = $egresos ? $egresos->total_egresos : 0;
        $balanceGeneral->save();
    }
}
