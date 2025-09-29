<?php

namespace App\Http\Controllers;

use App\Models\Montopr;
use Illuminate\Http\Request;

class MontoprController extends Controller
{
    public function index()
    {
        $montoprs = Montopr::all();
        return view('montoprs.index', compact('montoprs'));
    }

    public function create()
    {
        return view('montoprs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id_proyecto',
            'monto_inicial' => 'required|numeric|min:0',
            'monto_deseado' => 'required|numeric|min:0',
        ]);

        Montopr::create($request->all());
        return redirect()->route('montoprs.index')->with('success', 'Monto creado exitosamente.');
    }

    public function show(Montopr $montopr)
    {
        return view('montoprs.show', compact('montopr'));
    }

    public function edit(Montopr $montopr)
    {
        return view('montoprs.edit', compact('montopr'));
    }

    public function update(Request $request, Montopr $montopr)
    {
        $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id_proyecto',
            'monto_inicial' => 'required|numeric|min:0',
            'monto_deseado' => 'required|numeric|min:0',
        ]);

        $montopr->update($request->all());
        return redirect()->route('montoprs.index')->with('success', 'Monto actualizado exitosamente.');
    }

    public function destroy(Montopr $montopr)
    {
        $montopr->delete();
        return redirect()->route('montoprs.index')->with('success', 'Monto eliminado exitosamente.');
    }
}
