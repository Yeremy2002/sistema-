<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Reserva;

class ReservaExpiradaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $reserva;
    public $mensaje;

    public function __construct(Reserva $reserva, $mensaje = null)
    {
        $this->reserva = $reserva;
        $this->mensaje = $mensaje ?? 'La reserva para la habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente . ' ha expirado y fue cancelada automáticamente';
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
            'tipo' => 'reserva_expirada',
            'habitacion' => $this->reserva->habitacion->numero,
            'mensaje' => $this->mensaje,
            'reserva_id' => $this->reserva->id,
            'cliente_nombre' => $this->reserva->nombre_cliente,
            'fecha_entrada' => $this->reserva->fecha_entrada->format('d/m/Y'),
            'fecha_salida' => $this->reserva->fecha_salida->format('d/m/Y'),
            'expired_at' => now()->format('d/m/Y H:i'),
            'url' => $url,
            'icon' => 'fas fa-clock',
            'color' => 'warning',
            'severity' => 'medium'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}