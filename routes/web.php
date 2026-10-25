<?php

use App\Http\Controllers\BalanzaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GerenciaController;
use App\Http\Controllers\LoginRolController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// --- LOGIN ---
Route::get('/', function () {
    return view('login-roles');
})->name('login');
Route::post('/login', [LoginRolController::class, 'loginRapido'])->name('login.rapido');
Route::get('/producer/search', [ProducerController::class, 'search'])->name('producer.search');

// -- Ruta Libre --
Route::resource('ventas', VentaController::class);

// --- RUTAS PROTEGIDAS CON AUTH ---
Route::middleware(['auth'])->group(function () {

    // Dashboards
    Route::get('/gerente', [DashboardController::class, 'gerente'])->middleware('rol:gerente')->name('gerente');
    Route::get('/administracion', [DashboardController::class, 'admin'])->middleware('rol:administracion');
    Route::get('/operario', [DashboardController::class, 'operario'])->middleware('rol:operario');
    Route::get('/yerbatero', [DashboardController::class, 'yerbatero'])->middleware('rol:yerbatero');

    Route::middleware('rol:gerente')->prefix('gerencia')->name('gerencia.')->group(function () {
        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas');
        Route::get('/procesos', [GerenciaController::class, 'procesos'])->name('procesos');
    });

    Route::get('/monitoreo', [TareaController::class, 'index'])->name('monitoreo');

    // Módulos compartidos
    Route::post('/balanza/proveedores', [BalanzaController::class, 'storeProveedor'])->name('balanza.proveedores.store');
    Route::put('/balanza/proveedores/{proveedor}', [BalanzaController::class, 'updateProveedor'])->name('balanza.proveedores.update');
    Route::resource('balanza', BalanzaController::class);
    Route::resource('tareas', TareaController::class)->only(['index']);
    Route::patch('/tareas/{tarea}/estado', [TareaController::class, 'actualizarEstado'])->name('tareas.estado');

    Route::middleware('rol:administracion,gerente')->group(function () {
        Route::get('/personal', [TurnoController::class, 'index'])->name('personal.index');
        Route::post('/personal/empleados', [TurnoController::class, 'storeEmpleado'])->name('personal.empleados.store');
        Route::put('/personal/empleados/{empleado}', [TurnoController::class, 'updateEmpleado'])->name('personal.empleados.update');
        Route::get('/turnos', [TurnoController::class, 'index'])->name('turnos.index');
        Route::post('/turnos', [TurnoController::class, 'store'])->name('turnos.store');
        Route::post('/turnos/semanal', [TurnoController::class, 'storeSemanal'])->name('turnos.semanal.store');
        Route::get('/tareas/asignar', [TareaController::class, 'create'])->name('tareas.asignar');
        Route::post('/tareas/asignar', [TareaController::class, 'store'])->name('tareas.asignar.store');
    });

    Route::resource('procesos', ProcesoController::class);
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store']);
});
