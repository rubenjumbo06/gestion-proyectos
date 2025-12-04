<?php

namespace App\Http\Controllers;

use App\Models\Proveedor; // ← TU MODELO SE LLAMA PROVEEDOR
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
            'identificacion' => [
                'required',
                'numeric',
                'digits_between:8,11',
                Rule::unique('proveedores', 'identificacion')->whereNull('deleted_at')
            ],
            'tipo_identificacion' => 'required|in:RUC,DNI',
            'nombre_prov' => 'required|string|max:255',
            'descripcion_prov' => 'nullable|string',
        ], [
            'identificacion.unique' => 'Este número ya está registrado (aunque el financiador esté eliminado).',
        ]);

        Proveedor::create($request->all()); // ← PROVEEDOR, NO FINANCIADOR

        return redirect()->route('financiadores.index')
            ->with('success', 'Financiador agregado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor) // ← RECIBE PROVEEDOR
    {
        $request->validate([
            'identificacion' => [
                'required',
                'numeric',
                'digits_between:8,11',
                Rule::unique('proveedores', 'identificacion')
                    ->ignore($proveedor->id_proveedor, 'id_proveedor') // ← COLUMNA CORRECTA
                    ->whereNull('deleted_at')
            ],
            'tipo_identificacion' => 'required|in:RUC,DNI',
            'nombre_prov' => 'required|string|max:255',
            'descripcion_prov' => 'nullable|string',
        ]);

        $proveedor->update($request->all()); // ← USA $proveedor

        return redirect()->route('financiadores.index')
            ->with('success', 'Financiador actualizado correctamente.');
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

    public function buscarRuc($ruc)
    {
        if (strlen($ruc) !== 11 || !is_numeric($ruc)) {
            return response()->json(['error' => 'RUC inválido'], 400);
        }

        $token = env('DECOLECTA_KEY');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("https://api.decolecta.com/v1/sunat/ruc", [
            'numero' => $ruc
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $data = $response->json();

        return response()->json([
            'razon_social' => $data['razon_social'] ?? '',
            'direccion'    => $data['direccion'] ?? '',
            'estado'       => $data['estado'] ?? ''
        ]);
    }
}