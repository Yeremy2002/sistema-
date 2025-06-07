<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservaApiController extends Controller
{
  public function disponibilidad(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'fecha_entrada' => 'required|date|after_or_equal:today',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'categoria_id' => 'nullable|exists:categorias,id'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'error' => true,
        'mensaje' => 'Error de validación',
        'errores' => $validator->errors()
      ], 422);
    }

    $fechaEntrada = Carbon::parse($request->fecha_entrada);
    $fechaSalida = Carbon::parse($request->fecha_salida);

    // Validar que la estadía no sea mayor a 30 días
    if ($fechaEntrada->diffInDays($fechaSalida) > 30) {
      return response()->json([
        'error' => true,
        'mensaje' => 'La estadía no puede ser mayor a 30 días'
      ], 422);
    }

    $query = Habitacion::where('estado', 'Disponible')
      ->with(['categoria', 'imagenes']);

    if ($request->has('categoria_id')) {
      $query->where('categoria_id', $request->categoria_id);
    }

    $habitaciones = $query->get();

    // Filtrar habitaciones que no tienen reservas en las fechas seleccionadas
    $habitacionesDisponibles = $habitaciones->filter(function ($habitacion) use ($fechaEntrada, $fechaSalida) {
      return !$habitacion->reservas()
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
    });

    return response()->json([
      'error' => false,
      'habitaciones' => $habitacionesDisponibles->map(function ($habitacion) {
        return [
          'id' => $habitacion->id,
          'numero' => $habitacion->numero,
          'categoria' => $habitacion->categoria->nombre,
          'nivel' => $habitacion->nivel,
          'precio' => $habitacion->precio,
          'imagen' => $habitacion->imagenes->isNotEmpty()
            ? asset('storage/' . $habitacion->imagenes->first()->ruta)
            : null
        ];
      })
    ]);
  }

  public function crearReserva(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'habitacion_id' => 'required|exists:habitaciones,id',
      'cliente_id' => 'required|exists:clientes,id',
      'fecha_entrada' => 'required|date|after_or_equal:today',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'adelanto' => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'error' => true,
        'mensaje' => 'Error de validación',
        'errores' => $validator->errors()
      ], 422);
    }

    $fechaEntrada = Carbon::parse($request->fecha_entrada);
    $fechaSalida = Carbon::parse($request->fecha_salida);

    // Validar que la estadía no sea mayor a 30 días
    if ($fechaEntrada->diffInDays($fechaSalida) > 30) {
      return response()->json([
        'error' => true,
        'mensaje' => 'La estadía no puede ser mayor a 30 días'
      ], 422);
    }

    $habitacion = Habitacion::findOrFail($request->habitacion_id);

    // Verificar disponibilidad
    $estaDisponible = !$habitacion->reservas()
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

    if (!$estaDisponible) {
      return response()->json([
        'error' => true,
        'mensaje' => 'La habitación no está disponible para las fechas seleccionadas'
      ], 422);
    }

    // Crear la reserva
    $reserva = Reserva::create([
      'habitacion_id' => $request->habitacion_id,
      'cliente_id' => $request->cliente_id,
      'fecha_entrada' => $fechaEntrada,
      'fecha_salida' => $fechaSalida,
      'estado' => 'Pendiente',
      'precio' => $habitacion->precio,
      'adelanto' => $request->adelanto,
      'total' => $habitacion->precio * $fechaEntrada->diffInDays($fechaSalida)
    ]);

    return response()->json([
      'error' => false,
      'mensaje' => 'Reserva creada exitosamente',
      'reserva' => $reserva
    ]);
  }
}
