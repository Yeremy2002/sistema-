<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Hotel;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    // Obtener configuración del hotel
    $hotel = Hotel::first();
    $schedulerFrequency = $hotel ? $hotel->scheduler_frecuencia : '1h';
    
    // Ejecutar el comando de expiración de reservas cada 5 minutos
    $schedule->command('reservas:expirar')
      ->everyFiveMinutes()
      ->withoutOverlapping()
      ->runInBackground();
      
    // Detectar reservas vencidas por fecha de entrada pasada cada hora
    $schedule->command('reservas:vencidas')
      ->hourly()
      ->withoutOverlapping()
      ->runInBackground();
      
    // Limpiar reservas expiradas según la configuración
    $cleanupCommand = $schedule->command('reservations:clean-expired')
      ->withoutOverlapping()
      ->runInBackground();
      
    // Aplicar frecuencia según configuración
    switch($schedulerFrequency) {
        case '5m':
            $cleanupCommand->everyFiveMinutes();
            break;
        case '10m':
            $cleanupCommand->everyTenMinutes();
            break;
        case '15m':
            $cleanupCommand->everyFifteenMinutes();
            break;
        case '30m':
            $cleanupCommand->everyThirtyMinutes();
            break;
        case '1h':
            $cleanupCommand->hourly();
            break;
        case '2h':
            $cleanupCommand->everyTwoHours();
            break;
        case '4h':
            $cleanupCommand->everyFourHours();
            break;
        case '6h':
            $cleanupCommand->everySixHours();
            break;
        case '12h':
            $cleanupCommand->twiceDaily(1, 13);
            break;
        case '24h':
            $cleanupCommand->daily();
            break;
        default:
            $cleanupCommand->hourly(); // Default a cada hora
    }
      
    // Detectar checkouts pendientes cada 30 minutos
    $schedule->command('reservas:checkouts-pendientes')
      ->everyThirtyMinutes()
      ->withoutOverlapping()
      ->runInBackground()
      ->appendOutputTo(storage_path('logs/checkouts-pendientes.log'));
      
    // Verificar cierres de caja pendientes cada 30 minutos
    $schedule->command('cajas:verificar-cierres')
      ->everyThirtyMinutes()
      ->withoutOverlapping()
      ->runInBackground()
      ->appendOutputTo(storage_path('logs/caja-verificaciones.log'));
      
    // Verificación intensiva en horarios de cambio de turno
    // Turno matutino (1:00 PM - 3:00 PM)
    $schedule->command('cajas:verificar-cierres')
      ->dailyAt('13:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('cajas:verificar-cierres')
      ->dailyAt('14:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    // Turno nocturno (5:00 AM - 7:00 AM)
    $schedule->command('cajas:verificar-cierres')
      ->dailyAt('05:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('cajas:verificar-cierres')
      ->dailyAt('06:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    // Verificación intensiva de checkouts pendientes en horarios críticos
    // Al mediodía (12:30 PM - límite de checkout)
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('12:30')
      ->withoutOverlapping()
      ->runInBackground();
      
    // Cada 15 minutos después del límite de checkout (1:00 PM - 3:00 PM)
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('13:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('13:15')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('13:30')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('13:45')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('14:00')
      ->withoutOverlapping()
      ->runInBackground();
      
    $schedule->command('reservas:checkouts-pendientes')
      ->dailyAt('15:00')
      ->withoutOverlapping()
      ->runInBackground();
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
