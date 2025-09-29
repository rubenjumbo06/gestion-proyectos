<?php

namespace App\Http\Controllers;

use App\Models\Fechapr;
use Illuminate\Http\Request;

class FechaprController extends Controller
{
    public function index()
    {
        $fechaprs = Fechapr::all();
        return view('fechaprs.index', compact('fechaprs'));
    }

    public function create()
    {
        return view('fechaprs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id_proyecto',
            'fecha_inicio' => 'required|date',
            'fecha_fin_aprox' => 'nullable|date',
            'fecha_fin_true' => 'nullable|date',
        ]);

        Fechapr::create($request->all());
        return redirect()->route('fechaprs.index')->with('success', 'Fecha creada exitosamente.');
    }

    public function show(Fechapr $fechapr)
    {
        return view('fechaprs.show', compact('fechapr'));
    }

    public function edit(Fechapr $fechapr)
    {
        return view('fechaprs.edit', compact('fechapr'));
    }

    public function update(Request $request, Fechapr $fechapr)
    {
        $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id_proyecto',
            'fecha_inicio' => 'required|date',
            'fecha_fin_aprox' => 'nullable|date',
            'fecha_fin_true' => 'nullable|date',
        ]);

        $fechapr->update($request->all());
        return redirect()->route('fechaprs.index')->with('success', 'Fecha actualizada exitosamente.');
    }

    public function destroy(Fechapr $fechapr)
    {
        $fechapr->delete();
        return redirect()->route('fechaprs.index')->with('success', 'Fecha eliminada exitosamente.');
    }
}
