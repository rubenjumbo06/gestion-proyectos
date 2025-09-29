<?php

namespace App\Http\Controllers;

use App\Models\GastosExtra;
use Illuminate\Http\Request;

class GastosExtraController extends Controller
{
    public function index()
    {
        $gastosExtras = GastosExtra::all();
        return view('gastos_extras.index', compact('gastosExtras'));
    }

    public function create()
    {
        return view('gastos_extras.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'alimentacion_general' => 'required|numeric|min:0',
            'hospedaje' => 'required|numeric|min:0',
            'pasajes' => 'required|numeric|min:0',
        ]);

        GastosExtra::create($request->all());
        return redirect()->route('gastos_extras.index')->with('success', 'Gasto extra creado exitosamente.');
    }

    public function show(GastosExtra $gastosExtra)
    {
        return view('gastos_extras.show', compact('gastosExtra'));
    }

    public function edit(GastosExtra $gastosExtra)
    {
        return view('gastos_extras.edit', compact('gastosExtra'));
    }

    public function update(Request $request, GastosExtra $gastosExtra)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'alimentacion_general' => 'required|numeric|min:0',
            'hospedaje' => 'required|numeric|min:0',
            'pasajes' => 'required|numeric|min:0',
        ]);

        $gastosExtra->update($request->all());
        return redirect()->route('gastos_extras.index')->with('success', 'Gasto extra actualizado exitosamente.');
    }

    public function destroy(GastosExtra $gastosExtra)
    {
        $gastosExtra->delete();
        return redirect()->route('gastos_extras.index')->with('success', 'Gasto extra eliminado exitosamente.');
    }
}
