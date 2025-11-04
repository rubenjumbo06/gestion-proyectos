<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AllowedUserController;
use App\Http\Controllers\ProveedorController; 
use App\Http\Controllers\DepartamentoController; 
use App\Http\Controllers\ProyectosController; 
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\TrabajadoresController;
use App\Http\Controllers\ActividadesController;
use App\Http\Controllers\PlanillaController;

// Ruta para la página de login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Rutas de autenticación con Google
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Rutas de registro de usuario 
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// Ruta para cerrar sesión
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Ruta raíz (redirige al primer proyecto)
Route::get('/', [ProyectosController::class, 'dashboardHome'])
    ->name('dashboard')
    ->middleware('auth');

Route::resource('servicios', ServicioController::class);

// Ruta para ver un proyecto en específico
Route::get('/dashboard/{id}', [ProyectosController::class, 'dashboard'])
    ->name('dashboard.proyecto')
    ->middleware('auth');

// Rutas protegidas (solo para usuarios autenticados)
Route::middleware(['auth'])->group(function () {
    // Ruta para el perfil del usuario
    Route::get('/perfiles', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfiles', [ProfileController::class, 'update'])->name('profile.update');

    // Ruta para Exportación General
    Route::get('/admin/exportacion-general', [ProyectosController::class, 'exportacionGeneral'])
    ->name('exportacion.general')
    ->middleware('auth');

    // Ruta para exportar PDF de un proyecto
    Route::get('/proyectos/{proyecto}/export-pdf', [ProyectosController::class, 'exportPdf'])
    ->name('proyectos.exportPdf')
    ->middleware('auth');
    
    // Ruta para cambiar de contraseña
    Route::get('/change-password', [LoginController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('password.update');

    Route::prefix('admin')->group(function () {
        Route::get('/trabajadores/list', [TrabajadoresController::class, 'list'])->name('trabajadores.list');
        // Nuevas rutas para exportación
        Route::get('/trabajadores/export/excel', [TrabajadoresController::class, 'exportExcel'])->name('trabajadores.export.excel');
        Route::get('/trabajadores/export/pdf', [TrabajadoresController::class, 'exportPdf'])->name('trabajadores.export.pdf');
        Route::get('/departamentos/export/excel', [DepartamentoController::class, 'exportExcel'])->name('departamentos.export.excel');
        Route::get('/departamentos/export/pdf', [DepartamentoController::class, 'exportPdf'])->name('departamentos.export.pdf');
         Route::get('/proveedores/export/excel', [ProveedorController::class, 'exportExcel'])->name('proveedores.export.excel');
        Route::get('/proveedores/export/pdf', [ProveedorController::class, 'exportPdf'])->name('proveedores.export.pdf');
        //Otras rutas
        Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);
        Route::resource('departamentos', DepartamentoController::class);
        Route::resource('proyectos', ProyectosController::class)->parameters(['proyectos' => 'proyecto']);
        Route::post('/proyectos/{proyecto}/add-planilla', [ProyectosController::class, 'addPlanilla'])->name('proyectos.addPlanilla');
        Route::delete('/proyectos/{proyecto}/remove-planilla/{planilla}', [ProyectosController::class, 'removePlanilla'])->name('proyectos.removePlanilla');
        Route::get('/proyectos/{proyecto}/planilla/{planilla}', [ProyectosController::class, 'getPlanilla'])->name('proyectos.getPlanilla');
        Route::get('/admin/proyectos/{proyecto}/data-sueldos', [ProyectosController::class, 'getDataSueldos'])->name('proyectos.getDataSueldos');
        Route::post('/proyectos/{proyecto}/agregar-sueldos', [ProyectosController::class, 'agregarSueldos'])->name('proyectos.agregarSueldos');
        // Nuevo: asistencia y pago_dia
        Route::post('/proyectos/{proyecto}/planilla/{planilla}/marcar-asistencia', [ProyectosController::class, 'marcarAsistencia'])->name('proyectos.marcarAsistencia');
        Route::post('/proyectos/{proyecto}/planilla/{planilla}/pago-dia', [ProyectosController::class, 'setPagoDia'])->name('proyectos.setPagoDia');
        Route::get('/proyectos/{proyecto}/asistencia/status', [ProyectosController::class, 'asistenciaStatus'])->name('proyectos.asistenciaStatus');
        Route::post('/proyectos/{proyecto}/planilla/{planilla}/update-gastos', [ProyectosController::class, 'updatePlanillaGastos'])->name('proyectos.updatePlanillaGastos');
        Route::post('/proyectos/{proyecto}/planilla/{planilla}/add-gastos', [ProyectosController::class, 'addPlanillaGastos'])->name('proyectos.addPlanillaGastos');
        // Rutas para materiales
        Route::post('/proyectos/{proyecto}/materiales', [ProyectosController::class, 'storeMaterial'])->name('proyectos.materiales.store');
        Route::put('/proyectos/{proyecto}/materiales/{id}', [ProyectosController::class, 'updateMaterial'])->name('proyectos.materiales.update');
        Route::delete('/proyectos/{proyecto}/materiales/{id}', [ProyectosController::class, 'destroyMaterial'])->name('proyectos.materiales.destroy');
        Route::get('/proyectos/{proyecto}/materiales/{id}', [ProyectosController::class, 'getMaterial'])->name('proyectos.materiales.show');
        // Rutas para gastos extras
        Route::post('/proyectos/{proyecto}/gastos-extra', [ProyectosController::class, 'storeGastoExtra'])->name('proyectos.gastos-extra.store');
        Route::put('/proyectos/{proyecto}/gastos-extra/{id}', [ProyectosController::class, 'updateGastoExtra'])->name('proyectos.gastos-extra.update');
        Route::delete('/proyectos/{proyecto}/gastos-extra/{id}', [ProyectosController::class, 'destroyGastoExtra'])->name('proyectos.gastos-extra.destroy');
        Route::get('/proyectos/{proyecto}/gastos-extra/{id}', [ProyectosController::class, 'getGastoExtra'])->name('proyectos.gastos-extra.show');
        Route::get('/proyectos/{proyecto}/gastos-extra-data', [ProyectosController::class, 'getGastosExtraData'])->name('proyectos.gastos-extra.data');

        Route::get('/proyectos/{id}/egresos', [ProyectosController::class, 'getEgresos'])->name('proyectos.getEgresos');
        Route::post('/proyectos/{id}/calculate-egresos', [ProyectosController::class, 'calculateAndSaveEgresos'])->name('proyectos.calculate.egresos');
        Route::post('/proyectos/{id}/finalizar', [ProyectosController::class, 'finalizar'])->name('proyectos.finalizar');

        // Rutas para actividades
        Route::get('/proyectos/{proyecto}/actividades', [ActividadesController::class, 'index'])->name('proyectos.actividades.index');
        Route::post('/proyectos/{proyecto}/actividades', [ActividadesController::class, 'store'])->name('proyectos.actividades.store');
        Route::put('/proyectos/{proyecto}/actividades/{actividad}', [ActividadesController::class, 'update'])->name('proyectos.actividades.update');
        Route::delete('/proyectos/{proyecto}/actividades/{actividad}', [ActividadesController::class, 'destroy'])->name('proyectos.actividades.destroy');
        Route::get('/reporte_actividades', [ActividadesController::class, 'reporte'])->name('reporte_actividades');
        Route::post('/reporte_actividades', [ActividadesController::class, 'store'])->name('reporte_actividades.store');
        Route::put('/reporte_actividades/{actividad}', [ActividadesController::class, 'update'])->name('reporte_actividades.update');
        Route::delete('/reporte_actividades/{actividad}', [ActividadesController::class, 'destroy'])->name('reporte_actividades.destroy');
        
        Route::resource('trabajadores', TrabajadoresController::class)->parameters(['trabajadores' => 'trabajador']);
        Route::resource('planillas', PlanillaController::class)->parameters(['planillas' => 'planilla']);
    });

    // Nuevos endpoints API para los gráficos
    Route::prefix('api/proyectos/{proyecto}')->group(function () {
        Route::get('materiales', [ProyectosController::class, 'getMaterialesData'])->name('proyectos.materiales');
        Route::get('asistencia', [ProyectosController::class, 'getAsistenciaData'])->name('proyectos.asistencia');
        Route::get('gastos', [ProyectosController::class, 'getGastosData'])->name('proyectos.gastos');
        Route::get('egresos', [ProyectosController::class, 'getEgresosData'])->name('proyectos.egresos');
        Route::get('balance', [ProyectosController::class, 'getBalanceData']);
        Route::get('calendar', [ProyectosController::class, 'getCalendarData']);
        Route::get('calendar/day/{date}', [ProyectosController::class, 'getCalendarDayDetails']);
        Route::get('budgets', [ProyectosController::class, 'getBudgetSummary'])->name('proyectos.budgets');
    });

    Route::get('/user-activities', function (Illuminate\Http\Request $request) {
        $offset = $request->input('offset', 0);
        $activities = auth()->user()->activities()->latest()->skip($offset)->take(4)->get();
        $formattedActivities = $activities->map(function ($activity) {
            return [
                'description' => $activity->description,
                'created_at' => $activity->created_at->format('d/m/Y H:i')
            ];
        });
        return response()->json(['activities' => $formattedActivities]);
    })->middleware('auth');
});

// Rutas para Super Admin
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/allowed_users', [AllowedUserController::class, 'index'])->name('allowed_users.index');
    Route::post('/allowed_users', [AllowedUserController::class, 'store'])->name('allowed_users.store');
    Route::delete('/allowed_users/{allowedUser}', [AllowedUserController::class, 'destroy'])->name('allowed_users.destroy');
    Route::patch('/allowed_users/{allowedUser}/toggle', [AllowedUserController::class, 'toggle'])->name('allowed_users.toggle');
    Route::put('/allowed_users/{allowedUser}', [AllowedUserController::class, 'update'])->name('allowed_users.update');
    Route::put('allowed_users/{user}/permissions', [AllowedUserController::class, 'updatePermissions'])->name('allowed_users.permissions.update');
});