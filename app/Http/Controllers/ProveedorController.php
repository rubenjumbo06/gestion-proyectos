<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\ProveedoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index()
    {
        try {
            $proveedores = Proveedor::all();
            $user = Auth::user()->load(['permisos', 'allowedUser']);
            return view('admin.proveedores.index', compact('proveedores', 'user'));
        } catch (\Exception $e) {
            Log::error('Error al listar proveedores: ' . $e->getMessage());
            return redirect()->route('proveedores.index')->with('error', '¡Ups! Algo falló al cargar los proveedores.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_identificacion' => 'required|in:DNI,RUC',
            'identificacion' => [
                'required',
                'string',
                'max:11',
                'unique:proveedores,identificacion',
                Rule::when($request->tipo_identificacion === 'RUC', ['digits:11']),
                Rule::when($request->tipo_identificacion === 'DNI', ['digits:8']),
            ],
            'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+$/|max:100|unique:proveedores,nombre_prov',
            'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'tipo_identificacion.required' => 'Debe seleccionar un tipo de documento.',
            'identificacion.required' => 'El número de documento es obligatorio.',
            'identificacion.digits' => 'El :attribute debe tener :digits dígitos.',
            'identificacion.unique' => 'Este número de documento ya está registrado.',
            'nombre_prov.regex' => 'El nombre solo puede contener letras, espacios y puntos.',
            'nombre_prov.unique' => 'El nombre del proveedor ya está registrado.',
            'descripcion_prov.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            Proveedor::create($request->only([
                'nombre_prov',
                'descripcion_prov',
                'tipo_identificacion',
                'identificacion'
            ]));

            return redirect()->route('proveedores.index')->with('success', '¡Proveedor creado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el proveedor.');
        }
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'tipo_identificacion' => 'required|in:DNI,RUC',
            'identificacion' => [
                'required',
                'string',
                'max:11',
                Rule::unique('proveedores', 'identificacion')->ignore($proveedor->id_proveedor, 'id_proveedor'),
                Rule::when($request->tipo_identificacion === 'RUC', ['digits:11']),
                Rule::when($request->tipo_identificacion === 'DNI', ['digits:8']),
            ],
            'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+$/|max:100|unique:proveedores,nombre_prov,' . $proveedor->id_proveedor . ',id_proveedor',
            'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/|max:255',
        ], [
            'tipo_identificacion.required' => 'Debe seleccionar un tipo de documento.',
            'identificacion.required' => 'El número de documento es obligatorio.',
            'identificacion.digits' => 'El :attribute debe tener :digits dígitos.',
            'identificacion.unique' => 'Este número de documento ya está registrado.',
            'nombre_prov.regex' => 'El nombre solo puede contener letras, espacios y puntos.',
            'nombre_prov.unique' => 'El nombre del proveedor ya está registrado.',
            'descripcion_prov.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
        ]);

        try {
            $proveedor->update($request->only([
                'nombre_prov',
                'descripcion_prov',
                'tipo_identificacion',
                'identificacion'
            ]));

            return redirect()->route('proveedores.index')->with('success', '¡Proveedor actualizado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo actualizar el proveedor.');
        }
    }

    // Resto de métodos sin cambios...
    public function show(Proveedor $proveedor)
    {
        return view('admin.proveedores.show', compact('proveedor'));
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