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
    $proveedores = Proveedor::all();
    $user = Auth::user()->load(['permisos', 'allowedUser']);
    return view('admin.financiadores.index', compact('proveedores', 'user'));
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
        'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]+$/|max:100|unique:proveedores,nombre_prov',
        'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]*$/|max:255',
    ], [
        'nombre_prov.regex' => 'El nombre solo puede contener letras, espacios y puntos.',
        'descripcion_prov.regex' => 'La descripción solo puede contener letras, espacios y puntos.',
    ]);

    Proveedor::create($request->only([
        'nombre_prov', 'descripcion_prov', 'tipo_identificacion', 'identificacion'
    ]));

    return redirect()
        ->route('financiadores.index')
        ->with('success', 'Financiador creado con éxito!');
}

    public function update(Request $request, Proveedor $proveedor)
{
    $request->validate([
        'tipo_identificacion' => 'required|in:DNI,RUC',
        'identificacion' => [
            'required', 'string', 'max:11',
            Rule::unique('proveedores', 'identificacion')->ignore($proveedor->id_proveedor),
            Rule::when($request->tipo_identificacion === 'RUC', ['digits:11']),
            Rule::when($request->tipo_identificacion === 'DNI', ['digits:8']),
        ],
        'nombre_prov' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]+$/|max:100|unique:proveedores,nombre_prov,'.$proveedor->id_proveedor.',id_proveedor',
        'descripcion_prov' => 'nullable|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]*$/|max:255',
    ]);

    $proveedor->update($request->only([
        'nombre_prov', 'descripcion_prov', 'tipo_identificacion', 'identificacion'
    ]));

    return redirect()
        ->route('financiadores.index')
        ->with('success', 'Financiador actualizado con éxito!');
}
    public function show(Proveedor $proveedor)
    {
        return view('admin.proveedores.show', compact('proveedor'));
    }

    public function destroy(Proveedor $proveedor)
{
    if ($proveedor->materiales()->exists()) {
        return redirect()->route('financiadores.index')
            ->with('error', 'No se puede eliminar porque tiene materiales asociados.');
    }

    $proveedor->delete();

    return redirect()->route('financiadores.index')
        ->with('success', 'Financiador eliminado con éxito!');
}

    public function exportExcel()
    {
        return Excel::download(new ProveedoresExport, 'financiadores.xlsx');
    }

    public function exportPdf()
    {
        $proveedores = Proveedor::all();
        $pdf = Pdf::loadView('admin.financiadores.financiadores_pdf', compact('proveedores'))
                ->setPaper('a4', 'landscape');
        return $pdf->download('financiadores.pdf');
    }
}