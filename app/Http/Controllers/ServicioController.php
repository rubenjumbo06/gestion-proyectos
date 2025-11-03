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

        $servicio = Servicio::create($request->all());

        return response()->json([
            'success' => true,
            'servicio' => $servicio,
        ], 201);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'descripcion_serv' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
        ]);

        $servicio->update($request->all());

        return response()->json([
            'success' => true,
            'servicio' => $servicio,
        ]);
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return response()->json(['success' => true]);
    }
}
