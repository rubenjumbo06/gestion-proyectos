<?php

namespace App\Http\Controllers;

use App\Models\Egresos;
use Illuminate\Http\Request;

class EgresosController extends Controller
{
    public function index()
    {
        $egresos = Egresos::all();
        return view('egresos.index', compact('egresos'));
    }

    public function create()
    {
        return view('egresos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'materiales' => 'required|numeric|min:0',
            'planilla' => 'required|numeric|min:0',
            'scr' => 'required|numeric|min:0',
            'gastos_administrativos' => 'required|numeric|min:0',
            'gastos_extra' => 'required|numeric|min:0',
        ]);

        Egresos::create($request->all());
        return redirect()->route('egresos.index')->with('success', 'Egreso creado exitosamente.');
    }

    public function show(Egresos $egresos)
    {
        return view('egresos.show', compact('egresos'));
    }

    public function edit(Egresos $egresos)
    {
        return view('egresos.edit', compact('egresos'));
    }

    public function update(Request $request, Egresos $egresos)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'materiales' => 'required|numeric|min:0',
            'planilla' => 'required|numeric|min:0',
            'scr' => 'required|numeric|min:0',
            'gastos_administrativos' => 'required|numeric|min:0',
            'gastos_extra' => 'required|numeric|min:0',
        ]);

        $egresos->update($request->all());
        return redirect()->route('egresos.index')->with('success', 'Egreso actualizado exitosamente.');
    }

    public function destroy(Egresos $egresos)
    {
        $egresos->delete();
        return redirect()->route('egresos.index')->with('success', 'Egreso eliminado exitosamente.');
    }
}
