<?php

namespace App\Http\Controllers;

use App\Models\Trabajadores;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\TrabajadoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;


class TrabajadoresController extends Controller
{
    public function index()
{
    try {
        $trabajadores = Trabajadores::with('departamento')->get();
        $departamentos = Departamento::all();
        $user = Auth::user()->load(['permisos', 'allowedUser']); // Cargar ambas relaciones
        Log::info('Trabajadores cargados para index:', [
            'count' => $trabajadores->count(),
            'trabajadores' => $trabajadores->map(function ($t) {
                return [
                    'id_trabajadores' => $t->id_trabajadores,
                    'id_departamento' => $t->id_departamento,
                    'departamento' => $t->departamento ? $t->departamento->toArray() : null,
                ];
            })->toArray(),
        ]);
        return view('admin.trabajadores.index', compact('trabajadores', 'departamentos'));
    } catch (\Exception $e) {
        Log::error('Error al listar los trabajadores: ' . $e->getMessage());
        return redirect()->route('trabajadores.index')->with('error', '¡Ups! Algo falló al cargar los trabajadores.');
    }
}
    public function create()
    {
        $departamentos = Departamento::all();
        return view('admin.trabajadores.create', compact('departamentos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_trab' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100',
            'apellido_trab' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100',
            'dni_trab' => 'required|digits:8|unique:trabajadores,dni_trab',
            'correo_trab' => 'required|email|max:255|unique:trabajadores,correo_trab',
            'num_telef' => 'required|digits:9',
            'sexo_trab' => 'required|in:Masculino,Femenino',
            'fecha_nac' => 'required|date',
            'id_departamento' => 'required|exists:departamento,id_departamento',
        ], [
            'nombre_trab.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido_trab.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'dni_trab.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'num_telef.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
            'correo_trab.email' => 'Debe ingresar un correo electrónico válido.',
            'sexo_trab.required' => 'Selecciona un sexo.',
            'fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'id_departamento.required' => 'Selecciona un departamento.',
            'id_departamento.exists' => 'El departamento seleccionado no es válido.',
        ]);

        try {
            Log::info('Validated data for store: ', $validated);
            $trabajador = Trabajadores::create($validated);
            Log::info('Created trabajador: ', $trabajador->toArray());
            return redirect()->route('trabajadores.index')->with('success', '¡Trabajador creado con éxito!');
        } catch (\Exception $e) {
            Log::error('Error al crear trabajador: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el Trabajador.');
        }
    }

    public function show(Trabajadores $trabajador)
    {
        $trabajador->load('departamento');
        $departamentos = Departamento::all();
        return view('admin.trabajadores.show', compact('trabajador', 'departamentos'));
    }

    public function edit(Trabajadores $trabajador)
    {
        $trabajador->load('departamento');
        $departamentos = Departamento::all();
        return view('admin.trabajadores.edit', compact('trabajador', 'departamentos'));
    }

    public function update(Request $request, Trabajadores $trabajador)
    {
        $validated = $request->validate([
            'nombre_trab' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100',
            'apellido_trab' => 'required|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100',
            'dni_trab' => 'required|digits:8|unique:trabajadores,dni_trab,' . $trabajador->id_trabajadores . ',id_trabajadores',
            'correo_trab' => 'required|email|max:255|unique:trabajadores,correo_trab,' . $trabajador->id_trabajadores . ',id_trabajadores',
            'num_telef' => 'required|digits:9',
            'sexo_trab' => 'required|in:Masculino,Femenino',
            'fecha_nac' => 'required|date',
            'id_departamento' => 'required|exists:departamento,id_departamento',
        ], [
            'nombre_trab.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido_trab.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'dni_trab.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'num_telef.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
            'correo_trab.email' => 'Debe ingresar un correo electrónico válido.',
            'sexo_trab.required' => 'Selecciona un sexo.',
            'fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'id_departamento.required' => 'Selecciona un departamento.',
            'id_departamento.exists' => 'El departamento seleccionado no es válido.',
        ]);

        try {
            Log::info('Validated data for update: ', $validated);
            Log::info('Trabajador before update: ', $trabajador->toArray());
            $trabajador->update($validated);
            Log::info('Trabajador after update: ', $trabajador->fresh()->toArray());
            return redirect()->route('trabajadores.index')->with('success', 'Trabajador actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar trabajador: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo actualizar el Trabajador.');
        }
    }

    public function destroy(Trabajadores $trabajador)
    {
        try {
            $trabajador->delete();
            return redirect()->route('trabajadores.index')->with('success', 'Trabajador eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar trabajador: ' . $e->getMessage());
            return redirect()->route('trabajadores.index')->with('error', '¡Ups! Algo falló al intentar eliminar el trabajador.');
        }
    }

    public function list(Request $request)
    {
        try {
            $proyecto_id = (int) $request->query('proyecto_id', 0);
            $baseQuery = Trabajadores::select('id_trabajadores', 'nombre_trab', 'apellido_trab', 'dni_trab')
                ->whereNull('deleted_at')
                ->orderBy('nombre_trab')
                ->orderBy('apellido_trab');

            $trabajadores = (clone $baseQuery)
                ->when($proyecto_id > 0, function ($query) use ($proyecto_id) {
                    // Excluir trabajadores ya asignados al proyecto (sin depender de columna deleted_at en planilla)
                    $query->whereNotIn('id_trabajadores', function ($sub) use ($proyecto_id) {
                        $sub->select('id_trabajadores')
                            ->from('planilla')
                            ->where('id_proyecto', $proyecto_id);
                    });
                })
                ->take(100)
                ->get();

            if ($trabajadores->isEmpty()) {
                // Fallback: devolver todos los trabajadores, para que el front permita seleccionar
                $trabajadores = (clone $baseQuery)->take(100)->get();
                Log::warning('TrabajadoresController@list: lista vacía con filtro, devolviendo todos', [
                    'proyecto_id' => $proyecto_id,
                    'fallback_count' => $trabajadores->count()
                ]);
            } else {
                Log::info('TrabajadoresController@list', [
                    'proyecto_id' => $proyecto_id,
                    'count' => $trabajadores->count()
                ]);
            }

            return response()->json($trabajadores);
        } catch (\Exception $e) {
            Log::error('Error al listar trabajadores: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'No se pudo listar los trabajadores'], 500);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new TrabajadoresExport, 'trabajadores.xlsx');
    }

    public function exportPdf()
    {
        $trabajadores = Trabajadores::with('departamento')->get();
        Log::info('Trabajadores cargados para PDF:', [
            'count' => $trabajadores->count(),
            'trabajadores' => $trabajadores->map(function ($t) {
                return [
                    'id_trabajadores' => $t->id_trabajadores,
                    'id_departamento' => $t->id_departamento,
                    'departamento' => $t->departamento ? $t->departamento->toArray() : null,
                ];
            })->toArray(),
        ]);
        $departamentos = Departamento::all();
        $pdf = Pdf::loadView('admin.trabajadores.trabajadores_pdf', compact('trabajadores', 'departamentos'))
                ->setPaper('a4', 'landscape');
        return $pdf->download('trabajadores.pdf');
    }
}