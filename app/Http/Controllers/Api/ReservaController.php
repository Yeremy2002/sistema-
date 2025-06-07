<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ReservaApiController extends Controller
{
  public function calendario()
  {
    $reservas = Reserva::with(['habitacion', 'cliente'])
      ->where(function ($query) {
        $query->where('estado', 'Check-in')
          ->orWhere('estado', 'Pendiente');
      })
      ->get()
      ->map(function ($reserva) {
        $color = $reserva->estado === 'Check-in' ? '#dc3545' : '#ffc107';
        return [
          'id' => $reserva->id,
          'title' => "Habitación {$reserva->habitacion->numero} - {$reserva->cliente->nombre}",
          'start' => $reserva->fecha_entrada->format('Y-m-d\TH:i:s'),
          'end' => $reserva->fecha_salida->format('Y-m-d\TH:i:s'),
          'backgroundColor' => $color,
          'borderColor' => $color,
          'extendedProps' => [
            'estado' => $reserva->estado,
            'habitacion_id' => $reserva->habitacion_id,
            'cliente_id' => $reserva->cliente_id,
            'habitacion_numero' => $reserva->habitacion->numero,
            'cliente_nombre' => $reserva->cliente->nombre,
            'precio' => $reserva->habitacion->precio,
            'adelanto' => $reserva->adelanto,
            'total' => $reserva->total
          ]
        ];
      });

    return response()->json($reservas);
  }

  public function disponibilidad(Request $request)
  {
    $request->validate([
      'fecha_entrada' => 'required|date',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'categoria_id' => 'nullable|exists:categorias,id'
    ]);

    $fechaEntrada = Carbon::parse($request->fecha_entrada);
    $fechaSalida = Carbon::parse($request->fecha_salida);

    // Obtener habitaciones disponibles
    $habitacionesDisponibles = \App\Models\Habitacion::where('estado', 'Disponible')
      ->when($request->categoria_id, function ($query) use ($request) {
        return $query->where('categoria_id', $request->categoria_id);
      })
      ->whereDoesntHave('reservas', function ($query) use ($fechaEntrada, $fechaSalida) {
        $query->where(function ($q) use ($fechaEntrada, $fechaSalida) {
          $q->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
            ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
            ->orWhere(function ($q) use ($fechaEntrada, $fechaSalida) {
              $q->where('fecha_entrada', '<=', $fechaEntrada)
                ->where('fecha_salida', '>=', $fechaSalida);
            });
        })
          ->whereIn('estado', ['Check-in', 'Pendiente']);
      })
      ->with(['categoria', 'nivel', 'imagenPrincipal'])
      ->get()
      ->map(function ($habitacion) {
        return [
          'id' => $habitacion->id,
          'numero' => $habitacion->numero,
          'categoria' => $habitacion->categoria->nombre,
          'nivel' => $habitacion->nivel->nombre,
          'precio' => $habitacion->precio,
          'descripcion' => $habitacion->descripcion,
          'caracteristicas' => $habitacion->caracteristicas,
          'imagen' => $habitacion->imagenPrincipal ? Storage::url($habitacion->imagenPrincipal->ruta) : null
        ];
      });

    return response()->json([
      'disponibles' => $habitacionesDisponibles,
      'total' => $habitacionesDisponibles->count()
    ]);
  }

  public function crearReserva(Request $request)
  {
    $request->validate([
      'habitacion_id' => 'required|exists:habitacions,id',
      'cliente_id' => 'required|exists:clientes,id',
      'fecha_entrada' => 'required|date',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'adelanto' => 'nullable|numeric|min:0'
    ]);

    // Verificar disponibilidad
    $fechaEntrada = Carbon::parse($request->fecha_entrada);
    $fechaSalida = Carbon::parse($request->fecha_salida);

    $habitacion = \App\Models\Habitacion::findOrFail($request->habitacion_id);

    if ($habitacion->estado !== 'Disponible') {
      return response()->json([
        'error' => 'La habitación no está disponible'
      ], 400);
    }

    $reservaExistente = $habitacion->reservas()
      ->where(function ($query) use ($fechaEntrada, $fechaSalida) {
        $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
          ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
          ->orWhere(function ($q) use ($fechaEntrada, $fechaSalida) {
            $q->where('fecha_entrada', '<=', $fechaEntrada)
              ->where('fecha_salida', '>=', $fechaSalida);
          });
      })
      ->whereIn('estado', ['Check-in', 'Pendiente'])
      ->exists();

    if ($reservaExistente) {
      return response()->json([
        'error' => 'La habitación ya está reservada para esas fechas'
      ], 400);
    }

    // Crear la reserva
    $reserva = Reserva::create([
      'habitacion_id' => $request->habitacion_id,
      'cliente_id' => $request->cliente_id,
      'fecha_entrada' => $fechaEntrada,
      'fecha_salida' => $fechaSalida,
      'estado' => 'Pendiente',
      'adelanto' => $request->adelanto ?? 0
    ]);

    return response()->json([
      'message' => 'Reserva creada exitosamente',
      'reserva' => $reserva
    ], 201);
  }
}
