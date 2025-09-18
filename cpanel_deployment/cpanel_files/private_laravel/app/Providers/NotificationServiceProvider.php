<?php

namespace App\Providers;

use App\Models\Hotel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Inyectar configuración de notificaciones en AdminLTE
        $this->configureAdminLteNotifications();
    }

    /**
     * Configurar las notificaciones de AdminLTE dinámicamente
     */
    private function configureAdminLteNotifications()
    {
        View::composer('*', function ($view) {
            if (Auth::check() && Schema::hasTable('hotels')) {
                $hotel = Hotel::getInfo();
                $unreadCount = Auth::user()->unreadNotifications->count();
                
                // Modificar la configuración del menú de AdminLTE
                $menu = config('adminlte.menu', []);
                $menu = $this->updateNotificationMenuItem($menu, $unreadCount, $hotel);
                
                config(['adminlte.menu' => $menu]);
            }
        });
    }

    /**
     * Actualizar el item de notificación en el menú
     */
    private function updateNotificationMenuItem($menu, $unreadCount, $hotel)
    {
        foreach ($menu as $index => $item) {
            if (isset($item['type']) && $item['type'] === 'navbar-notification') {
                $menu[$index]['badge_label'] = $unreadCount;
                $menu[$index]['badge_color'] = $unreadCount > 0 
                    ? ($hotel->notificacion_badge_color ?? 'danger') 
                    : 'secondary';
                
                // Configurar actualización automática si está activa
                if ($hotel->notificacion_activa) {
                    $menu[$index]['update_cfg'] = [
                        'url' => route('notifications.count'),
                        'period' => $hotel->notificacion_intervalo_segundos ?? 30,
                    ];
                }
                break;
            }
        }
        
        return $menu;
    }
}
