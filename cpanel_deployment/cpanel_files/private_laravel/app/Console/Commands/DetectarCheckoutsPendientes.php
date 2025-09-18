<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Hotel;
use App\Notifications\CheckoutPendienteNotification;
use Carbon\Carbon;

class DetectarCheckoutsPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservas:checkouts-pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta reservas con checkout pendiente fuera del horario establecido y notifica a administradores y recepcionistas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando detección de checkouts pendientes...');
        
        $hotel = Hotel::getInfo();
        $ahora = Carbon::now();
        $hoy = $ahora->toDateString();
        
        // Obtener horarios de checkout del hotel
        $horaCheckoutInicio = $hotel->checkout_hora_inicio ? $hotel->checkout_hora_inicio->format('H:i') : '12:30';
        $horaCheckoutFin = $hotel->checkout_hora_fin ? $hotel->checkout_hora_fin->format('H:i') : '13:00';
        
        // Buscar reservas en Check-in con fecha de salida de hoy
        $reservasCheckout = Reserva::where('estado', 'Check-in')
            ->whereDate('fecha_salida', $hoy)
            ->with(['habitacion', 'user'])
            ->get();
            
        $this->info("Encontradas {$reservasCheckout->count()} reservas con checkout programado para hoy.");
        
        $checkoutsPendientes = collect();
        $checkoutsRequierenAutorizacion = collect();
        
        foreach ($reservasCheckout as $reserva) {
            $fechaSalidaProgramada = $reserva->fecha_salida;
            $horaLimiteCheckout = Carbon::createFromFormat('Y-m-d H:i', $hoy . ' ' . $horaCheckoutFin);
            
            // Si ya pasó la hora límite de checkout
            if ($ahora->gt($horaLimiteCheckout)) {
                $horasVencidas = $ahora->diffInHours($horaLimiteCheckout);
                $minutosVencidos = $ahora->diffInMinutes($horaLimiteCheckout) % 60;
                
                $this->warn("Checkout pendiente: Habitación {$reserva->habitacion->numero} - Cliente: {$reserva->nombre_cliente}");
                $this->warn("  Tiempo vencido: {$horasVencidas}h {$minutosVencidos}m");
                
                // Determinar si requiere autorización administrativa
                $requiereAutorizacion = $horasVencidas >= 2; // Más de 2 horas vencido
                
                if ($requiereAutorizacion) {
                    $checkoutsRequierenAutorizacion->push([
                        'reserva' => $reserva,
                        'horas_vencidas' => $horasVencidas
                    ]);
                } else {
                    $checkoutsPendientes->push([
                        'reserva' => $reserva,
                        'horas_vencidas' => $horasVencidas
                    ]);
                }
            }
        }
        
        // Obtener usuarios a notificar (administradores y recepcionistas activos)
        $usuariosANotificar = User::where('active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Administrador', 'Recepcionista', 'Super Admin']);
            })
            ->get();
            
        $this->info("Usuarios a notificar: {$usuariosANotificar->count()}");
        
        // Enviar notificaciones para checkouts pendientes (sin autorización)
        foreach ($checkoutsPendientes as $item) {
            $reserva = $item['reserva'];
            $horasVencidas = $item['horas_vencidas'];
            
            foreach ($usuariosANotificar as $usuario) {
                $usuario->notify(new CheckoutPendienteNotification($reserva, $horasVencidas, false));
            }
            
            $this->info("Notificación enviada para checkout pendiente: Habitación {$reserva->habitacion->numero}");
        }
        
        // Enviar notificaciones para checkouts que requieren autorización
        foreach ($checkoutsRequierenAutorizacion as $item) {
            $reserva = $item['reserva'];
            $horasVencidas = $item['horas_vencidas'];
            
            // Solo notificar a administradores para casos que requieren autorización
            $administradores = $usuariosANotificar->filter(function ($usuario) {
                return $usuario->hasRole(['Administrador', 'Super Admin']);
            });
            
            foreach ($administradores as $admin) {
                $admin->notify(new CheckoutPendienteNotification($reserva, $horasVencidas, true));
            }
            
            $this->warn("Notificación de autorización enviada para checkout crítico: Habitación {$reserva->habitacion->numero} ({$horasVencidas}h vencido)");
        }
        
        $totalNotificaciones = $checkoutsPendientes->count() + $checkoutsRequierenAutorizacion->count();
        
        if ($totalNotificaciones > 0) {
            $this->info("Proceso completado. Se enviaron notificaciones para {$totalNotificaciones} checkouts pendientes.");
            $this->info("- Checkouts pendientes: {$checkoutsPendientes->count()}");
            $this->info("- Checkouts que requieren autorización: {$checkoutsRequierenAutorizacion->count()}");
        } else {
            $this->info('No se encontraron checkouts pendientes.');
        }
        
        return 0;
    }
}