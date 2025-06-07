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
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CalendarioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    });

    Route::resource('habitaciones', HabitacionController::class);
    Route::resource('reservas', ReservaController::class)->middleware('verificar.caja')->only(['store', 'update']);
    Route::resource('reservas', ReservaController::class)->except(['store', 'update']);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('niveles', NivelController::class);
    Route::resource('usuarios', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('clientes', ClienteController::class);

    // Rutas para búsqueda de clientes
    Route::get('api/clientes/buscar', [ClienteController::class, 'buscar'])->name('api.clientes.buscar');
    Route::get('api/clientes/buscar-por-dpi/{dpi}', [ClienteController::class, 'buscarPorDpi'])->name('api.clientes.buscarPorDpi');
    Route::get('api/clientes/buscar-por-nit/{nit}', [ClienteController::class, 'buscarPorNit'])->name('api.clientes.buscarPorNit');

    // Rutas de reservas
    Route::get('/habitaciones/{habitacione}/checkin', [ReservaController::class, 'checkin'])->name('habitaciones.checkin')->middleware('verificar.caja');
    // Route::post('/habitaciones/{habitacione}/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('reservas/{reserva}/checkout', [ReservaController::class, 'checkout'])->name('reservas.checkout');
    Route::post('reservas/{reserva}/checkout', [ReservaController::class, 'storeCheckout'])->name('reservas.checkout.store');

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
        Route::post('cajas/{caja}/movimientos', [CajaController::class, 'storeMovimiento'])->name('cajas.movimientos.store')->middleware('verificar.caja');
        Route::get('cajas/{caja}/arqueo', [CajaController::class, 'arqueo'])->name('cajas.arqueo');
        Route::post('cajas/{caja}/arqueo', [CajaController::class, 'realizarArqueo'])->name('cajas.arqueo.store')->middleware('verificar.caja');

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

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Rutas de reportes
    Route::get('/reportes/ingresos', [\App\Http\Controllers\ReporteController::class, 'ingresos'])->name('reportes.ingresos');
});

Auth::routes(['register' => false]);

Route::get('/logout-now', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.now');

// Redirigir /home a /dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Ruta para el calendario de reservas
Route::get('/reservas/calendario', [CalendarioController::class, 'index'])->name('api.reservas.calendario');

// Ruta para cambiar el estado de la habitación
Route::post('/habitaciones/{habitacion}/cambiar-estado', [HabitacionController::class, 'cambiarEstado'])->name('habitaciones.cambiar-estado');
