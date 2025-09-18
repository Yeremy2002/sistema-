<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired reservations and free up rooms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired reservations...');
        
        // Obtener configuración del hotel
        $hotel = Hotel::first();
        $hoursToExpire = $hotel ? $hotel->reservas_vencidas_horas : 24;
        
        $this->info("Configuración: Las reservas vencen después de {$hoursToExpire} horas.");
        
        // Obtener todas las reservas pendientes de confirmación que han expirado
        // Consideramos tanto expires_at como created_at + horas configuradas
        $expiredReservations = Reserva::where('estado', 'Pendiente de Confirmación')
            ->where(function($query) use ($hoursToExpire) {
                $query->where('expires_at', '<', Carbon::now())
                      ->orWhere('created_at', '<', Carbon::now()->subHours($hoursToExpire));
            })
            ->get();
            
        $count = 0;
        
        foreach ($expiredReservations as $reservation) {
            // Usar el método cancelarPorExpiracion que incluye notificaciones
            if ($reservation->cancelarPorExpiracion()) {
                // Agregar observaciones sobre la expiración
                $reservation->observaciones = ($reservation->observaciones ? $reservation->observaciones . ' | ' : '') . 
                                            'Cancelada automáticamente por expiración el ' . Carbon::now()->format('d/m/Y H:i');
                $reservation->save();
                
                $count++;
                $this->info("Reserva #{$reservation->id} cancelada por expiración y notificaciones enviadas.");
                
                // Log de la acción
                Log::info("Reserva #{$reservation->id} cancelada automáticamente por CleanExpiredReservations", [
                    'reserva_id' => $reservation->id,
                    'habitacion_id' => $reservation->habitacion_id,
                    'cliente' => $reservation->nombre_cliente,
                    'fecha_entrada' => $reservation->fecha_entrada,
                    'fecha_salida' => $reservation->fecha_salida,
                    'expires_at' => $reservation->expires_at,
                    'expired_at' => now()
                ]);
            }
        }
        
        $this->info("Proceso completado. Se anularon {$count} reservas vencidas.");
        
        return Command::SUCCESS;
    }
}
