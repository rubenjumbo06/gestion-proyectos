<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\ProveedoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    public function index()
    {
        try {
            $proveedores = Proveedor::all();
            $user = Auth::user()->load(['permisos', 'allowedUser']); // Cargar permisos y allowedUser
            return view('admin.proveedores.index', compact('proveedores', 'user'));
        } catch (\Exception $e) {
            Log::error('Error al listar proveedores: ' . $e->getMessage());
            return redirect()->route('proveedores.index')->with('error', '¡Ups! Algo falló al cargar los proveedores.');
        }
    }

    public function create()
    {
        return view('admin.proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+$/|max:100|unique:proveedores,nombre_prov',
            'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'nombre_prov.regex' => 'El nombre solo puede contener letras, espacios y puntos.',
            'nombre_prov.unique' => 'El nombre del proveedor ya está registrado.',
            'descripcion_prov.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            Proveedor::create($request->only(['nombre_prov', 'descripcion_prov']));
            return redirect()->route('proveedores.index')->with('success', '¡Proveedor creado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el proveedor.');
        }
    }

    public function show(Proveedor $proveedor)
    {
        return view('admin.proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+$/|max:100|unique:proveedores,nombre_prov,' . $proveedor->id_proveedor . ',id_proveedor',
            'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'nombre_prov.regex' => 'El nombre solo puede contener letras, espacios y puntos.',
            'nombre_prov.unique' => 'El nombre del proveedor ya está registrado.',
            'descripcion_prov.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            $proveedor->update($request->only(['nombre_prov', 'descripcion_prov']));
            return redirect()->route('proveedores.index')->with('success', '¡Proveedor actualizado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo actualizar el proveedor.');
        }
    }

    public function destroy(Proveedor $proveedor)
    {
        try {
            $proveedor->delete();
            return redirect()->route('proveedores.index')->with('success', '¡Proveedor eliminado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al eliminar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')->with('error', '¡Ups! No se pudo eliminar el proveedor.');
        }
    }

    public function exportExcel()
    {
        return Excel::download(new ProveedoresExport, 'proveedores.xlsx');
    }

    public function exportPdf()
    {
        $proveedores = Proveedor::all();
        $pdf = Pdf::loadView('admin.proveedores.proveedores_pdf', compact('proveedores'))
                ->setPaper('a4', 'landscape');
        return $pdf->download('proveedores.pdf');
    }
}