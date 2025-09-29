<?php

namespace App\Http\Controllers;

use App\Models\Materiales;
use Illuminate\Http\Request;

class MaterialesController extends Controller
{
    public function index()
    {
        $materiales = Materiales::all();
        return view('materiales.index', compact('materiales'));
    }

    public function create()
    {
        return view('materiales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'descripcion_mat' => 'required|string|max:255',
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'monto_mat' => 'required|numeric|min:0',
        ]);

        Materiales::create($request->all());
        return redirect()->route('materiales.index')->with('success', 'Material creado exitosamente.');
    }

    public function show(Materiales $materiales)
    {
        return view('materiales.show', compact('materiales'));
    }

    public function edit(Materiales $materiales)
    {
        return view('materiales.edit', compact('materiales'));
    }

    public function update(Request $request, Materiales $materiales)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'descripcion_mat' => 'required|string|max:255',
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'monto_mat' => 'required|numeric|min:0',
        ]);

        $materiales->update($request->all());
        return redirect()->route('materiales.index')->with('success', 'Material actualizado exitosamente.');
    }

    public function destroy(Materiales $materiales)
    {
        $materiales->delete();
        return redirect()->route('materiales.index')->with('success', 'Material eliminado exitosamente.');
    }
}
