<?php

use App\Http\Controllers\BalanzaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginRolController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProducerController;

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

    // Dashboards (Las pantallas de los botones verdes)
    Route::get('/gerente', [DashboardController::class, 'gerente'])->middleware('rol:gerente')->name('gerente');
    Route::get('/administracion', [DashboardController::class, 'admin'])->middleware('rol:administracion');
    Route::get('/operario', [DashboardController::class, 'operario'])->middleware('rol:operario');
    Route::get('/yerbatero', [DashboardController::class, 'yerbatero'])->middleware('rol:yerbatero');

    Route::get('/monitoreo', [TareaController::class, 'index'])->name('monitoreo');

    // Módulos compartidos (Adentro del controlador se filtra por rol)
    Route::resource('balanza', BalanzaController::class);
    Route::resource('tareas', TareaController::class);
    Route::resource('procesos', ProcesoController::class);
    
});
