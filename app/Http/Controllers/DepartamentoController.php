<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\DepartamentosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class DepartamentoController extends Controller
{
    public function index()
    {
        try {
            $departamentos = Departamento::all();
            $user = Auth::user()->load(['permisos', 'allowedUser']); // Cargar permisos y allowedUser
            return view('admin.departamentos.index', compact('departamentos', 'user'));
        } catch (\Exception $e) {
            Log::error('Error al listar los departamentos: ' . $e->getMessage());
            return redirect()->route('departamentos.index')->with('error', '¡Ups! Algo falló al cargar los departamentos.');
        }
    }

    public function create()
    {
        return view('admin.departamentos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_dep' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100|unique:departamento,nombre_dep',
            'descripcion_dep' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'nombre_dep.regex' => 'El nombre solo puede contener letras y espacios.',
            'nombre_dep.unique' => 'El nombre del departamento ya está registrado.',
            'descripcion_dep.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            $departamento = Departamento::create($request->all());
            Activity::create([
                'user_id' => auth()->id(),
                'description' => 'Creó el departamento ' . $departamento->nombre_dep . '.',
            ]);
            return redirect()->route('departamentos.index')->with('success', 'Departamento creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear departamento: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el departamento.');
        }
    }
    public function update(Request $request, Departamento $departamento)
    {
        $request->validate([
            'nombre_dep' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100|unique:departamento,nombre_dep,' . $departamento->id_departamento . ',id_departamento',
            'descripcion_dep' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'nombre_dep.regex' => 'El nombre solo puede contener letras y espacios.',
            'nombre_dep.unique' => 'El nombre del departamento ya está registrado.',
            'descripcion_dep.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            $departamento->update($request->all());
            Activity::create([
                'user_id' => auth()->id(),
                'description' => 'Actualizó el departamento ' . $departamento->nombre_dep . '.',
            ]);
            return redirect()->route('departamentos.index')->with('success', 'Departamento actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar departamento: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo actualizar el departamento.');
        }
    }

    public function destroy(Departamento $departamento)
    {
        try {
            $nombre = $departamento->nombre_dep;
            $departamento->delete();
            Activity::create([
                'user_id' => auth()->id(),
                'description' => 'Eliminó el departamento ' . $nombre . '.',
            ]);
            return redirect()->route('departamentos.index')->with('success', 'Departamento eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar departamento: ' . $e->getMessage());
            return redirect()->route('departamentos.index')->with('error', '¡Ups! Algo falló al intentar eliminar el departamento.');
        }
    }

    public function exportExcel()
    {
        return Excel::download(new DepartamentosExport, 'departamentos.xlsx');
    }

    public function exportPdf()
    {
        $departamentos = Departamento::all();
        $pdf = Pdf::loadView('admin.departamentos.departamentos_pdf', compact('departamentos'))
                ->setPaper('a4', 'landscape');
        return $pdf->download('departamentos.pdf');
    }
}