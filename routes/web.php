<?php

use App\Http\Controllers\BalanzaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GerenciaController;
use App\Http\Controllers\LoginRolController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\TareaController;
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
    Route::get('/gerente', [DashboardController::class, 'gerente'])->middleware('rol:gerente');
    Route::get('/administracion', [DashboardController::class, 'admin'])->middleware('rol:administracion');
    Route::get('/operario', [DashboardController::class, 'operario'])->middleware('rol:operario');
    Route::get('/yerbatero', [DashboardController::class, 'yerbatero'])->middleware('rol:yerbatero');

    Route::middleware('rol:gerente')->prefix('gerencia')->name('gerencia.')->group(function () {
        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas');
        Route::get('/procesos', [GerenciaController::class, 'procesos'])->name('procesos');
    });

    // Módulos compartidos (Adentro del controlador se filtra por rol)
    Route::resource('balanza', BalanzaController::class);
    Route::resource('tareas', TareaController::class);
    Route::resource('procesos', ProcesoController::class);
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store']);
});
