<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function __construct()
  {
    $this->middleware('permission:ver habitaciones|ver reservas|ver clientes|ver reportes');
  }

  public function index()
  {
    $habitaciones = Habitacion::with(['categoria', 'nivel', 'reservaActiva'])
      ->orderBy('numero')
      ->get();    // Identifica habitaciones con check-out próximo (menos de 2 horas)
    $now = now(); // Fecha actual usando la zona horaria configurada (America/Guatemala)
    $checkoutHoy = [];

    // Para diagnóstico - almacenar la fecha actual en la vista
    $fechaActualSistema = $now->format('Y-m-d H:i:s');

    foreach ($habitaciones as $habitacion) {
      if ($habitacion->estado === 'Ocupada' && $habitacion->reservaActiva) {
        $fechaSalida = $habitacion->reservaActiva->fecha_salida;

        // Si la fecha de salida es hoy (compara solo la parte de fecha, no la hora)
        if ($fechaSalida->format('Y-m-d') === $now->format('Y-m-d')) {
          // Configura la hora de checkout (12:30)
          $fechaCheckout = clone $fechaSalida;
          $fechaCheckout->setTime(12, 30, 0);

          // Si faltan menos de 2 horas para el checkout
          $horasRestantes = $now->diffInHours($fechaCheckout, false);
          if ($horasRestantes > 0 && $horasRestantes < 2) {
            $checkoutHoy[$habitacion->id] = [
              'checkout_hoy' => true,
              'urgente' => true,
              'horas_restantes' => $horasRestantes,
              'minutos_restantes' => $now->diffInMinutes($fechaCheckout) % 60
            ];
          }
          // Si ya pasó la hora de checkout
          elseif ($horasRestantes <= 0 && $now->format('H:i') > '12:30') {
            $checkoutHoy[$habitacion->id] = [
              'checkout_hoy' => true,
              'checkout_vencido' => true,
              'horas_vencidas' => abs($horasRestantes),
              'minutos_vencidos' => abs($now->diffInMinutes($fechaCheckout) % 60)
            ];
          } else {
            // Checkout hoy pero no es urgente aún
            $checkoutHoy[$habitacion->id] = [
              'checkout_hoy' => true
            ];
          }
        }
      }
    }

    return view('admin.dashboard', compact('habitaciones', 'checkoutHoy', 'fechaActualSistema'));
  }
}
