<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LandingSetting;

class InitializeLandingExperiences extends Command
{
    protected $signature = 'landing:init-experiences';
    protected $description = 'Inicializar experiencias por defecto en la landing page';

    public function handle()
    {
        $this->info('Inicializando experiencias por defecto...');
        
        $settings = LandingSetting::first();
        
        if (!$settings) {
            $this->error('No se encontró configuración de landing page.');
            return 1;
        }
        
        // Experiencias por defecto
        $defaultExperiences = [
            [
                'title' => 'Senderismo Guiado',
                'icon' => 'ri-mountain-line',
                'description' => 'Explora los senderos de la montaña con guías locales expertos que te mostrarán la flora y fauna única de la región.'
            ],
            [
                'title' => 'Fogatas Nocturnas',
                'icon' => 'ri-fire-line',
                'description' => 'Disfruta de noches mágicas alrededor del fuego, con historias locales y la mejor vista de las estrellas.'
            ],
            [
                'title' => 'Fotografía de Paisaje',
                'icon' => 'ri-camera-line',
                'description' => 'Captura los momentos más hermosos con nuestros tours fotográficos en los mejores miradores.'
            ],
            [
                'title' => 'Huerta Orgánica',
                'icon' => 'ri-plant-line',
                'description' => 'Conoce nuestro huerto orgánico y aprende sobre agricultura sostenible de montaña.'
            ]
        ];
        
        // Solo inicializar si no hay experiencias o está vacío
        if (!$settings->experiences_list || (is_array($settings->experiences_list) && count($settings->experiences_list) === 0)) {
            $settings->experiences_list = $defaultExperiences;
            $settings->save();
            
            $this->info('✓ Experiencias inicializadas correctamente:');
            foreach ($defaultExperiences as $exp) {
                $this->line("  - {$exp['title']}");
            }
        } else {
            $this->warn('Ya existen experiencias configuradas (' . count($settings->experiences_list) . ' experiencias).');
            $this->info('No se realizaron cambios.');
        }
        
        return 0;
    }
}
