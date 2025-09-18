<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Caja;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    // Obtener información de cajas para el dashboard
    $estadoCajas = $this->obtenerEstadoCajas($now);
    
    return view('admin.dashboard', compact('habitaciones', 'checkoutHoy', 'fechaActualSistema', 'estadoCajas'));
  }
  
  /**
   * Obtiene el estado actual de las cajas para el dashboard
   */
  private function obtenerEstadoCajas(Carbon $now): array
  {
    // Solo mostrar información de cajas si el usuario tiene permisos
    if (!\Auth::user()->can('ver cajas')) {
      return [
        'total_abiertas' => 0,
        'cajas_problematicas' => collect(),
        'cajas_normales' => collect(),
        'mostrar_widget' => false
      ];
    }
    
    $cajasAbiertas = Caja::with('user')
      ->where('estado', true)
      ->get();
      
    $cajasProblematicas = collect();
    $cajasNormales = collect();
    
    foreach ($cajasAbiertas as $caja) {
      $horasAbierta = $now->diffInHours($caja->fecha_apertura);
      $esCajaAntigua = $caja->fecha_apertura->startOfDay()->lt($now->startOfDay());
      
      $estadoCaja = [
        'caja' => $caja,
        'horas_abierta' => $horasAbierta,
        'es_antigua' => $esCajaAntigua,
        'severidad' => 'normal',
        'mensaje' => '',
        'color' => 'primary',
        'icono' => 'fas fa-cash-register'
      ];
      
      if ($esCajaAntigua) {
        $estadoCaja['severidad'] = 'urgente';
        $estadoCaja['mensaje'] = 'Caja abierta desde día anterior';
        $estadoCaja['color'] = 'danger';
        $estadoCaja['icono'] = 'fas fa-exclamation-triangle';
        $cajasProblematicas->push($estadoCaja);
      } elseif ($horasAbierta > 12) {
        $estadoCaja['severidad'] = 'advertencia';
        $estadoCaja['mensaje'] = sprintf('Abierta %.1f horas', $horasAbierta);
        $estadoCaja['color'] = 'warning';
        $estadoCaja['icono'] = 'fas fa-clock';
        $cajasProblematicas->push($estadoCaja);
      } else {
        // Verificar cambio de turno
        $horaActual = $now->hour;
        $turnoActual = ($horaActual >= 6 && $horaActual < 18) ? 'matutino' : 'nocturno';
        $esCambioTurno = ($horaActual >= 13 && $horaActual < 15) || ($horaActual >= 5 && $horaActual < 7);
        
        if ($esCambioTurno && $caja->turno !== $turnoActual) {
          $estadoCaja['severidad'] = 'cambio_turno';
          $estadoCaja['mensaje'] = "Cambio de turno ({$caja->turno} → {$turnoActual})";
          $estadoCaja['color'] = 'info';
          $estadoCaja['icono'] = 'fas fa-exchange-alt';
          $cajasProblematicas->push($estadoCaja);
        } else {
          $estadoCaja['mensaje'] = sprintf('Funcionando normalmente (%.1fh)', $horasAbierta);
          $cajasNormales->push($estadoCaja);
        }
      }
    }
    
    return [
      'total_abiertas' => $cajasAbiertas->count(),
      'cajas_problematicas' => $cajasProblematicas,
      'cajas_normales' => $cajasNormales,
      'mostrar_widget' => $cajasAbiertas->isNotEmpty(),
      'hay_problemas' => $cajasProblematicas->isNotEmpty()
    ];
  }
}
