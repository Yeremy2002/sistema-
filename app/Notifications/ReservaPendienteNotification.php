<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Reserva;

class ReservaPendienteNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public $reserva;
  public $mensaje;

  public function __construct(Reserva $reserva, $mensaje = null)
  {
    $this->reserva = $reserva;
    $this->mensaje = $mensaje ?? 'Nueva reserva pendiente de confirmación para la habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente;
  }

  public function via($notifiable)
  {
    return ['database', 'broadcast'];
  }

  public function toArray($notifiable)
  {
    // Forzar el uso de APP_URL para generar URLs correctas en contexto de queue
    $url = config('app.url') . '/reservas/' . $this->reserva->id;
    
    return [
      'tipo' => 'reserva_pendiente',
      'habitacion' => $this->reserva->habitacion->numero,
      'mensaje' => $this->mensaje,
      'reserva_id' => $this->reserva->id,
      'url' => $url,
    ];
  }

  public function toBroadcast($notifiable)
  {
    return new BroadcastMessage($this->toArray($notifiable));
  }
}
