<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LandingSetting;

class FixLandingTestimonials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landing:fix-testimonials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix testimonials field in landing_settings table to ensure it is a proper JSON array';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Corrigiendo campo testimonials...');
        
        $settings = LandingSetting::first();
        
        if (!$settings) {
            $this->error('No se encontró configuración de landing page.');
            return 1;
        }
        
        // Si testimonials es string, intentar decodificar o resetear a array vacío
        if (is_string($settings->testimonials)) {
            $this->warn('testimonials es un string, intentando corregir...');
            
            // Intentar decodificar si es JSON válido
            $decoded = json_decode($settings->testimonials, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Ya es JSON válido, solo necesitamos forzar el cast
                $this->info('JSON válido encontrado, actualizando...');
                $settings->testimonials = $decoded;
            } else {
                // No es JSON válido, resetear a array vacío
                $this->warn('JSON inválido, reseteando a array vacío...');
                $settings->testimonials = [];
            }
            
            $settings->save();
            $this->info('¡Campo testimonials corregido exitosamente!');
        } elseif (is_array($settings->testimonials)) {
            $this->info('testimonials ya es un array. Todo correcto.');
        } elseif (is_null($settings->testimonials)) {
            $this->warn('testimonials es NULL, estableciendo array vacío...');
            $settings->testimonials = [];
            $settings->save();
            $this->info('¡Campo testimonials inicializado!');
        } else {
            $this->error('tipo de dato desconocido para testimonials: ' . gettype($settings->testimonials));
            return 1;
        }
        
        // Verificar el resultado
        $settings->refresh();
        $this->info("\nEstado final:");
        $this->line('Tipo: ' . gettype($settings->testimonials));
        
        if (is_array($settings->testimonials)) {
            $this->line('Cantidad de testimonios: ' . count($settings->testimonials));
            $this->info('✓ Campo corregido correctamente');
        }
        
        return 0;
    }
}
