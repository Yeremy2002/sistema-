<?php

namespace App\Providers;

use App\Models\Hotel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Observers\CajaObserver;
use App\Models\Caja;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuración dinámica de la duración de la sesión
        if (\Schema::hasTable('hotels')) {
            $hotel = Hotel::first();
            if ($hotel && $hotel->session_lifetime) {
                config(['session.lifetime' => (int)$hotel->session_lifetime]);
            }
        }

        // Compartir la configuración del hotel en todas las vistas
        View::composer('*', function ($view) {
            if (Schema::hasTable('hotels')) {
                $view->with('hotel', \App\Models\Hotel::first());
            } else {
                $view->with('hotel', null);
            }
        });

        // Configurar AdminLTE dinámicamente
        $this->configureAdminLTE();
    }

    /**
     * Configurar AdminLTE con información dinámica del hotel
     */
    protected function configureAdminLTE()
    {
        try {
            if (Schema::hasTable('hotels')) {
                $hotel = Hotel::first();
                
                if ($hotel) {
                    // Configurar logo y título dinámicamente
                    config([
                        'adminlte.logo' => $hotel->nombre ? '<b>' . $hotel->nombre . '</b>' : '<b>Hotel</b>',
                        'adminlte.logo_img_alt' => $hotel->nombre ? $hotel->nombre . ' Logo' : 'Hotel Logo',
                    ]);

                    // Configurar logo de imagen si existe
                    if ($hotel->logo) {
                        $logoPath = $hotel->logo;
                        if (!\Str::startsWith($logoPath, ['http://', 'https://'])) {
                            $logoPath = 'storage/' . $logoPath;
                        }
                        config(['adminlte.logo_img' => $logoPath]);
                    }
                }
            }
        } catch (\Exception $e) {
            // En caso de error, mantener configuración por defecto
            \Log::warning('Error configurando AdminLTE dinámicamente: ' . $e->getMessage());
        }
        // Registrar observer para Caja
Caja::observe(CajaObserver::class);
        \App\Models\Reserva::observe(\App\Observers\ReservaObserver::class);
    }
}
