<?php

namespace App\Observers;

use App\Models\Planilla;

class PlanillaObserver
{
    public function creating(Planilla $planilla)
    {
        // Solo calcular pago en creación si se proporciona días trabajados > 0
        // y no se está seteando 'pago' explícitamente
        $dias = (int) ($planilla->dias_trabajados ?? 0);
        $pagoVieneSeteado = $planilla->getAttribute('pago') !== null && $planilla->isDirty('pago');
        if ($dias > 0 && !$pagoVieneSeteado) {
            $this->calcularPago($planilla);
        }
    }

    public function updating(Planilla $planilla)
    {
        // NO recalcular si solo se están actualizando gastos del trabajador
        // Recalcular 'pago' solo cuando cambien los días trabajados y
        // no se esté modificando 'pago' explícitamente en esta actualización
        $cambioDias = $planilla->isDirty('dias_trabajados');
        $cambioPago = $planilla->isDirty('pago');
        if ($cambioDias && !$cambioPago) {
            $this->calcularPago($planilla);
        }
    }

    public function deleted(Planilla $planilla)
    {
        // Ya no sincronizamos GastosExtra con Planilla al eliminar
    }

    protected function calcularPago(Planilla $planilla)
    {
        // Cargar la relación proyecto con montopr para evitar consultas adicionales
        $proyecto = $planilla->proyecto()->with('montopr')->first();

        if (!$proyecto || !$proyecto->montopr) {
            $planilla->pago = 60.00; // Valor por defecto si no hay proyecto o montopr
            return;
        }

        $montoInicial = $proyecto->montopr->monto_inicial ?? 0;
        $porcentaje = config('planilla.porcentaje', 0.10); // Usar config para flexibilidad
        $pagoBase = $montoInicial * $porcentaje;
        $diasTrabajados = $planilla->dias_trabajados ?? 1; // Validar si es correcto usar 1

        $planilla->pago = max($pagoBase, 60.00) * $diasTrabajados;
    }

    protected function actualizarGastosExtra(Planilla $planilla)
    {
        // No-op: a partir de ahora Planilla NO modifica GastosExtra.
        // GastosExtra representa gastos generales del proyecto, independientes de la planilla.
        return;
    }
}