<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LimpiezaMantenimientoNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public $tipo;
  public $habitacion;
  public $mensaje;
  public $registro_id;
  public $registro_tipo;

  public function __construct($tipo, $habitacion, $mensaje, $registro_id = null, $registro_tipo = null)
  {
    $this->tipo = $tipo; // 'limpieza' o 'mantenimiento'
    $this->habitacion = $habitacion;
    $this->mensaje = $mensaje;
    $this->registro_id = $registro_id;
    $this->registro_tipo = $registro_tipo;
  }

  public function via($notifiable)
  {
    return ['database', 'broadcast'];
  }

  public function toArray($notifiable)
  {
    return [
      'tipo' => $this->tipo,
      'habitacion' => $this->habitacion,
      'mensaje' => $this->mensaje,
      'registro_id' => $this->registro_id,
      'registro_tipo' => $this->registro_tipo,
      'url' => $this->getUrl(),
    ];
  }

  public function toBroadcast($notifiable)
  {
    return new BroadcastMessage([
      'tipo' => $this->tipo,
      'habitacion' => $this->habitacion,
      'mensaje' => $this->mensaje,
      'registro_id' => $this->registro_id,
      'registro_tipo' => $this->registro_tipo,
      'url' => $this->getUrl(),
    ]);
  }

  private function getUrl()
  {
    $baseUrl = config('app.url');
    
    if ($this->registro_tipo === 'limpieza' && $this->registro_id) {
      return $baseUrl . '/mantenimiento/limpieza#limpieza-' . $this->registro_id;
    }
    if ($this->registro_tipo === 'mantenimiento' && $this->registro_id) {
      return $baseUrl . '/mantenimiento/reparaciones#reparacion-' . $this->registro_id;
    }
    return null;
  }
}
