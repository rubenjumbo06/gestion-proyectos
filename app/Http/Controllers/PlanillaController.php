<?php

namespace App\Http\Controllers;

use App\Models\Planilla;
use App\Models\Trabajadores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PlanillaController extends Controller
{
    public function index()
    {
        $planillas = Planilla::with(['proyecto', 'trabajador'])->get();
        return view('planillas.index', compact('planillas'));
    }

    public function create()
    {
        return view('planillas.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'trabajador_id' => 'required|integer|exists:trabajadores,id_trabajadores',
                'id_proyecto' => 'required|integer|exists:proyectos,id_proyecto',
            ]);

            // Verificar si el trabajador ya está en la planilla del proyecto
            $exists = Planilla::where('id_proyecto', $request->id_proyecto)
                              ->where('id_trabajadores', $request->trabajador_id)
                              ->whereNull('deleted_at')
                              ->exists();
            if ($exists) {
                return response()->json(['error' => 'El trabajador ya está en la planilla de este proyecto.'], 400);
            }

            // Crear registro en la planilla con valores por defecto
            $planilla = Planilla::create([
                'id_proyecto' => $request->id_proyecto,
                'id_trabajadores' => $request->trabajador_id,
                'dias_trabajados' => 0,
                'pago' => 0,
                'alimentacion_trabajador' => 0,
                'hospedaje_trabajador' => 0,
                'pasajes_trabajador' => 0,
                'estado' => 'NO LIQUIDADO',
            ]);

            $trabajador = Trabajadores::findOrFail($request->trabajador_id);

            return response()->json([
                'success' => true,
                'id' => $planilla->id_plan_trabajador,
                'dni_trab' => $trabajador->dni_trab,
                'dias_trabajados' => $planilla->dias_trabajados,
                'pago' => $planilla->pago,
                'alimentacion_trabajador' => $planilla->alimentacion_trabajador,
                'hospedaje_trabajador' => $planilla->hospedaje_trabajador,
                'pasajes_trabajador' => $planilla->pasajes_trabajador,
                'estado' => $planilla->estado,
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Error de validación al crear planilla: ' . json_encode($e->errors()), [
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Datos inválidos: ' . implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear registro en la planilla: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'No se pudo agregar el trabajador: ' . $e->getMessage()], 500);
        }
    }

    public function show(Planilla $planilla)
    {
        return view('planillas.show', compact('planilla'));
    }

    public function edit(Planilla $planilla)
    {
        return view('planillas.edit', compact('planilla'));
    }

    public function update(Request $request, Planilla $planilla)
    {
        $request->validate([
            'id_trabajadores' => 'required|exists:trabajadores,id_trabajadores',
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'dias_trabajados' => 'required|integer|min:1',
            'pago' => 'required|numeric|min:0',
            'alimentacion_trabajador' => 'required|numeric|min:0',
            'hospedaje_trabajador' => 'required|numeric|min:0',
            'pasajes_trabajador' => 'required|numeric|min:0',
            'estado' => 'required|in:LIQUIDADO,NO LIQUIDADO',
        ]);

        $planilla->update($request->all());
        return redirect()->route('planillas.index')->with('success', 'Planilla actualizada exitosamente.');
    }

    public function destroy(Planilla $planilla)
    {
        $planilla->delete();
        return redirect()->route('planillas.index')->with('success', 'Planilla eliminada exitosamente.');
    }
}