<?php

namespace App\Observers;

use App\Models\Reserva;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class ReservaObserver
{
    /**
     * Handle the Reserva "updated" event.
     */
    public function updated(Reserva $reserva): void
    {
        if ($reserva->isDirty('estado')) {
            $orig = $reserva->getOriginal('estado');
            $nuevo = $reserva->estado;

            $pendientes = [
                'Pendiente de Confirmación',
                'Reservada-Pendiente',
                'Pendiente'
            ];

            // Si pasó de un estado pendiente a cualquier otro (check-in, cancelada, etc.)
            if (in_array($orig, $pendientes) && !in_array($nuevo, $pendientes)) {
                DatabaseNotification::query()
                    ->whereNull('read_at')
                    ->where('data->tipo', 'reserva_pendiente')
                    ->where('data->reserva_id', $reserva->id)
                    ->update(['read_at' => now()]);

                Log::info("Notificaciones de reserva pendiente marcadas como leídas para Reserva #{$reserva->id}");
            }
        }
    }
}

