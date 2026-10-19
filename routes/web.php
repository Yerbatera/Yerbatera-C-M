<?php

use App\Http\Controllers\BalanzaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginRolController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// --- LOGIN ---
Route::get('/', function () {
    return view('login-roles');
})->name('login');
Route::post('/login', [LoginRolController::class, 'loginRapido'])->name('login.rapido');

// --- RUTAS PROTEGIDAS CON AUTH ---
Route::middleware(['auth'])->group(function () {

    // Dashboards (Las pantallas de los botones verdes)
    Route::get('/gerente', [DashboardController::class, 'gerente'])->middleware('rol:gerente')->name('gerente');
    Route::get('/administracion', [DashboardController::class, 'admin'])->middleware('rol:administracion');
    Route::get('/operario', [DashboardController::class, 'operario'])->middleware('rol:operario');
    Route::get('/yerbatero', [DashboardController::class, 'yerbatero'])->middleware('rol:yerbatero');

    Route::get('/monitoreo', [TareaController::class, 'index'])->name('monitoreo');

    // Módulos compartidos (Adentro del controlador se filtra por rol)
    Route::resource('balanza', BalanzaController::class);
    Route::resource('tareas', TareaController::class)->only(['index']);

    Route::middleware('rol:administracion,gerente')->group(function () {
        Route::get('/turnos', [TurnoController::class, 'index'])->name('turnos.index');
        Route::post('/turnos', [TurnoController::class, 'store'])->name('turnos.store');
        Route::get('/tareas/asignar', [TareaController::class, 'create'])->name('tareas.asignar');
        Route::post('/tareas/asignar', [TareaController::class, 'store'])->name('tareas.asignar.store');
    });

    Route::resource('procesos', ProcesoController::class);
    Route::resource('ventas', VentaController::class);
});
