<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoCaja;
use App\Models\Habitacion;
use App\Models\Reserva;
use Carbon\Carbon;

class ReporteController extends Controller
{
  public function ocupacion(Request $request)
  {
    // Definir fechas por defecto
    $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
    $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
    
    // Obtener todas las habitaciones
    $habitaciones = Habitacion::with(['categoria', 'nivel'])->get();
    $totalHabitaciones = $habitaciones->count();
    
    // Obtener reservas en el período especificado
    $reservas = Reserva::with(['habitacion', 'cliente'])
      ->whereBetween('fecha_entrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
      ->orWhereBetween('fecha_salida', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
      ->orWhere(function($query) use ($fechaInicio, $fechaFin) {
        $query->where('fecha_entrada', '<=', $fechaInicio . ' 00:00:00')
              ->where('fecha_salida', '>=', $fechaFin . ' 23:59:59');
      })
      ->orderBy('fecha_entrada')
      ->get();
    
    // Calcular estadísticas
    $reservasActivas = $reservas->where('estado', '!=', 'cancelada')->count();
    $porcentajeOcupacion = $totalHabitaciones > 0 ? round(($reservasActivas / $totalHabitaciones) * 100, 2) : 0;
    
    // Estadísticas por estado
    $estadisticas = [
      'ocupadas' => $habitaciones->where('estado', 'ocupada')->count(),
      'disponibles' => $habitaciones->where('estado', 'disponible')->count(),
      'limpieza' => $habitaciones->where('estado', 'limpieza')->count(),
      'mantenimiento' => $habitaciones->where('estado', 'mantenimiento')->count(),
      'total' => $totalHabitaciones,
      'porcentaje_ocupacion' => $porcentajeOcupacion
    ];
    
    return view('reportes.ocupacion', compact(
      'habitaciones', 'reservas', 'estadisticas', 
      'fechaInicio', 'fechaFin', 'totalHabitaciones'
    ));
  }

  public function ingresos()
  {
    $totalIngresos = MovimientoCaja::where('tipo', 'ingreso')->sum('monto');
    $totalEgresos = MovimientoCaja::where('tipo', 'egreso')->sum('monto');
    $movimientos = MovimientoCaja::with(['caja', 'user'])
      ->orderByDesc('created_at')
      ->limit(50)
      ->get();
    return view('reportes.ingresos', compact('totalIngresos', 'totalEgresos', 'movimientos'));
  }
}
