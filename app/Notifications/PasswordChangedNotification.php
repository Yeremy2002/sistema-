<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    return (new MailMessage)
      ->subject('Notificación de cambio de contraseña | Hotel Gestión')
      ->greeting('¡Hola ' . $notifiable->name . '!')
      ->line('Queremos informarte que la contraseña de tu cuenta en <b>Hotel Gestión</b> ha sido cambiada exitosamente.')
      ->line('Si realizaste este cambio, puedes ignorar este mensaje.')
      ->line('Si <b>NO</b> realizaste este cambio, por favor contacta inmediatamente al administrador o responde a este correo para reportarlo.')
      ->salutation('Gracias por confiar en nosotros.\nEquipo de Hotel Gestión');
  }
}
