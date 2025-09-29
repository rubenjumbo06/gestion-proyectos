<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index()
    {
        $asistencias = Asistencia::all();
        return view('asistencias.index', compact('asistencias'));
    }

    public function create()
    {
        return view('asistencias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_trabajadores' => 'required|exists:trabajadores,id_trabajadores',
            'hora' => 'required',
            'fecha' => 'required|date',
            'ubicacion' => 'required|string|max:255',
            'Dia_de_Semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
        ]);

        Asistencia::create($request->all());
        return redirect()->route('asistencias.index')->with('success', 'Asistencia creada exitosamente.');
    }

    public function show(Asistencia $asistencia)
    {
        return view('asistencias.show', compact('asistencia'));
    }

    public function edit(Asistencia $asistencia)
    {
        return view('asistencias.edit', compact('asistencia'));
    }

    public function update(Request $request, Asistencia $asistencia)
    {
        $request->validate([
            'id_trabajadores' => 'required|exists:trabajadores,id_trabajadores',
            'hora' => 'required',
            'fecha' => 'required|date',
            'ubicacion' => 'required|string|max:255',
            'Dia_de_Semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
        ]);

        $asistencia->update($request->all());
        return redirect()->route('asistencias.index')->with('success', 'Asistencia actualizada exitosamente.');
    }

    public function destroy(Asistencia $asistencia)
    {
        $asistencia->delete();
        return redirect()->route('asistencias.index')->with('success', 'Asistencia eliminada exitosamente.');
    }
}
