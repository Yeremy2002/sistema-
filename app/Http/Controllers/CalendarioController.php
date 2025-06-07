<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Reparacion;
use App\Models\Limpieza;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarioController extends Controller
{
  public function index()
  {
    // Reservas
    $reservas = Reserva::with(['habitacion', 'cliente'])
      ->whereIn('estado', ['Check-in', 'Pendiente'])
      ->get()
      ->map(function ($reserva) {
        $color = $reserva->estado === 'Check-in' ? '#dc3545' : '#ffc107';
        return [
          'id' => 'reserva_' . $reserva->id,
          'title' => "{$reserva->habitacion->numero}",
          'start' => $reserva->fecha_entrada,
          'end' => $reserva->fecha_salida,
          'backgroundColor' => $color,
          'borderColor' => $color,
          'extendedProps' => [
            'tipo' => 'reserva',
            'habitacion_numero' => $reserva->habitacion->numero,
            'cliente_nombre' => $reserva->cliente->nombre,
            'precio' => $reserva->precio,
            'total' => $reserva->total,
            'estado' => $reserva->estado
          ]
        ];
      });

    // Mantenimientos (Reparaciones)
    $mantenimientos = Reparacion::with('habitacion')
      ->whereIn('estado', ['pendiente', 'en_proceso'])
      ->get()
      ->map(function ($reparacion) {
        $color = '#6c757d'; // gris
        return [
          'id' => 'mantenimiento_' . $reparacion->id,
          'title' => "{$reparacion->habitacion->numero}",
          'start' => $reparacion->fecha,
          'end' => $reparacion->fecha_fin ?? $reparacion->fecha,
          'backgroundColor' => $color,
          'borderColor' => $color,
          'extendedProps' => [
            'tipo' => 'mantenimiento',
            'habitacion_numero' => $reparacion->habitacion->numero,
            'estado' => $reparacion->estado
          ]
        ];
      });

    // Limpiezas
    $limpiezas = Limpieza::with('habitacion')
      ->whereIn('estado', ['pendiente', 'en_proceso'])
      ->get()
      ->map(function ($limpieza) {
        $color = '#ffc107'; // amarillo
        return [
          'id' => 'limpieza_' . $limpieza->id,
          'title' => "{$limpieza->habitacion->numero}",
          'start' => $limpieza->fecha,
          'end' => $limpieza->fecha,
          'backgroundColor' => $color,
          'borderColor' => $color,
          'extendedProps' => [
            'tipo' => 'limpieza',
            'habitacion_numero' => $limpieza->habitacion->numero,
            'estado' => $limpieza->estado
          ]
        ];
      });

    return response()->json($reservas->concat($mantenimientos)->concat($limpiezas));
  }
}
