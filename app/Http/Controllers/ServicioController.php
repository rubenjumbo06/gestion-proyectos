<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::all();
        return view('servicios.index', compact('servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'descripcion_serv' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
        ]);

        Servicio::create($request->all());

        return redirect()->back()->with('success', 'Servicio registrado exitosamente.');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'descripcion_serv' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
        ]);

        $servicio->update($request->all());

        return redirect()->back()->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->back()->with('success', 'Servicio eliminado exitosamente.');
    }
}
