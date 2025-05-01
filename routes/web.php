<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\HotelController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    });

    Route::resource('habitaciones', HabitacionController::class);
    Route::resource('reservas', ReservaController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('niveles', NivelController::class);
    Route::resource('usuarios', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('clientes', ClienteController::class);

    // Ruta para búsqueda de clientes
    Route::get('api/clientes/buscar', [ClienteController::class, 'buscar'])->name('api.clientes.buscar');

    // Rutas de reservas
    Route::get('/habitaciones/{habitacione}/checkin', [ReservaController::class, 'checkin'])->name('reservas.checkin');
    // Route::post('/habitaciones/{habitacione}/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/reservas/{reserva}/checkout', [ReservaController::class, 'checkout'])->name('reservas.checkout');

    // Rutas de Mantenimiento
    Route::prefix('mantenimiento')->name('mantenimiento.')->group(function () {
        // Rutas de Limpieza
        Route::get('/limpieza', [MantenimientoController::class, 'indexLimpieza'])->name('limpieza.index');
        Route::get('/limpieza/create', [MantenimientoController::class, 'createLimpieza'])->name('limpieza.create');
        Route::post('/limpieza', [MantenimientoController::class, 'storeLimpieza'])->name('limpieza.store');
        Route::put('/limpieza/{limpieza}', [MantenimientoController::class, 'updateLimpieza'])->name('limpieza.update');

        // Rutas de Reparación
        Route::get('/reparaciones', [MantenimientoController::class, 'indexReparacion'])->name('reparacion.index');
        Route::get('/reparaciones/create', [MantenimientoController::class, 'createReparacion'])->name('reparacion.create');
        Route::post('/reparaciones', [MantenimientoController::class, 'storeReparacion'])->name('reparacion.store');
        Route::put('/reparaciones/{reparacion}', [MantenimientoController::class, 'updateReparacion'])->name('reparacion.update');
    });

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // Rutas de cajas
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::resource('cajas', CajaController::class)->except(['destroy']);
        Route::get('cajas/{caja}/movimientos', [CajaController::class, 'movimientos'])->name('cajas.movimientos');
        Route::get('cajas/{caja}/movimientos/create', [CajaController::class, 'createMovimiento'])->name('cajas.movimientos.create');
        Route::post('cajas/{caja}/movimientos', [CajaController::class, 'storeMovimiento'])->name('cajas.movimientos.store');
        Route::get('cajas/{caja}/arqueo', [CajaController::class, 'arqueo'])->name('cajas.arqueo');
        Route::post('cajas/{caja}/arqueo', [CajaController::class, 'realizarArqueo'])->name('cajas.arqueo.store');

        // Rutas para asignación de cajas
        Route::get('cajas/asignar/usuarios', [CajaController::class, 'asignar'])->name('cajas.asignar');
        Route::post('cajas/asignar/usuarios', [CajaController::class, 'asignarStore'])->name('cajas.asignar.store');
    });

    Route::post('/usuarios/{usuario}/asignar-caja', [UserController::class, 'asignarCaja'])
        ->name('usuarios.asignar-caja')
        ->middleware('can:asignar caja');

    // Rutas de configuración del hotel
    Route::get('configuracion/hotel', [HotelController::class, 'edit'])->name('configuracion.hotel');
    Route::put('configuracion/hotel', [HotelController::class, 'update'])->name('configuracion.hotel.update');
});

Auth::routes();

// Redirigir /home a /dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
});
