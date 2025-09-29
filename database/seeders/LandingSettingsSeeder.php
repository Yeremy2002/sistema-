<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LandingSetting;

class LandingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LandingSetting::create([
            // Hero Section
            'hero_title' => 'Bienvenido a Casa Vieja Hotel',
            'hero_subtitle' => 'Experimenta la comodidad y elegancia en el corazón de la montaña. Un lugar donde la naturaleza y el confort se encuentran para brindarte una experiencia inolvidable.',
            'hero_cta_text' => 'Reservar Ahora',
            'hero_cta_link' => '#contacto',
            'hero_carousel_duration' => 5000,
            'hero_overlay_opacity' => 0.4,
            'hero_show_carousel' => true,
            
            // Sections
            'about_title' => 'Sobre Nosotros',
            'about_content' => 'Casa Vieja Hotel es más que un lugar de hospedaje; es tu hogar lejos de casa. Ubicado en un entorno natural privilegiado, ofrecemos habitaciones cómodas y acogedoras, junto con un servicio personalizado que hace de cada visita una experiencia única.',
            'restaurant_title' => 'Restaurante',
            'restaurant_content' => 'Disfruta de una experiencia gastronómica única en nuestro restaurante. Ofrecemos una selección de platos tradicionales y contemporáneos, preparados con ingredientes frescos de la región.',
            'experiences_title' => 'Experiencias',
            'experiences_content' => 'Descubre las maravillas que te esperan en cada rincón. Desde caminatas por senderos naturales hasta momentos de relajación en espacios diseñados para tu comodidad.',
            'gallery_title' => 'Galería',
            'testimonials_title' => 'Opiniones de Nuestros Huéspedes',
            
            // Contact
            'contact_phone' => '+502 1234-5678',
            'contact_email' => 'info@casaviejahotel.com',
            'contact_address' => 'Casa Vieja Hotel\nDirección del hotel\nCiudad, País',
            
            // SEO
            'meta_title' => 'Casa Vieja Hotel - Tu hogar en la montaña',
            'meta_description' => 'Hospedaje cómodo y acogedor en un entorno natural privilegiado. Habitaciones con todas las comodidades y servicio personalizado.',
            'meta_keywords' => 'hotel, hospedaje, montaña, turismo, habitaciones, restaurante, naturaleza',
            
            // Settings
            'is_active' => true,
            'rooms_per_carousel' => 6,
            'booking_system' => 'internal',
            
            // Initial JSON data
            'experiences_list' => [
                [
                    'title' => 'Senderismo Natural',
                    'description' => 'Explora senderos naturales con vistas espectaculares',
                    'icon' => 'fas fa-mountain'
                ],
                [
                    'title' => 'Zona de Relajación',
                    'description' => 'Espacios tranquilos para descansar y meditar',
                    'icon' => 'fas fa-leaf'
                ],
                [
                    'title' => 'Experiencia Gastronómica',
                    'description' => 'Sabores auténticos de la región',
                    'icon' => 'fas fa-utensils'
                ]
            ],
            'testimonials' => [
                [
                    'name' => 'María González',
                    'comment' => 'Una experiencia maravillosa. El servicio es excelente y las habitaciones muy cómodas.',
                    'rating' => 5
                ],
                [
                    'name' => 'Carlos Rodríguez',
                    'comment' => 'El lugar perfecto para desconectarse y disfrutar de la naturaleza.',
                    'rating' => 5
                ],
                [
                    'name' => 'Ana López',
                    'comment' => 'Recomiendo este hotel. La atención es personalizada y el ambiente muy acogedor.',
                    'rating' => 5
                ]
            ],
            'social_media' => [
                'facebook' => '#',
                'instagram' => '#',
                'twitter' => '#'
            ]
        ]);
    }
}
