<?php

namespace App\Observers;

use App\Models\Proyectos;
use App\Models\Fechapr;
use App\Models\Montopr;
use App\Models\Planilla;
use Illuminate\Support\Facades\Log;

class ProyectosObserver
{
    /**
     * Handle the Proyectos "created" event.
     */
    public function created(Proyectos $proyecto)
    {
        try {
            // Insert into fechapr table
            Fechapr::create([
                'proyecto_id' => $proyecto->id_proyecto,
                'fecha_inicio' => request()->input('fecha_inicio'),
                'fecha_fin_aprox' => request()->input('fecha_fin_aprox'),
                'fecha_fin_true' => null,
            ]);

            // Insert into montopr table
            $monto = request()->input('monto');
            Montopr::create([
                'proyecto_id' => $proyecto->id_proyecto,
                'monto_inicial' => $monto ?? 0,
                'monto_deseado' => $monto ?? 0,
            ]);

            // Insert into planilla table for each selected trabajador
            $trabajadores = request()->input('trabajadores', []);
            $sueldo = request()->input('sueldo', 0);
            foreach ($trabajadores as $trabajadorId) {
                Planilla::create([
                    'id_trabajadores' => $trabajadorId,
                    'id_proyecto' => $proyecto->id_proyecto,
                    'dias_trabajados' => 0, // Default, adjust if needed
                    'pago' => $sueldo, // Use project's sueldo
                    'alimentacion_trabajador' => 0.00,
                    'hospedaje_trabajador' => 0.00,
                    'pasajes_trabajador' => 0.00,
                    'estado' => 'NO LIQUIDADO',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in ProyectosObserver: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'trabajadores' => request()->input('trabajadores', []),
                'exception' => $e
            ]);
        }
    }
}