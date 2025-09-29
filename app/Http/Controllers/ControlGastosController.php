<?php

namespace App\Http\Controllers;

use App\Models\ControlGastos;
use Illuminate\Http\Request;

class ControlGastosController extends Controller
{
    public function index()
    {
        $controlGastos = ControlGastos::all();
        return view('control_gastos.index', compact('controlGastos'));
    }

    public function create()
    {
        return view('control_gastos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        ControlGastos::create($request->all());
        return redirect()->route('control_gastos.index')->with('success', 'Control de gasto creado exitosamente.');
    }

    public function show(ControlGastos $controlGastos)
    {
        return view('control_gastos.show', compact('controlGastos'));
    }

    public function edit(ControlGastos $controlGastos)
    {
        return view('control_gastos.edit', compact('controlGastos'));
    }

    public function update(Request $request, ControlGastos $controlGastos)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $controlGastos->update($request->all());
        return redirect()->route('control_gastos.index')->with('success', 'Control de gasto actualizado exitosamente.');
    }

    public function destroy(ControlGastos $controlGastos)
    {
        $controlGastos->delete();
        return redirect()->route('control_gastos.index')->with('success', 'Control de gasto eliminado exitosamente.');
    }
}
