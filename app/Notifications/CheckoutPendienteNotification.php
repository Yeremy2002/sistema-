<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class CheckoutPendienteNotification extends Notification
{
    use Queueable;

    protected $reserva;
    protected $horasVencidas;
    protected $requiereAutorizacion;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reserva $reserva, $horasVencidas, $requiereAutorizacion = false)
    {
        $this->reserva = $reserva;
        $this->horasVencidas = $horasVencidas;
        $this->requiereAutorizacion = $requiereAutorizacion;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $mensaje = $this->requiereAutorizacion 
            ? "Check-out pendiente requiere autorización administrativa"
            : "Check-out no realizado en horario establecido";
            
        return [
            'tipo' => 'checkout_pendiente',
            'titulo' => 'Check-out Pendiente',
            'mensaje' => $mensaje,
            'reserva_id' => $this->reserva->id,
            'habitacion_numero' => $this->reserva->habitacion->numero,
            'cliente_nombre' => $this->reserva->nombre_cliente,
            'fecha_salida' => $this->reserva->fecha_salida->format('d/m/Y'),
            'hora_salida_programada' => $this->reserva->fecha_salida->format('H:i'),
            'horas_vencidas' => $this->horasVencidas,
            'requiere_autorizacion' => $this->requiereAutorizacion,
            'url' => route('reservas.checkout', $this->reserva->id),
            'icono' => 'fas fa-clock',
            'color' => $this->requiereAutorizacion ? 'danger' : 'warning',
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}