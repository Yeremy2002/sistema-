<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use Illuminate\Support\Carbon;

class DetectarReservasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservas:vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and manage expired reservations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Iniciando búsqueda de reservas vencidas...');
        
        $reservasVencidas = Reserva::where('estado', 'Pendiente')
            ->whereDate('fecha_entrada', '<', Carbon::now())
            ->get();

        $this->info('Encontradas ' . $reservasVencidas->count() . ' reservas pendientes con fecha de entrada pasada.');

        foreach ($reservasVencidas as $reserva) {
            $diasVencidos = $reserva->fecha_entrada->diffInDays(Carbon::now());
            $this->info("Procesando reserva #{$reserva->id} - Días vencidos: {$diasVencidos}");
            
            if ($diasVencidos > 0) {
                $reserva->estado = 'Vencida';
                $reserva->save();
                
                // Liberar la habitación
                $habitacion = $reserva->habitacion;
                if ($habitacion) {
                    $this->info("Liberando habitación #{$habitacion->numero} (estado anterior: {$habitacion->estado})");
                    $habitacion->estado = 'Disponible';
                    $habitacion->save();
                }
                
                // Enviar notificaciones a recepcionistas y administradores
                $usuariosANotificar = \App\Models\User::where('active', true)
                    ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Recepcionista', 'Administrador', 'Super Admin']);
            })
                    ->get();

                foreach ($usuariosANotificar as $usuario) {
                    $mensaje = 'La reserva para la habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente . ' ha vencido por fecha de entrada pasada';
                    $usuario->notify(new \App\Notifications\ReservaExpiradaNotification($reserva, $mensaje));
                }
                
                $this->info("Reserva #{$reserva->id} para {$reserva->nombre_cliente} marcada como VENCIDA y notificaciones enviadas.");
            }
        }

        $this->info('Proceso completado. Reservas vencidas actualizadas.');
    }
}
