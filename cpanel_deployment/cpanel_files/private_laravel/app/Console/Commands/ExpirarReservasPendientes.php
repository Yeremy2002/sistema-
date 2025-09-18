<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use Carbon\Carbon;

class ExpirarReservasPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservas:expirar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira automáticamente las reservas pendientes de confirmación que han pasado su fecha de expiración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de expiración de reservas pendientes...');

        // Buscar reservas pendientes de confirmación que han expirado
        $reservasExpiradas = Reserva::where('estado', 'Pendiente de Confirmación')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($reservasExpiradas as $reserva) {
            if ($reserva->cancelarPorExpiracion()) {
                $count++;
                $this->info("Reserva #{$reserva->id} expirada y cancelada automáticamente.");

                // Log de la acción
                \Log::info("Reserva #{$reserva->id} expirada automáticamente", [
                    'reserva_id' => $reserva->id,
                    'habitacion_id' => $reserva->habitacion_id,
                    'cliente' => $reserva->nombre_cliente,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'expires_at' => $reserva->expires_at,
                    'expired_at' => now()
                ]);
            }
        }

        if ($count > 0) {
            $this->info("Se expiraron y cancelaron {$count} reservas pendientes de confirmación.");
        } else {
            $this->info('No se encontraron reservas pendientes de confirmación para expirar.');
        }

        return 0;
    }
}
