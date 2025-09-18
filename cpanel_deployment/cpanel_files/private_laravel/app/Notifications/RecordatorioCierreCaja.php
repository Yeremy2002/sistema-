<?php

namespace App\Notifications;

use App\Models\Caja;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class RecordatorioCierreCaja extends Notification implements ShouldQueue
{
    use Queueable;

    protected $caja;
    protected $tipo;
    protected $razon;

    /**
     * Create a new notification instance.
     */
    public function __construct(Caja $caja, string $tipo = 'normal', string $razon = '')
    {
        $this->caja = $caja;
        $this->tipo = $tipo;
        $this->razon = $razon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject();
        $priority = $this->getPriority();
        
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting($this->getGreeting($notifiable))
            ->line($this->getMainMessage())
            ->line($this->getDetailMessage())
            ->action('Cerrar Caja Ahora', config('app.url') . '/cajas/' . $this->caja->id . '/edit')
            ->line('Es importante mantener un control adecuado de las operaciones de caja.');
            
        // Configurar prioridad del correo
        if ($priority === 'high') {
            $message->priority(1); // Alta prioridad
        }
        
        return $message;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'recordatorio_cierre_caja',
            'caja_id' => $this->caja->id,
            'severity' => $this->getSeverity(),
            'title' => $this->getTitle(),
            'message' => $this->getMainMessage(),
            'reason' => $this->razon,
            'action_url' => config('app.url') . '/cajas/' . $this->caja->id . '/edit',
            'action_text' => 'Cerrar Caja',
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'expires_at' => now()->addHours(2), // La notificación expira en 2 horas
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
    
    /**
     * Obtiene el asunto del correo según el tipo
     */
    private function getSubject(): string
    {
        return match($this->tipo) {
            'urgente' => '🚨 URGENTE: Caja pendiente de cierre',
            'advertencia' => '⚠️ ADVERTENCIA: Caja abierta por mucho tiempo',
            'cambio_turno' => '🔄 Recordatorio: Cambio de turno - Cerrar caja',
            'supervisor' => '👥 ALERTA SUPERVISOR: Caja problemática',
            'forzado' => '📋 Verificación de caja',
            default => '📝 Recordatorio: Cierre de caja pendiente'
        };
    }
    
    /**
     * Obtiene el saludo personalizado
     */
    private function getGreeting(object $notifiable): string
    {
        $hora = Carbon::now()->hour;
        $saludo = match(true) {
            $hora >= 5 && $hora < 12 => 'Buenos días',
            $hora >= 12 && $hora < 18 => 'Buenas tardes',
            default => 'Buenas noches'
        };
        
        return "{$saludo}, {$notifiable->name}";
    }
    
    /**
     * Obtiene el mensaje principal
     */
    private function getMainMessage(): string
    {
        $horasAbierta = Carbon::now()->diffInHours($this->caja->fecha_apertura);
        
        return match($this->tipo) {
            'urgente' => "Tiene una caja abierta desde el día anterior (Caja #{$this->caja->id}). Es crítico que realice el cierre inmediatamente.",
            'advertencia' => "Su caja #{$this->caja->id} ha estado abierta por {$horasAbierta} horas. Se recomienda realizar el cierre.",
            'cambio_turno' => "Es hora de cambio de turno. Por favor, proceda con el cierre de su caja #{$this->caja->id}.",
            'supervisor' => "ALERTA: El usuario {$this->caja->user->name} tiene una caja problemática (#{$this->caja->id}) que requiere atención.",
            'forzado' => "Verificación programada: Su caja #{$this->caja->id} está abierta y pendiente de cierre.",
            default => "Recordatorio: Su caja #{$this->caja->id} está pendiente de cierre."
        };
    }
    
    /**
     * Obtiene el mensaje de detalles
     */
    private function getDetailMessage(): string
    {
        $detalles = [
            "Caja: #{$this->caja->id} (Turno {$this->caja->turno})",
            "Apertura: {$this->caja->fecha_apertura->format('d/m/Y H:i')}",
            "Saldo actual: " . number_format($this->caja->saldo_actual, 2)
        ];
        
        if ($this->razon) {
            $detalles[] = "Razón: {$this->razon}";
        }
        
        return implode(' | ', $detalles);
    }
    
    /**
     * Obtiene la prioridad del correo
     */
    private function getPriority(): string
    {
        return in_array($this->tipo, ['urgente', 'supervisor']) ? 'high' : 'normal';
    }
    
    /**
     * Obtiene la severidad para la base de datos
     */
    private function getSeverity(): string
    {
        return match($this->tipo) {
            'urgente', 'supervisor' => 'high',
            'advertencia' => 'medium',
            default => 'low'
        };
    }
    
    /**
     * Obtiene el título para la notificación
     */
    private function getTitle(): string
    {
        return match($this->tipo) {
            'urgente' => 'Cierre de caja URGENTE',
            'advertencia' => 'Caja abierta por mucho tiempo',
            'cambio_turno' => 'Cambio de turno',
            'supervisor' => 'Caja problemática',
            'forzado' => 'Verificación de caja',
            default => 'Recordatorio de cierre'
        };
    }
    
    /**
     * Obtiene el icono para la notificación
     */
    private function getIcon(): string
    {
        return match($this->tipo) {
            'urgente' => 'fas fa-exclamation-triangle',
            'advertencia' => 'fas fa-clock',
            'cambio_turno' => 'fas fa-exchange-alt',
            'supervisor' => 'fas fa-user-shield',
            'forzado' => 'fas fa-search',
            default => 'fas fa-cash-register'
        };
    }
    
    /**
     * Obtiene el color para la notificación
     */
    private function getColor(): string
    {
        return match($this->tipo) {
            'urgente' => 'danger',
            'advertencia' => 'warning',
            'cambio_turno' => 'info',
            'supervisor' => 'dark',
            'forzado' => 'secondary',
            default => 'primary'
        };
    }
}
