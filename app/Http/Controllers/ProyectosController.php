<?php

namespace App\Http\Controllers;

use App\Models\Proyectos;
use App\Models\Proveedor;
use App\Models\Planilla;
use App\Models\Trabajadores;
use App\Models\ActividadProyecto;
use App\Models\Materiales;
use App\Models\Servicio;
use App\Models\GastosExtra;
use App\Models\Egresos;
use App\Models\Asistencia;
use App\Models\BalanceGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProyectosController extends Controller
{
    /**
     * Verifica si un proyecto está finalizado
     */
    private function isProyectoFinalizado($proyecto_id)
    {
        return \DB::table('fechapr')
            ->where('proyecto_id', $proyecto_id)
            ->whereNotNull('fecha_fin_true')
            ->exists();
    }

    // ProyectosController.php
    public function dashboardHome()
    {
        try {
            // Obtener el primer proyecto (si existe) y redirigir a su dashboard
            $primerProyecto = Proyectos::first();

            if ($primerProyecto) {
                return redirect()->route('dashboard.proyecto', $primerProyecto->id_proyecto);
            }

            // Si no hay proyectos, devolver la vista dashboard con datos vacíos
            $proyectos = Proyectos::select('id_proyecto', 'nombre_proyecto', 'cliente_proyecto')->get();

            return view('dashboard', [
                'proyectos' => $proyectos,
                'proyecto'  => null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en ProyectosController@dashboardHome: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response('Error al cargar el dashboard', 500);
        }
    }

    /**
     * Detalle de un día del calendario: lista de trabajadores presentes y ausentes
     * Endpoint: GET /api/proyectos/{proyecto}/calendar/day/{date}
     * Respuesta:
     * { date: 'YYYY-MM-DD', presentes: [...], ausentes: [...], counts: { presentes: int, ausentes: int, total: int } }
     */
    public function getCalendarDayDetails(Proyectos $proyecto, $date)
    {
        try {
            // Validar formato de fecha
            try {
                $day = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Fecha inválida. Use formato YYYY-MM-DD'], 422);
            }

            // Obtener planilla activa con datos del trabajador
            $planillas = $proyecto->planilla()
                ->whereNull('deleted_at')
                ->with(['trabajador' => function ($q) {
                    $q->select('id_trabajadores', 'nombre_trab', 'apellido_trab', 'dni_trab');
                }])
                ->get(['id_planilla', 'id_trabajadores']);

            // IDs de planilla presentes en la fecha
            $presentPlanillaIds = Asistencia::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->whereNotNull('hora')
                ->whereDate('fecha', $day->toDateString())
                ->pluck('id_planilla')
                ->unique();

            $presentes = [];
            $ausentes = [];

            foreach ($planillas as $p) {
                $item = [
                    'id_planilla' => $p->id_planilla,
                    'id_trabajadores' => $p->id_trabajadores,
                    'nombre' => $p->trabajador->nombre_trab ?? '',
                    'apellido' => $p->trabajador->apellido_trab ?? '',
                    'dni' => $p->trabajador->dni_trab ?? '',
                ];
                if ($presentPlanillaIds->contains($p->id_planilla)) {
                    $presentes[] = $item;
                } else {
                    $ausentes[] = $item;
                }
            }

            return response()->json([
                'date' => $day->toDateString(),
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'counts' => [
                    'presentes' => count($presentes),
                    'ausentes' => count($ausentes),
                    'total' => $planillas->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getCalendarDayDetails: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'date' => $date,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Error al cargar detalles del día'], 500);
        }
    }

    public function dashboard($id = null)
    {
        try {
            // Lista de proyectos para el select (solo campos necesarios)
            $proyectos = Proyectos::with('montopr')->select('id_proyecto', 'nombre_proyecto', 'cliente_proyecto')->get();

            // Cargar el proyecto seleccionado (con montopr para mostrar monto inicial)
            $proyecto = null;
            if ($id) {
                $proyecto = Proyectos::with('montopr')->find($id);
            } else {
                $proyecto = Proyectos::with('montopr')->first();
            }

            // Si no encontré el proyecto pedido pero hay proyectos, usar el primero
            if (!$proyecto && $proyectos->count() > 0) {
                $primer = $proyectos->first();
                $proyecto = Proyectos::with('montopr')->find($primer->id_proyecto);
            }

            // Retornar la vista principal (los datos de charts se llamarán por AJAX)
            return view('dashboard', compact('proyectos', 'proyecto'));
        } catch (\Exception $e) {
            \Log::error('Error en ProyectosController@dashboard: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            return response('Error al cargar el dashboard', 500);
        }
    }

   public function index()
    {
        try {
            Log::info('Iniciando index de proyectos', [
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB',
                'user_id' => auth()->id() ?? 'No autenticado'
            ]);

            $proyectos = Proyectos::with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->select([
                'proyectos.id_proyecto',
                'proyectos.nombre_proyecto',
                'proyectos.descripcion_proyecto',
                'proyectos.cliente_proyecto',
                'proyectos.cantidad_trabajadores',
                'proyectos.sueldo',
                'proyectos.fecha_creacion',
                'proyectos.updated_at',
                'proyectos.user_id',
            ])
            ->leftJoin('montopr', 'proyectos.id_proyecto', '=', 'montopr.proyecto_id')
            ->leftJoin('fechapr', 'proyectos.id_proyecto', '=', 'fechapr.proyecto_id')
            ->selectRaw('montopr.monto_inicial as monto, fechapr.fecha_inicio, fechapr.fecha_fin_aprox')
            ->simplePaginate(50);

            $allProyectos = Proyectos::with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->select([
                'id_proyecto',
                'nombre_proyecto',
                'cliente_proyecto',
                'fecha_creacion',
                'user_id',
            ])
            ->get();

            // === AÑADE ESTO: CARGAR montos_apartados EN UNA SOLA CONSULTA ===
            $montosApartados = DB::table('montos_apartados')
                ->whereIn('id_proyecto', $allProyectos->pluck('id_proyecto'))
                ->get()
                ->keyBy('id_proyecto');
            // === FIN DE LA LÍNEA NUEVA ===

            Log::info('Proyectos cargados', [
                'count' => count($proyectos->items()),
                'total_count' => $allProyectos->count(),
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            return view('admin.proyectos.index', compact('proyectos', 'allProyectos', 'montosApartados'));

        } catch (\Exception $e) {
            Log::error('Error al listar los proyectos: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? 'No autenticado'
            ]);
            return response('Error al cargar proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        return view('admin.proyectos.create');
    }

   public function loadMoreProjects(Request $request)
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 4); // Default to 4 projects

            $proyectos = Proyectos::select([
                'id_proyecto',
                'nombre_proyecto',
                'cliente_proyecto',
                'fecha_creacion',
                'user_id',
            ])
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderByDesc('fecha_creacion')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($proyecto) {
                return [
                    'id_proyecto' => $proyecto->id_proyecto,
                    'nombre_proyecto' => $proyecto->nombre_proyecto,
                    'cliente_proyecto' => $proyecto->cliente_proyecto,
                    'fecha_creacion' => $proyecto->fecha_creacion ? $proyecto->fecha_creacion->toISOString() : null,
                    'user_id' => $proyecto->user_id,
                    'user' => [
                        'name' => $proyecto->user->name ?? 'Desconocido',
                    ],
                ];
            });

            Log::info('Cargando más proyectos', [
                'offset' => $offset,
                'limit' => $limit,
                'count' => $proyectos->count(),
            ]);

            return response()->json($proyectos);
        } catch (\Exception $e) {
            Log::error('Error al cargar más proyectos: ' . $e->getMessage(), [
                'offset' => $offset,
                'limit' => $limit,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Error al cargar más proyectos'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre_proyecto' => 'required|string|max:100',
                'cliente_proyecto' => 'required|string|max:100',
                'descripcion_proyecto' => 'nullable|string',
                'cantidad_trabajadores' => 'required|integer|min:0',
                'monto_material' => 'required|numeric|min:0',
                'monto_operativos' => 'required|numeric|min:0',
                'monto_servicios' => 'required|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_fin_aprox' => 'nullable|date|after_or_equal:fecha_inicio',
            ]);

            $proyecto = Proyectos::create(array_merge(
                $request->only([
                    'nombre_proyecto',
                    'cliente_proyecto',
                    'descripcion_proyecto',
                    'cantidad_trabajadores',
                ]),
                [
                    'user_id' => auth()->id(),
                    // Ya no se solicita 'sueldo' en el formulario; se establece a 0 por defecto
                    'sueldo' => 0,
                ]
            ));

            // Las inserciones en 'fechapr', 'montopr' y 'montos_apartados' se manejan en ProyectosObserver@created

            return redirect()->route('proyectos.index')->with('success', '¡Proyecto creado con éxito!');
            } catch (ValidationException $e) {
                $mensajeErrores = collect($e->errors())->flatten()->implode(', ');

                Log::error('Error de validación al crear proyecto: ' . $mensajeErrores, [
                    'request' => json_encode($request->all()),
                ]);

                return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el proyecto: ' . $mensajeErrores);
            }  catch (\Exception $e) {
                Log::error('Error al crear proyecto: ' . $e->getMessage(), [
                    'request' => json_encode($request->all()), // JSON seguro
                    'exception_message' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
            ]);
            return redirect()->back()->withInput()->with('error', '¡Ups! No se pudo crear el proyecto.');
        }
    }

    public function show(Proyectos $proyecto)
    {
        try {
            Log::info('Iniciando show de proyecto', [
                'proyecto_id' => $proyecto->id_proyecto,
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            $proyecto->load([
                'montopr' => function ($query) {
                    $query->select('proyecto_id', 'monto_inicial', 'monto_deseado');
                },
                'fechapr' => function ($query) {
                    $query->select('proyecto_id', 'fecha_inicio', 'fecha_fin_aprox', 'fecha_fin_true');
                }
            ]);

            $planilla = Planilla::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->select([
                    'id_planilla',
                    'id_proyecto',
                    'id_trabajadores',
                    'dias_trabajados',
                    'pago_dia',
                    'pago',
                    'alimentacion_trabajador',
                    'hospedaje_trabajador',
                    'pasajes_trabajador',
                    'estado'
                ])
                ->with(['trabajador' => function ($query) {
                    $query->select('id_trabajadores', 'nombre_trab', 'apellido_trab', 'dni_trab');
                }])
                ->paginate(50);

            $gastosExtra = GastosExtra::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->select([
                    'id_gasto',
                    'id_proyecto',
                    'alimentacion_general',
                    'hospedaje',
                    'pasajes',
                    'created_at'
                ])
                ->paginate(50);

            $materiales = Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->select([
                    'id_material',
                    'id_proyecto',
                    'descripcion_mat',
                    'id_proveedor',
                    'monto_mat',
                    'fecha_mat',
                    'updated_at' // Added updated_at
                ])
                ->with(['proveedor' => function ($query) {
                    $query->select('id_proveedor', 'nombre_prov');
                }])
                ->paginate(50);
            

            $servicios = Servicio::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->select([
                    'id_servicio',
                    'id_proyecto',  
                    'descripcion_serv',
                    'monto',
                    'created_at'
                ])
                ->paginate(50);


            Log::info('Proyecto, planilla, gastos extras y materiales cargados', [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_count' => $planilla->total(),
                'gastos_extras_count' => $gastosExtra->total(),
                'materiales_count' => $materiales->total(),
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            // Presupuestos por apartado (montos asignados y gastados)
            $apartado = DB::table('montos_apartados')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->first();

            $materialesGastado = (float) Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->sum('monto_mat');
            $personalGastado = (float) DB::table('planilla')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador),0) AS total')
                ->value('total');
            $serviciosGastado = (float) Servicio::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->sum('monto');
            // Gastos extras sumados a servicios
            $gExtraAli = (float) GastosExtra::where('id_proyecto', $proyecto->id_proyecto)->whereNull('deleted_at')->sum('alimentacion_general');
            $gExtraHos = (float) GastosExtra::where('id_proyecto', $proyecto->id_proyecto)->whereNull('deleted_at')->sum('hospedaje');
            $gExtraPas = (float) GastosExtra::where('id_proyecto', $proyecto->id_proyecto)->whereNull('deleted_at')->sum('pasajes');
            $serviciosGastado += ($gExtraAli + $gExtraHos + $gExtraPas);

            $budgetMaterials = [
                'assigned' => (float) ($apartado->monto_material ?? 0),
                'spent'    => $materialesGastado,
            ];
            $budgetMaterials['remaining'] = max(0, $budgetMaterials['assigned'] - $budgetMaterials['spent']);

            $budgetPersonal = [
                'assigned' => (float) ($apartado->monto_operativos ?? 0),
                'spent'    => $personalGastado,
            ];
            $budgetPersonal['remaining'] = max(0, $budgetPersonal['assigned'] - $budgetPersonal['spent']);

            $budgetServicios = [
                'assigned' => (float) ($apartado->monto_servicios ?? 0),
                'spent'    => $serviciosGastado,
            ];
            $budgetServicios['remaining'] = max(0, $budgetServicios['assigned'] - $budgetServicios['spent']);

            // Pre-cargar trabajadores disponibles (no asignados al proyecto) como fallback para el modal
            $trabajadoresPreload = \App\Models\Trabajadores::select('id_trabajadores', 'nombre_trab', 'apellido_trab', 'dni_trab')
                ->whereNull('deleted_at')
                ->whereNotIn('id_trabajadores', function ($q) use ($proyecto) {
                    $q->select('id_trabajadores')
                        ->from('planilla')
                        ->where('id_proyecto', $proyecto->id_proyecto)
                        ->whereNull('deleted_at');
                })
                ->orderBy('nombre_trab')
                ->orderBy('apellido_trab')
                ->take(100)
                ->get();

            return view('admin.proyectos.show', compact('proyecto', 'planilla', 'gastosExtra', 'materiales', 'servicios', 'budgetMaterials', 'budgetPersonal', 'budgetServicios', 'trabajadoresPreload'));
        } catch (\Exception $e) {
            Log::error('Error al mostrar proyecto: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('Error al cargar proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function getDataSueldos(Proyectos $proyecto)
    {
        try {
            $proyecto->load('fechapr');
            $fechaInicio = $proyecto->fechapr->fecha_inicio;
            $fechaFin = $proyecto->fechapr->fecha_fin_aprox ?? now();
            $diasTotales = max(1, $fechaFin->diffInDays($fechaInicio) + 1);
            $sueldoDiario = $proyecto->sueldo;
            $totalPlazas = $proyecto->cantidad_trabajadores;
            $trabajadoresEnPlanilla = $proyecto->planilla()->whereNull('deleted_at')->count();
            $sueldoTotalProyectado = $diasTotales * $sueldoDiario * $totalPlazas;
            $sueldoPorTrabajador = $trabajadoresEnPlanilla > 0 ? $sueldoTotalProyectado / $trabajadoresEnPlanilla : 0;

            return response()->json([
                'fechapr' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin_aprox' => $fechaFin,
                ],
                'sueldo' => $sueldoDiario,
                'cantidad_trabajadores' => $totalPlazas,
                'trabajadores_planilla' => $trabajadoresEnPlanilla,
                'dias_totales' => $diasTotales,
                'sueldo_total' => $sueldoTotalProyectado,
                'sueldo_por_trabajador' => $sueldoPorTrabajador,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos para sueldos: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
            ]);
            return response()->json(['error' => 'Error al cargar datos del proyecto.'], 500);
        }
    }

    public function edit(Proyectos $proyecto){
        return view('admin.proyectos.edit', compact('proyecto'));
    }

    public function update(Request $request, Proyectos $proyecto)
    {
        try {
            $request->validate([
                'nombre_proyecto' => 'required|string|max:100',
                'cliente_proyecto' => 'required|string|max:100',
                'descripcion_proyecto' => 'nullable|string',
                'cantidad_trabajadores' => 'required|integer|min:0',
                'monto_material' => 'required|numeric|min:0',
                'monto_operativos' => 'required|numeric|min:0',
                'monto_servicios' => 'required|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_fin_aprox' => 'nullable|date|after_or_equal:fecha_inicio',
            ]);

            DB::beginTransaction();

            // 1. Actualizar datos básicos del proyecto
            $proyecto->update([
                'nombre_proyecto' => $request->nombre_proyecto,
                'cliente_proyecto' => $request->cliente_proyecto,
                'descripcion_proyecto' => $request->descripcion_proyecto,
                'cantidad_trabajadores' => $request->cantidad_trabajadores,
            ]);

            // 2. Calcular monto inicial
            $monto_inicial = $request->monto_material + $request->monto_operativos + $request->monto_servicios;

            // 3. Actualizar montos_apartados
            DB::table('montos_apartados')->updateOrInsert(
                ['id_proyecto' => $proyecto->id_proyecto],
                [
                    'monto_material' => $request->monto_material,
                    'monto_operativos' => $request->monto_operativos,
                    'monto_servicios' => $request->monto_servicios,
                    'updated_at' => now(),
                ]
            );

            // 4. Actualizar montopr (monto_inicial y monto_deseado)
            DB::table('montopr')->updateOrInsert(
                ['proyecto_id' => $proyecto->id_proyecto],
                [
                    'monto_inicial' => $monto_inicial,
                    'monto_deseado' => $monto_inicial * 1.2, // +20%
                    'updated_at' => now(),
                ]
            );

            // 5. Actualizar fechas en fechapr
            DB::table('fechapr')->updateOrInsert(
                ['proyecto_id' => $proyecto->id_proyecto],
                [
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin_aprox' => $request->fecha_fin_aprox,
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            return redirect()->route('proyectos.index')
                ->with('success', '¡Proyecto actualizado con éxito!');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación al actualizar proyecto: ' . implode(', ', $e->errors()), [
                'request' => $request->all(),
            ]);
            return redirect()->back()->withInput()
                ->with('error', '¡Ups! No se pudo actualizar el proyecto: ' . implode(', ', $e->errors()));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar proyecto: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);
            return redirect()->back()->withInput()
                ->with('error', '¡Ups! No se pudo actualizar el proyecto.');
        }
    }

    public function destroy(Proyectos $proyecto){
        try {
            // Soft delete de registros relacionados
            $proyecto->fechapr()->delete();
            $proyecto->montopr()->delete();
            $proyecto->planilla()->delete();
            $proyecto->materiales()->delete();
            $proyecto->gastosExtra()->delete();
            $proyecto->egresos()->delete();
            $proyecto->controlGastos()->delete();
            $proyecto->balanceGeneral()->delete();

            // Soft delete del proyecto
            $proyecto->delete();

            return redirect()->route('proyectos.index')->with('success', 'Proyecto movido a la papelera exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al mover proyecto a la papelera: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'exception' => $e
            ]);
            return redirect()->route('proyectos.index')->with('error', '¡Ups! No se pudo mover el proyecto a la papelera.');
        }
    }

    public function addPlanilla(Request $request, Proyectos $proyecto){
        try {
            if ($this->isProyectoFinalizado($proyecto->id_proyecto)) {
                Log::warning('Intento de agregar planilla en proyecto finalizado', ['proyecto_id' => $proyecto->id_proyecto]);
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden agregar más registros.'], 400);
            }

            Log::info('Iniciando addPlanilla', [
                'proyecto_id' => $proyecto->id_proyecto,
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            $validated = $request->validate([
                'trabajador_id' => 'required|integer|exists:trabajadores,id_trabajadores',
            ]);

            $exists = Planilla::where('id_proyecto', $proyecto->id_proyecto)
                ->where('id_trabajadores', $request->trabajador_id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                Log::warning('Trabajador ya existe en planilla', ['trabajador_id' => $request->trabajador_id, 'proyecto_id' => $proyecto->id_proyecto]);
                return response()->json(['error' => 'El trabajador ya está en la planilla de este proyecto.'], 400);
            }

            // REMOVIDO: Chequeo de presupuesto (mover a operaciones que gastan, no a agregar trabajador)
            // Si quieres warn en lugar de block, puedes agregar log o response warn, pero no error 400.

            $planilla = Planilla::create([
                'id_proyecto' => $proyecto->id_proyecto,
                'id_trabajadores' => $request->trabajador_id,
                'dias_trabajados' => 0,
                'pago' => 0,
                'alimentacion_trabajador' => 0,
                'hospedaje_trabajador' => 0,
                'pasajes_trabajador' => 0,
                'estado' => 'NO LIQUIDADO',
            ]);

            Log::info('Planilla creada', ['planilla_id' => $planilla->id_planilla]);

            $trabajador = Trabajadores::find($request->trabajador_id, ['id_trabajadores', 'dni_trab', 'nombre_trab', 'apellido_trab']);

            Log::info('Memoria después de crear planilla', ['memory' => memory_get_usage() / 1024 / 1024 . ' MB']);

            $nombreCompleto = trim(($trabajador->nombre_trab ?? '') . ' ' . ($trabajador->apellido_trab ?? ''));
            
            $responseData = [
                'success' => true,
                'id' => $planilla->id_planilla,
                'dni_trab' => $trabajador->dni_trab ?? '',
                'nombre_completo' => $nombreCompleto,
                'dias_trabajados' => $planilla->dias_trabajados,
                'pago' => $planilla->pago,
                'alimentacion_trabajador' => $planilla->alimentacion_trabajador,
                'hospedaje_trabajador' => $planilla->hospedaje_trabajador,
                'pasajes_trabajador' => $planilla->pasajes_trabajador,
                'estado' => $planilla->estado,
            ];

            Log::info('Trabajador agregado exitosamente', [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla,
                'trabajador_id' => $request->trabajador_id,
                'response_data' => $responseData
            ]);
            
            return response()->json($responseData, 201);
        } catch (ValidationException $e) {
            Log::error('Error de validación en addPlanilla', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = implode(', ', $messages);
            }
            return response()->json(['error' => 'Error de validación: ' . implode('. ', $errorMessages)], 422);
        } catch (\Exception $e) {
            Log::error('Error al agregar registro a la planilla: ' . $e->getMessage(), [
                'request' => $request->all(),
                'proyecto_id' => $proyecto->id_proyecto ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al agregar el trabajador: ' . $e->getMessage()], 500);
        }
    }

    
    public function removePlanilla(Proyectos $proyecto, Planilla $planilla)
    {
        try {
            // Verificar si el proyecto está finalizado
            if ($this->isProyectoFinalizado($proyecto->id_proyecto)) {
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
            }

            // Verificar que la planilla pertenece al proyecto (seguridad extra)
            if ($planilla->id_proyecto !== $proyecto->id_proyecto) {
                return response()->json(['error' => 'La planilla no pertenece a este proyecto.'], 403);
            }

            // Eliminar (soft delete)
            $planilla->delete();

            return response()->json(['success' => true, 'message' => 'Trabajador eliminado de la planilla correctamente.']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de la planilla: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'No se pudo eliminar el registro.'], 500);
        }
    }

    public function getPlanilla(Proyectos $proyecto, Planilla $planilla)
    {
        try {
            if ($planilla->id_proyecto !== $proyecto->id_proyecto) {
                return response()->json(['error' => 'La planilla no pertenece a este proyecto.'], 403);
            }

            return response()->json([
                'id_planilla' => $planilla->id_planilla,
                'alimentacion_trabajador' => $planilla->alimentacion_trabajador,
                'hospedaje_trabajador' => $planilla->hospedaje_trabajador,
                'pasajes_trabajador' => $planilla->pasajes_trabajador,
                'dias_trabajados' => $planilla->dias_trabajados,
                'pago' => $planilla->pago,
                'estado' => $planilla->estado,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de la planilla: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla,
            ]);
            return response()->json(['error' => 'Error al cargar datos de la planilla'], 500);
        }
    }

    public function updatePlanillaGastos(Request $request, Proyectos $proyecto, Planilla $planilla){
        try {
            // Verificar si el proyecto está finalizado
            if ($this->isProyectoFinalizado($proyecto->id_proyecto)) {
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
            }

            $request->validate([
                'alimentacion_trabajador' => 'required|numeric|min:0',
                'hospedaje_trabajador' => 'required|numeric|min:0',
                'pasajes_trabajador' => 'required|numeric|min:0',
            ]);

            if ($planilla->id_proyecto !== $proyecto->id_proyecto) {
                return response()->json(['error' => 'La planilla no pertenece a este proyecto.'], 403);
            }

            // Enforce budget (Personal) before applying increments
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();
            $assigned = (float)($apartado->monto_operativos ?? 0);
            $spent = (float) DB::table('planilla')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador),0) AS total')
                ->value('total');
            $increment = (float)$request->alimentacion_trabajador + (float)$request->hospedaje_trabajador + (float)$request->pasajes_trabajador;
            if ($spent + $increment > $assigned + 1e-9) {
                return response()->json(['error' => 'Se alcanzó el límite del presupuesto de Personal.'], 400);
            }

            // Sumar los nuevos valores a los existentes
            $planilla->alimentacion_trabajador += $request->alimentacion_trabajador;
            $planilla->hospedaje_trabajador += $request->hospedaje_trabajador;
            $planilla->pasajes_trabajador += $request->pasajes_trabajador;
            $planilla->save();

            return response()->json([
                'success' => true,
                'id_planilla' => $planilla->id_planilla,
                'alimentacion_trabajador' => $planilla->alimentacion_trabajador,
                'hospedaje_trabajador' => $planilla->hospedaje_trabajador,
                'pasajes_trabajador' => $planilla->pasajes_trabajador,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar gastos de la planilla: ' . $e->getMessage(), [
                'request' => $request->all(),
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla,
            ]);
            return response()->json(['error' => 'No se pudo actualizar los gastos de la planilla.'], 500);
        }
    }

    public function addPlanillaGastos(Request $request, $proyecto, $planilla){
        // Verificar si el proyecto está finalizado
        if ($this->isProyectoFinalizado($proyecto)) {
            return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
        }

        $planilla = Planilla::findOrFail($planilla);

        $nuevaAlimentacion = $request->input('alimentacion_trabajador', 0);
        $nuevoHospedaje = $request->input('hospedaje_trabajador', 0);
        $nuevoPasajes = $request->input('pasajes_trabajador', 0);

        // Sumar los nuevos valores a los existentes
        $planilla->alimentacion_trabajador += $nuevaAlimentacion;
        $planilla->hospedaje_trabajador += $nuevoHospedaje;
        $planilla->pasajes_trabajador += $nuevoPasajes;

        $planilla->save();

        return response()->json([
            'alimentacion_trabajador' => $planilla->alimentacion_trabajador,
            'hospedaje_trabajador' => $planilla->hospedaje_trabajador,
            'pasajes_trabajador' => $planilla->pasajes_trabajador,
        ]);
    }

    public function storeMaterial(Request $request, $proyecto)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'El proyecto ya está finalizado. No se pueden agregar más registros.'], 400);
            }

            $request->validate([
                'descripcion_mat' => 'required|string|max:255',
                'id_proveedor' => 'required|exists:proveedores,id_proveedor',
                'monto_mat' => 'required|numeric|min:0',
            ]);

            $proyecto = Proyectos::findOrFail($proyecto);

            // Presupuesto: Materiales (bloquear si excede)
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();
            $assigned = (float)($apartado->monto_material ?? 0);
            $spent = (float) DB::table('materiales')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->sum('monto_mat');
            $nuevoMonto = (float) $request->monto_mat;
            if ($spent + $nuevoMonto > $assigned + 1e-9) {
                return response()->json(['error' => 'Se alcanzó el límite del presupuesto de Materiales.'], 400);
            }

            $material = Materiales::create([
                'id_proyecto' => $proyecto->id_proyecto,
                'descripcion_mat' => $request->descripcion_mat,
                'id_proveedor' => $request->id_proveedor,
                'monto_mat' => $request->monto_mat,
                'fecha_mat' => now(),
            ]);

            return response()->json([
                'message' => 'Material agregado correctamente.',
                'material' => [
                    'id_material' => $material->id_material,
                    'descripcion_mat' => $material->descripcion_mat,
                    'proveedor_nombre' => $material->proveedor->nombre_prov,
                    'monto_mat' => $material->monto_mat,
                    'fecha_mat' => \Carbon\Carbon::parse($material->fecha_mat)->format('d/m/Y'),
                    'updated_at' => $material->updated_at ? \Carbon\Carbon::parse($material->updated_at)->format('d/m/Y') : null
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al agregar material: ' . $e->getMessage(), [
                'request' => $request->all(),
                'proyecto_id' => $proyecto,
            ]);
            return response()->json(['error' => 'Error al agregar el material: ' . $e->getMessage()], 500);
        }
    }

    public function updateMaterial(Request $request, $proyecto, $id)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'El proyecto ya está finalizado. No se pueden modificar registros.'], 400);
            }

            $request->validate([
                'descripcion_mat' => 'required|string|max:255',
                'id_proveedor' => 'required|exists:proveedores,id_proveedor',
                'monto_mat' => 'required|numeric|min:0',
            ]);

            $proyecto = Proyectos::findOrFail($proyecto);
            $material = Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->where('id_material', $id)
                ->firstOrFail();

            // Presupuesto: Materiales (validar delta)
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();
            $assigned = (float)($apartado->monto_material ?? 0);
            $spent = (float) DB::table('materiales')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->sum('monto_mat');
            $montoActual = (float) $material->monto_mat;
            $montoNuevo = (float) $request->monto_mat;
            $nuevoTotal = $spent - $montoActual + $montoNuevo;
            if ($nuevoTotal > $assigned + 1e-9) {
                return response()->json(['error' => 'Se alcanzó el límite del presupuesto de Materiales.'], 400);
            }

            $material->update([
                'descripcion_mat' => $request->descripcion_mat,
                'id_proveedor' => $request->id_proveedor,
                'monto_mat' => $request->monto_mat,
            ]);

            return response()->json([
                'message' => 'Material actualizado correctamente.',
                'material' => [
                    'id_material' => $material->id_material,
                    'descripcion_mat' => $material->descripcion_mat,
                    'proveedor_nombre' => $material->proveedor->nombre_prov,
                    'monto_mat' => $material->monto_mat,
                    'fecha_mat' => \Carbon\Carbon::parse($material->fecha_mat)->format('d/m/Y'),
                    'updated_at' => $material->updated_at ? \Carbon\Carbon::parse($material->updated_at)->format('d/m/Y') : null
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar material: ' . $e->getMessage(), [
                'request' => $request->all(),
                'proyecto_id' => $proyecto,
                'material_id' => $id,
            ]);
            return response()->json(['error' => 'Error al actualizar el material: ' . $e->getMessage()], 500);
        }
    }

    public function destroyMaterial($proyecto, $id)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'El proyecto ya está finalizado. No se pueden modificar registros.'], 400);
            }

            $proyecto = Proyectos::findOrFail($proyecto);
            $material = Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->where('id_material', $id)
                ->firstOrFail();

            $material->delete();

            return response()->json([
                'message' => 'Material eliminado correctamente.',
                'material_id' => $id
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar material: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto,
                'material_id' => $id,
            ]);
            return response()->json(['error' => 'Error al eliminar el material: ' . $e->getMessage()], 500);
        }
    }

    public function getMaterial($proyecto, $id){
        try {
            $proyecto = Proyectos::findOrFail($proyecto);
            $material = Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->where('id_material', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'id_material' => $material->id_material,
                'descripcion_mat' => $material->descripcion_mat,
                'id_proveedor' => $material->id_proveedor,
                'monto_mat' => $material->monto_mat,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener material: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto,
                'material_id' => $id,
            ]);
            return response()->json(['error' => 'Error al obtener el material: ' . $e->getMessage()], 500);
        }
    }

    public function getMaterialesData(Proyectos $proyecto)
    {
        try {
            $materiales = Materiales::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->select(
                    'descripcion_mat',
                    \DB::raw('SUM(monto_mat) as monto_total'),
                    \DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('descripcion_mat')
                ->orderByDesc('monto_total')
                ->get();

            return response()->json([
                'labels' => $materiales->pluck('descripcion_mat')->toArray(),
                'montos' => $materiales->pluck('monto_total')->toArray(),
                'cantidad' => $materiales->pluck('cantidad')->toArray(), // opcional
            ]);

        } catch (\Exception $e) {
            Log::error('Error materiales: ' . $e->getMessage());
            return response()->json(['error' => 'Error materiales'], 500);
        }
    }

    public function agregarSueldos(Request $request)
    {
        $proyecto_id = $request->input('proyecto_id');
        $sueldo_por_trabajador = $request->input('sueldo_por_trabajador');

        // Verificar si el proyecto está finalizado
        if ($this->isProyectoFinalizado($proyecto_id)) {
            return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
        }

        \Log::info('agregarSueldos invoked', [
            'user_id' => auth()->id(),
            'proyecto_id' => $proyecto_id
        ]);

        try {
            // Actualizar el campo pago de todas las planillas de ese proyecto
            $updated = \App\Models\Planilla::where('id_proyecto', $proyecto_id)
                ->update([
                    'pago' => $sueldo_por_trabajador,
                    'updated_at' => now()
                ]);

            \Log::info('Sueldos aplicados', [
                'proyecto_id' => $proyecto_id,
                'updated' => $updated,
                'sueldo_por_trabajador' => $sueldo_por_trabajador
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sueldos actualizados correctamente',
                'updated' => $updated
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al aplicar sueldos', [
                'proyecto_id' => $proyecto_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar sueldos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Establecer pago_dia para una fila de planilla
    public function setPagoDia(Request $request, Proyectos $proyecto, Planilla $planilla)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto->id_proyecto)) {
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
            }

            if ($planilla->id_proyecto !== $proyecto->id_proyecto) {
                return response()->json(['error' => 'La planilla no pertenece a este proyecto.'], 403);
            }

            $request->validate([
                'pago_dia' => 'required|numeric|min:0'
            ]);

            $planilla->pago_dia = $request->pago_dia;
            $planilla->save();

            return response()->json([
                'success' => true,
                'id_planilla' => $planilla->id_planilla,
                'pago_dia' => (float)$planilla->pago_dia
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al establecer pago_dia: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla,
            ]);
            return response()->json(['error' => 'No se pudo establecer el pago diario.'], 500);
        }
    }

    // Marcar asistencia una vez por día por trabajador (planilla)
    public function marcarAsistencia(Request $request, Proyectos $proyecto, Planilla $planilla)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto->id_proyecto)) {
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
            }

            if ($planilla->id_proyecto !== $proyecto->id_proyecto) {
                return response()->json(['error' => 'La planilla no pertenece a este proyecto.'], 403);
            }

            $request->validate([
                'fecha' => 'required|date',
                'ubicacion' => 'nullable|string|max:255'
            ]);

            $fecha = \Carbon\Carbon::parse($request->fecha)->toDateString();

            // Nota: Se permite marcar asistencia a múltiples trabajadores el mismo día.

            // Verificar si ya existe asistencia hoy para esta planilla
            $yaMarcado = Asistencia::where('id_planilla', $planilla->id_planilla)
                ->whereDate('fecha', $fecha)
                ->exists();

            if ($yaMarcado) {
                return response()->json([
                    'success' => false,
                    'already_marked' => true,
                    'message' => 'La asistencia ya fue marcada hoy. Intente nuevamente mañana.'
                ], 409);
            }

            // Validación mínima: exigir pago por día configurado (> 0) antes de marcar asistencia
            if ((float)($planilla->pago_dia ?? 0) <= 0) {
                return response()->json([
                    'error' => 'Primero agrega el monto que ganara por dia tu personal'
                ], 422);
            }

            $diaSemana = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado'
            ][\Carbon\Carbon::parse($fecha)->dayOfWeek];

            Asistencia::create([
                'id_planilla' => $planilla->id_planilla,
                'id_proyecto' => $proyecto->id_proyecto,
                'hora' => now()->format('H:i:s'),
                'fecha' => $fecha,
                'ubicacion' => $request->input('ubicacion', 'No especificada'),
                'Dia_de_Semana' => $diaSemana,
            ]);

            // Enforce budget (Personal) before incrementing pago
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();
            $assigned = (float)($apartado->monto_operativos ?? 0);
            $spent = (float) DB::table('planilla')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador),0) AS total')
                ->value('total');
            $incremento = (float) ($planilla->pago_dia ?? 0);
            if ($spent + $incremento > $assigned + 1e-9) {
                return response()->json(['error' => 'Se alcanzó el límite del presupuesto de Personal.'], 400);
            }

            // Incrementar días trabajados y sumar pago de acuerdo a pago_dia
            $planilla->dias_trabajados = (int) $planilla->dias_trabajados + 1;
            $planilla->pago = (float) $planilla->pago + $incremento;
            $planilla->save();

            return response()->json([
                'success' => true,
                'message' => 'Asistencia marcada correctamente.',
                'id_planilla' => $planilla->id_planilla,
                'dias_trabajados' => (int)$planilla->dias_trabajados,
                'pago' => (float)$planilla->pago,
                'pago_dia' => (float)($planilla->pago_dia ?? 0),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => implode(', ', $e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Error al marcar asistencia: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'planilla_id' => $planilla->id_planilla,
            ]);
            return response()->json(['error' => 'No se pudo marcar la asistencia.'], 500);
        }
    }

    // Estado de asistencia hoy a nivel proyecto
    public function asistenciaStatus(Proyectos $proyecto)
    {
        try {
            $hoy = now()->toDateString();
            $existe = Asistencia::where('id_proyecto', $proyecto->id_proyecto)
                ->whereDate('fecha', $hoy)
                ->exists();
            return response()->json(['today_marked' => $existe]);
        } catch (\Exception $e) {
            return response()->json(['today_marked' => false], 200);
        }
    }

    public function getAsistenciaData(Proyectos $proyecto){
        try {
            Log::info('Obteniendo datos de asistencia', [
                'proyecto_id' => $proyecto->id_proyecto,
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            // Obtener los trabajadores del proyecto
            $trabajadores = $proyecto->planilla()
                ->with('trabajador')
                ->whereNull('deleted_at')
                ->get()
                ->pluck('trabajador')
                ->unique('id_trabajadores');

            // Obtener las fechas únicas de asistencia
            $fechas = Asistencia::whereIn('id_trabajadores', $trabajadores->pluck('id_trabajadores'))
                ->select(\DB::raw('DATE(fecha) as fecha'))
                ->groupBy(\DB::raw('DATE(fecha)'))
                ->orderBy('fecha')
                ->get()
                ->pluck('fecha')
                ->toArray();

            $datasets = [];
            $colors = [
                'Asistió' => 'rgba(75, 192, 192, 0.2)',
                'Faltó' => 'rgba(255, 99, 132, 0.2)'
            ];

            // Contar asistencias y faltas por día
            foreach (['Asistió', 'Faltó'] as $estado) {
                $data = [];
                foreach ($fechas as $fecha) {
                    $count = Asistencia::whereIn('id_trabajadores', $trabajadores->pluck('id_trabajadores'))
                        ->whereDate('fecha', $fecha)
                        ->when($estado === 'Asistió', function($query) {
                            return $query->whereNotNull('hora');
                        }, function($query) {
                            return $query->whereNull('hora');
                        })
                        ->count();
                    
                    $data[] = $estado === 'Faltó' ? 
                        $trabajadores->count() - $count : 
                        $count;
                }

                $datasets[] = [
                    'label' => $estado,
                    'data' => $data,
                    'backgroundColor' => $colors[$estado],
                    'borderColor' => str_replace('0.2', '1', $colors[$estado]),
                    'borderWidth' => 1
                ];
            }

            return response()->json([
                'labels' => $fechas,
                'datasets' => $datasets
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de asistencia: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar datos de asistencia'], 500);
        }
    }

    public function getGastosData(Proyectos $proyecto){
        try {
            Log::info('Obteniendo datos de gastos', [
                'proyecto_id' => $proyecto->id_proyecto,
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);
    
            // FIX: Cambiar first() a aggregate SUM para manejar múltiples GastosExtra
            $gastos = GastosExtra::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(alimentacion_general) as alimentacion_general, SUM(hospedaje) as hospedaje, SUM(pasajes) as pasajes')
                ->first();
    
            if (!$gastos || ($gastos->alimentacion_general == 0 && $gastos->hospedaje == 0 && $gastos->pasajes == 0)) {
                return response()->json([
                    'labels' => ['Alimentación', 'Hospedaje', 'Pasajes'],
                    'data' => [0, 0, 0]
                ]);
            }
    
            return response()->json([
                'labels' => ['Alimentación', 'Hospedaje', 'Pasajes'],
                'data' => [
                    (float)($gastos->alimentacion_general ?? 0),
                    (float)($gastos->hospedaje ?? 0),
                    (float)($gastos->pasajes ?? 0)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de gastos: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar datos de gastos'], 500);
        }
    }

    public function getEgresosData(Proyectos $proyecto){
        try {
            Log::info('Obteniendo datos de egresos', [
                'proyecto_id' => $proyecto->id_proyecto,
                'memory' => memory_get_usage() / 1024 / 1024 . ' MB'
            ]);

            // Obtener el último registro de egresos del proyecto
            $egresos = Egresos::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->orderBy('id_egreso', 'desc')
                ->first();

            if (!$egresos) {
                return response()->json([
                    'labels' => ['Materiales', 'Planilla', 'SCR', 'Gastos Administrativos', 'Gastos Extra'],
                    'data' => [0, 0, 0, 0, 0]
                ]);
            }

            return response()->json([
                'labels' => ['Materiales', 'Planilla', 'SCTR', 'Gastos Administrativos', 'Gastos Extra'],
                'data' => [
                    (float)$egresos->materiales,
                    (float)$egresos->planilla,
                    (float)$egresos->scr,
                    (float)$egresos->gastos_administrativos,
                    (float)$egresos->gastos_extra
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de egresos: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar datos de egresos'], 500);
        }
    }

    /**
     * Nuevo método: devuelve el Balance General más reciente del proyecto en formato JSON
     * Endpoint esperado por la vista: GET /api/proyectos/{proyecto}/balance
     * Devuelve: { total_servicios: float, egresos: float, ganancia_neta: float }
     */
    public function getBalanceData(Proyectos $proyecto)
    {
        try {
            Log::info('Calculando balance general en tiempo real', ['proyecto_id' => $proyecto->id_proyecto]);

            // 1. Monto inicial del proyecto
            $montoInicial = (float) ($proyecto->montopr->monto_inicial ?? 0);

            // 2. Últimos egresos (el más reciente tiene todos los totales calculados)
            $ultimoEgreso = Egresos::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->orderByDesc('id_egreso')
                ->first();

            $totalEgresos = $ultimoEgreso ? (float) $ultimoEgreso->total_egresos : 0;
            $gananciaNeta = $montoInicial - $totalEgresos;

            return response()->json([
                'monto_inicial' => $montoInicial,
                'total_egresos' => $totalEgresos,
                'ganancia_neta' => $gananciaNeta,
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculando balance: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error en balance'], 500);
        }
    }

    /**
     * Devuelve datos para el calendario del proyecto: rango de fechas y asistencia por día.
     * Endpoint: GET /api/proyectos/{proyecto}/calendar
     * Respuesta tipo:
     * {
     *   start: 'YYYY-MM-DD',
     *   end: 'YYYY-MM-DD',
     *   finished: bool,
     *   total_trabajadores: int,
     *   dias_totales: int|null,
     *   days: [ { fecha: 'YYYY-MM-DD', presentes: int, ausentes: int } ]
     * }
     */
    public function getCalendarData(Proyectos $proyecto)
    {
        try {
            // Cargar fechas del proyecto
            $proyecto->load('fechapr');
            $fechapr = $proyecto->fechapr;

            $start      = $fechapr?->fecha_inicio; // Carbon (cast en modelo)
            $plannedEnd = $fechapr?->fecha_fin_aprox; // Fin planificado
            $trueEnd    = $fechapr?->fecha_fin_true;  // Fin real si existe
            $end        = $trueEnd ?? $plannedEnd;    // Para rango general
            $today      = \Carbon\Carbon::today();

            // Total de trabajadores (planilla activa)
            $totalTrabajadores = $proyecto->planilla()->whereNull('deleted_at')->count();

            // Asistencias agrupadas por fecha (excluyendo soft-deletes)
            // Agrupar por DATE(fecha) y exponer la clave como 'fecha_str' para evitar casts a Carbon
            $asistencias = Asistencia::where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->whereNotNull('hora')
                ->select(\DB::raw('DATE(fecha) as fecha_str'), \DB::raw('COUNT(DISTINCT id_planilla) as presentes'))
                ->groupBy(\DB::raw('DATE(fecha)'))
                ->orderBy('fecha_str')
                ->get();

            $presentesPorFecha = $asistencias->pluck('presentes', 'fecha_str');

            Log::info('CalendarData: asistencia agregada', [
                'proyecto_id' => $proyecto->id_proyecto,
                'start' => $start?->toDateString(),
                'planned_end' => $plannedEnd?->toDateString(),
                'true_end' => $trueEnd?->toDateString(),
                'asistencias_count' => $asistencias->count(),
                'asistencias_sample' => $asistencias->take(5)->toArray(),
                'presentesPorFecha' => $presentesPorFecha,
                'total_trabajadores' => $totalTrabajadores,
            ]);

            $days = [];
            if ($start) {
                $s = \Carbon\Carbon::parse($start->toDateString());
                // visual_end: si hay fecha fin real, usarla; de lo contrario, usar max(fecha_fin_aprox, hoy)
                $visualEnd = null;
                if ($trueEnd) {
                    $visualEnd = \Carbon\Carbon::parse($trueEnd->toDateString());
                } else {
                    $visualEnd = $plannedEnd ? \Carbon\Carbon::parse($plannedEnd->toDateString()) : $s->copy();
                    if ($today->gt($visualEnd)) {
                        $visualEnd = $today;
                    }
                }

                // Asegurar que visualEnd no sea menor a start
                if ($visualEnd->lt($s)) {
                    $visualEnd = $s->copy();
                }

                for ($d = $s->copy(); $d->lte($visualEnd); $d->addDay()) {
                    $key = $d->toDateString();
                    $presentes = (int) ($presentesPorFecha[$key] ?? 0);
                    // No contar ausentes para fechas futuras (después de hoy)
                    $isFuture = $d->gt($today);
                    $ausentes = $isFuture ? null : max(0, (int)$totalTrabajadores - $presentes);
                    $days[] = [
                        'fecha' => $key,
                        'presentes' => $presentes,
                        'ausentes' => $ausentes,
                    ];
                }
            } else {
                // Fallback: si no hay rango, usar solo días con registros
                foreach ($presentesPorFecha as $fecha => $presentes) {
                    $days[] = [
                        'fecha' => $fecha,
                        'presentes' => (int) $presentes,
                        'ausentes' => max(0, (int)$totalTrabajadores - (int)$presentes),
                    ];
                }
            }

            // Calcular visual_end para la respuesta (coincide con la lógica anterior)
            $respVisualEnd = null;
            if ($start) {
                if ($trueEnd) {
                    $respVisualEnd = \Carbon\Carbon::parse($trueEnd->toDateString());
                } else {
                    $respVisualEnd = $plannedEnd ? \Carbon\Carbon::parse($plannedEnd->toDateString()) : \Carbon\Carbon::parse($start->toDateString());
                    $today = \Carbon\Carbon::today();
                    if ($today->gt($respVisualEnd)) {
                        $respVisualEnd = $today;
                    }
                }
            }

            Log::info('CalendarData: días generados', [
                'proyecto_id' => $proyecto->id_proyecto,
                'days_count' => count($days),
                'first_day' => $days[0]['fecha'] ?? null,
                'last_day' => $days[count($days)-1]['fecha'] ?? null,
            ]);

            return response()->json([
                'start' => $start ? $start->toDateString() : null,
                'end' => $end ? \Carbon\Carbon::parse($end)->toDateString() : null,
                'planned_end' => $plannedEnd ? \Carbon\Carbon::parse($plannedEnd)->toDateString() : null,
                'true_end' => $trueEnd ? \Carbon\Carbon::parse($trueEnd)->toDateString() : null,
                'visual_end' => $respVisualEnd ? $respVisualEnd->toDateString() : null,
                'finished' => (bool) ($fechapr?->fecha_fin_true !== null),
                'total_trabajadores' => (int) $totalTrabajadores,
                'dias_totales' => ($start && $respVisualEnd) ? ($respVisualEnd->diffInDays(\Carbon\Carbon::parse($start)) + 1) : null,
                'days' => $days,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de calendario: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto->id_proyecto,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar datos de calendario'], 500);
        }
    }

    private function getColor($entidad)
    {
        $colors = [
            'Alexis' => '#1E40AF',
            'Empresa' => '#60A5FA',
            'STARNET' => '#BFDBFE'
        ];
        return $colors[$entidad] ?? '#000000';
    }

    // === STORE GASTO EXTRA ===
    public function storeGastoExtra(Request $request, $proyecto)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'Proyecto finalizado'], 400);
            }

            $request->validate([
                'alimentacion_general' => 'required|numeric|min:0',
                'hospedaje' => 'required|numeric|min:0',
                'pasajes' => 'required|numeric|min:0',
            ]);

            $proyecto = Proyectos::findOrFail($proyecto);
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();
            $assigned = (float)($apartado->monto_operativos ?? 0); // ← PERSONAL

            // Planilla + gastos_extra
            $planillaGastado = (float) DB::table('planilla')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador), 0)')
                ->value(DB::raw('1')) ?? 0;

            $gExtra = DB::table('gastos_extra')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(alimentacion_general + hospedaje + pasajes), 0)')
                ->value(DB::raw('1')) ?? 0;

            $spent = $planillaGastado + $gExtra;
            $increment = $request->alimentacion_general + $request->hospedaje + $request->pasajes;

            if ($spent + $increment > $assigned + 1e-9) {
                return response()->json(['error' => 'Límite de presupuesto Personal alcanzado.'], 400);
            }

            $gasto = GastosExtra::create([
                'id_proyecto' => $proyecto->id_proyecto,
                'alimentacion_general' => $request->alimentacion_general,
                'hospedaje' => $request->hospedaje,
                'pasajes' => $request->pasajes,
            ]);

            return response()->json([
                'success' => true,
                'gasto' => $gasto,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('storeGastoExtra: ' . $e->getMessage());
            return response()->json(['error' => 'Error'], 500);
        }
    }

    // === UPDATE GASTO EXTRA ===
    public function updateGastoExtra(Request $request, $proyecto, $id)
    {
        try {
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'Proyecto finalizado'], 400);
            }

            $request->validate([
                'alimentacion_general' => 'required|numeric|min:0',
                'hospedaje' => 'required|numeric|min:0',
                'pasajes' => 'required|numeric|min:0',
            ]);

            $gasto = GastosExtra::where('id_proyecto', $proyecto)->where('id_gasto', $id)->firstOrFail();
            $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto)->first();
            $assigned = (float)($apartado->monto_operativos ?? 0); // ← PERSONAL

            $planillaGastado = (float) DB::table('planilla')
                ->where('id_proyecto', $proyecto)
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador), 0)')
                ->value(DB::raw('1')) ?? 0;

            $gExtra = DB::table('gastos_extra')
                ->where('id_proyecto', $proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(alimentacion_general + hospedaje + pasajes), 0)')
                ->value(DB::raw('1')) ?? 0;

            $spent = $planillaGastado + $gExtra;
            $oldTotal = $gasto->alimentacion_general + $gasto->hospedaje + $gasto->pasajes;
            $newTotal = $request->alimentacion_general + $request->hospedaje + $request->pasajes;
            $delta = $newTotal - $oldTotal;

            if ($spent + $delta > $assigned + 1e-9) {
                return response()->json(['error' => 'Límite de presupuesto Personal alcanzado.'], 400);
            }

            $gasto->update([
                'alimentacion_general' => $request->alimentacion_general,
                'hospedaje' => $request->hospedaje,
                'pasajes' => $request->pasajes,
            ]);

            return response()->json([
                'success' => true,
                'gasto' => $gasto,
            ]);
        } catch (\Exception $e) {
            \Log::error('updateGastoExtra: ' . $e->getMessage());
            return response()->json(['error' => 'Error'], 500);
        }
    }

    public function destroyGastoExtra($proyecto, $id){
        try {
            // Verificar si el proyecto está finalizado
            if ($this->isProyectoFinalizado($proyecto)) {
                return response()->json(['error' => 'El proyecto ya esta finalizado. No se pueden modificar registros.'], 400);
            }

            $gasto = GastosExtra::where('id_proyecto', $proyecto)
                ->where('id_gasto', $id)
                ->firstOrFail();

            $gasto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gasto extra eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar gasto extra: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto,
                'gasto_id' => $id,
            ]);
            return response()->json(['error' => 'Error al eliminar el gasto extra: ' . $e->getMessage()], 500);
        }
    }

    public function getGastoExtra($proyecto, $id){
        try {
            $gasto = GastosExtra::where('id_proyecto', $proyecto)
                ->where('id_gasto', $id)
                ->firstOrFail();

            return response()->json([
                'id_gasto' => $gasto->id_gasto,
                'alimentacion_general' => $gasto->alimentacion_general,
                'hospedaje' => $gasto->hospedaje,
                'pasajes' => $gasto->pasajes,
                'created_at' => $gasto->created_at->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener gasto extra: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto,
                'gasto_id' => $id,
            ]);
            return response()->json(['error' => 'Error al obtener el gasto extra: ' . $e->getMessage()], 404);
        }
    }

    public function getGastosExtraData($proyecto){
        try {
            $gastos = GastosExtra::where('id_proyecto', $proyecto)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(alimentacion_general) as alimentacion_general, SUM(hospedaje) as hospedaje, SUM(pasajes) as pasajes')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    floatval($gastos->alimentacion_general ?? 0),
                    floatval($gastos->hospedaje ?? 0),
                    floatval($gastos->pasajes ?? 0),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de gastos extras: ' . $e->getMessage(), [
                'proyecto_id' => $proyecto,
            ]);
            return response()->json(['error' => 'Error al obtener datos de gastos extras: ' . $e->getMessage()], 500);
        }
    }

    public function getEgresos($id)
    {
        $proyecto = Proyectos::with(['materiales', 'planilla', 'gastosExtra', 'egresos', 'servicios'])
            ->findOrFail($id);

        $egreso = $proyecto->egresos()->orderBy('id_egreso', 'desc')->first();

        return view('egresos', compact('proyecto', 'egreso'));
    }

    public function calculateAndSaveEgresos(Request $request, $id)
    {
        try {
            $sctr = (float) $request->input('sctr_monto', $request->input('scr', 0));
            $gastos_admin_mensual = (float) $request->input('gastos_admin_mensual', 800);

            \Log::info("Llamando SP sp_calcular_egresos", [
                'proyecto' => $id,
                'sctr' => $sctr,
                'gastos_admin_mensual' => $gastos_admin_mensual
            ]);

            $result = DB::select('CALL sp_calcular_egresos(?, ?, ?)', [
                $id,
                $sctr,
                $gastos_admin_mensual
            ]);

            $data = $result[0] ?? null;

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'No se obtuvo resultado del SP.'], 500);
            }

            // === FALLBACK: Servicios (por si SP falla) ===
            $serviciosFix = DB::table('servicios')
                ->where('id_proyecto', $id)
                ->whereNull('deleted_at')
                ->sum('monto') ?? 0;

            if ((float)($data->servicios ?? 0) === 0.0 && $serviciosFix > 0) {
                $data->servicios = $serviciosFix;

                // Actualizar en BD
                $ultimo = DB::table('egresos')
                    ->where('id_proyecto', $id)
                    ->orderByDesc('id_egreso')
                    ->first();

                if ($ultimo) {
                    DB::table('egresos')
                        ->where('id_egreso', $ultimo->id_egreso)
                        ->update([
                            'servicios' => $serviciosFix,
                            'updated_at' => now(),
                        ]);
                }
            }

            // === FALLBACK: Planilla (si necesitas) ===
            $planillaFix = DB::table('planilla')
                ->where('id_proyecto', $id)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador), 0) AS total')
                ->first()->total ?? 0;

            if ((float)($data->planilla ?? 0) === 0.0 && $planillaFix > 0) {
                $data->planilla = $planillaFix;
                $ultimo = DB::table('egresos')->where('id_proyecto', $id)->orderByDesc('id_egreso')->first();
                if ($ultimo) {
                    DB::table('egresos')
                        ->where('id_egreso', $ultimo->id_egreso)
                        ->update(['planilla' => $planillaFix, 'updated_at' => now()]);
                }
            }

            // === FALLBACK: Gastos Extra ===
            $gastosExtraFix = DB::table('gastos_extra')
                ->where('id_proyecto', $id)
                ->whereNull('deleted_at')
                ->sum('gasto_total') ?? 0;

            if ((float)($data->gastos_extra ?? 0) === 0.0 && $gastosExtraFix > 0) {
                $data->gastos_extra = $gastosExtraFix;
                $ultimo = DB::table('egresos')->where('id_proyecto', $id)->orderByDesc('id_egreso')->first();
                if ($ultimo) {
                    DB::table('egresos')
                        ->where('id_egreso', $ultimo->id_egreso)
                        ->update(['gastos_extra' => $gastosExtraFix, 'updated_at' => now()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Egresos calculados y guardados correctamente',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en calculateAndSaveEgresos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la solicitud',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function finalizar($id)
    {
        // 1. Marcar fecha fin real en fechapr
        \DB::table('fechapr')
            ->where('proyecto_id', $id)
            ->update(['fecha_fin_true' => now()]);

        // 2. Cambiar estado de planilla a LIQUIDADO
        \DB::table('planilla')
            ->where('id_proyecto', $id)
            ->update(['estado' => 'LIQUIDADO']);

        // 3. (Opcional) Bloquear inserciones: se hace validando en controladores
        // Antes de crear materiales, gastos extra, etc., verificamos que el proyecto esté finalizado
        // Ejemplo:
        // if ($proyecto->fechapr->fecha_fin_true !== null) return redirect()->back()->with('error', 'Proyecto finalizado. No se pueden agregar más datos.');

        return redirect()->back()->with('success', 'Proyecto finalizado correctamente.');
    }

    // Mostrar actividades
    public function actividades($proyectoId)
    {
        $proyecto = Proyectos::findOrFail($proyectoId);
        $actividades = ActividadProyecto::where('proyecto_id', $proyectoId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.proyectos.actividades', compact('proyecto', 'actividades'));
    }

    // Guardar nueva actividad
    public function storeActividad(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_actividad' => 'required|date',
            'imagen' => 'nullable|image|max:2048', // Ajusta según tus necesidades
        ]);

        $proyecto = Proyectos::findOrFail($id);
        $actividad = new ActividadProyecto();
        $actividad->proyecto_id = $id;
        $actividad->nombre = $request->nombre;
        $actividad->descripcion = $request->descripcion;
        $actividad->fecha_actividad = $request->fecha_actividad;

        if ($request->hasFile('imagen')) {
            $actividad->imagen_url = $request->file('imagen')->store('actividades', 'public');
        }

        $actividad->save();

        return redirect()->back()->with('success', 'Actividad agregada correctamente.');
    }

    // Eliminar actividad
    public function destroyActividad($proyectoId, $id)
    {
        $actividad = Actividad::findOrFail($id);
        if ($actividad->imagen) {
            Storage::disk('public')->delete('actividades/' . $actividad->imagen);
        }
        $actividad->delete();

        return redirect()->back()->with('success', 'Actividad eliminada.');
    }


    public function exportacionGeneral()
    {
        $proyectos = Proyectos::paginate(10); // Ajusta la paginación según necesites
        return view('admin.proyectos.exportacion.exportacion-general', compact('proyectos'));
    }
    
    public function exportPdf(Proyectos $proyecto)
    {
        // Carga todas las relaciones necesarias, incluyendo 'actividades' ordenadas por fecha descendente
        $proyecto->load([
            'montopr' => fn($q) => $q->select('proyecto_id', 'monto_inicial'),
            'fechapr' => fn($q) => $q->select('proyecto_id', 'fecha_inicio', 'fecha_fin_aprox'),
            'planilla.trabajador' => fn($q) => $q->select('id_trabajadores', 'nombre_trab', 'apellido_trab', 'dni_trab'),
            'gastosExtra' => fn($q) => $q->select('id_proyecto', 'alimentacion_general', 'hospedaje', 'pasajes', 'created_at'),
            'materiales.proveedor' => fn($q) => $q->select('id_proveedor', 'nombre_prov'),
            'egresos' => fn($q) => $q->select('id_proyecto', 'materiales', 'planilla', 'scr', 'gastos_administrativos', 'gastos_extra'),
            'actividades' => fn($q) => $q->orderByDesc('fecha_actividad') // Carga actividades ordenadas
        ]);

        // Tabla de proveedores con suma de montos (como en tu código original)
        $proveedores = $proyecto->materiales->groupBy('id_proveedor')->map(function ($group) {
            return [
                'nombre' => $group->first()->proveedor->nombre_prov,
                'total_monto' => $group->sum('monto_mat')
            ];
        })->values();

        // Genera el PDF
        $pdf = Pdf::loadView('admin.proyectos.exportacion.pdf-export', compact('proyecto', 'proveedores'));

        // Opciones de DomPDF
        $pdf->setOption('isRemoteEnabled', true); // Ya lo cambiaste, pero asegúrate de que esté aquí para imágenes remotas (Imgur)
        $pdf->setOption('footer-html', view('admin.proyectos.exportacion.footer')->render()); // Agrega el footer para paginación
        $pdf->setOption('footer-spacing', 10); // Espacio entre contenido y footer (opcional, ajusta si necesitas)
        $pdf->setPaper('A4', 'portrait'); // Tamaño de página (puedes cambiar a 'letter' si prefieres)

        // Descarga el PDF
        return $pdf->download('Proyecto_' . $proyecto->nombre_proyecto . '.pdf');
    }

    /**
     * API: Presupuesto por apartados (Materiales, Personal, Servicios)
     * GET /api/proyectos/{proyecto}/budgets
     */
    public function getBudgetSummary(Proyectos $proyecto)
    {
        $apartado = DB::table('montos_apartados')->where('id_proyecto', $proyecto->id_proyecto)->first();

        $materialesGastado = (float) DB::table('materiales')
            ->where('id_proyecto', $proyecto->id_proyecto)
            ->whereNull('deleted_at')
            ->sum('monto_mat');

        $planillaGastado = (float) DB::table('planilla')
            ->where('id_proyecto', $proyecto->id_proyecto)
            ->selectRaw('COALESCE(SUM(pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador), 0)')
            ->value(DB::raw('1')) ?? 0;

        $gExtra = (float) DB::table('gastos_extra')
            ->where('id_proyecto', $proyecto->id_proyecto)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(alimentacion_general + hospedaje + pasajes), 0)')
            ->value(DB::raw('1')) ?? 0;

        $personalGastado = $planillaGastado + $gExtra;

        $serviciosGastado = (float) DB::table('servicios')
            ->where('id_proyecto', $proyecto->id_proyecto)
            ->whereNull('deleted_at')
            ->sum('monto');

        return response()->json([
            'materials' => [
                'assigned' => (float)($apartado->monto_material ?? 0),
                'spent' => $materialesGastado,
                'remaining' => max(0, (float)($apartado->monto_material ?? 0) - $materialesGastado),
            ],
            'personal' => [
                'assigned' => (float)($apartado->monto_operativos ?? 0),
                'spent' => $personalGastado,
                'remaining' => max(0, (float)($apartado->monto_operativos ?? 0) - $personalGastado),
            ],
            'services' => [
                'assigned' => (float)($apartado->monto_servicios ?? 0),
                'spent' => $serviciosGastado,
                'remaining' => max(0, (float)($apartado->monto_servicios ?? 0) - $serviciosGastado),
            ],
        ]);
    }
}