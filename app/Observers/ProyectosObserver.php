<?php

namespace App\Observers;

use App\Models\Proyectos;
use App\Models\Fechapr;
use App\Models\Montopr;
use App\Models\Planilla;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

            // Calcular total y guardar en montopr
            $montoMaterial = (float) request()->input('monto_material', 0);
            $montoOperativos = (float) request()->input('monto_operativos', 0);
            $montoServicios = (float) request()->input('monto_servicios', 0);
            $montoTotal = $montoMaterial + $montoOperativos + $montoServicios;

            Montopr::create([
                'proyecto_id' => $proyecto->id_proyecto,
                'monto_inicial' => $montoTotal,
                'monto_deseado' => $montoTotal,
            ]);

            // Insertar montos apartados por proyecto
            DB::table('montos_apartados')->insert([
                'id_proyecto' => $proyecto->id_proyecto,
                'monto_material' => $montoMaterial,
                'monto_operativos' => $montoOperativos,
                'monto_servicios' => $montoServicios,
                'fecha_creacion' => now(),
                'updated_at' => null,
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