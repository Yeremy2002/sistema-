<?php

namespace App\Providers;

use App\Models\Hotel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
            $view->with('hotel', \App\Models\Hotel::first());
        });
    }
}
