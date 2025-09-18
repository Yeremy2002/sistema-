<?php

namespace App\Observers;

use App\Models\Caja;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class CajaObserver
{
    /**
     * Handle the Caja "updated" event.
     *
     * @param  \App\Models\Caja  $caja
     * @return void
     */
    public function updated(Caja $caja): void
    {
        // Si la caja pasó de abierta (estado=true) a cerrada (estado=false)
        if ($caja->isDirty('estado') && $caja->getOriginal('estado') === true && $caja->estado === false) {
            // Marcar como leídas todas las notificaciones de tipo "recordatorio_cierre_caja" relacionadas
            DatabaseNotification::query()
                ->whereNull('read_at')
                ->where('data->type', 'recordatorio_cierre_caja')
                ->where('data->caja_id', $caja->id)
                ->update(['read_at' => now()]);

            Log::info("Notificaciones de cierre de caja marcadas como leídas para Caja #{$caja->id}");
        }
    }
}

