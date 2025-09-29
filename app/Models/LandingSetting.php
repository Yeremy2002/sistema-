<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandingSetting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        // Hero Section
        'hero_title',
        'hero_subtitle',
        'hero_cta_text',
        'hero_cta_link',
        'hero_carousel_duration',
        'hero_overlay_opacity',
        'hero_show_carousel',
        
        // Sections
        'about_title',
        'about_content',
        'about_image',
        'restaurant_title',
        'restaurant_content',
        'restaurant_images',
        'experiences_title',
        'experiences_content',
        'experiences_list',
        'gallery_title',
        'gallery_images',
        'testimonials_title',
        'testimonials',
        
        // Contact
        'contact_phone',
        'contact_email',
        'contact_address',
        'contact_maps_embed',
        'social_media',
        
        // SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        
        // Additional
        'is_active',
        'rooms_per_carousel',
        'booking_system',
        'external_booking_url'
    ];
    
    protected $casts = [
        'restaurant_images' => 'array',
        'experiences_list' => 'array',
        'gallery_images' => 'array',
        'testimonials' => 'array',
        'social_media' => 'array',
        'hero_show_carousel' => 'boolean',
        'is_active' => 'boolean',
        'hero_overlay_opacity' => 'decimal:2',
        'hero_carousel_duration' => 'integer',
        'rooms_per_carousel' => 'integer'
    ];
    
    /**
     * Get the active landing settings
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first() ?? self::getDefault();
    }
    
    /**
     * Get default settings if none exist
     */
    public static function getDefault()
    {
        return new self([
            'hero_title' => 'Bienvenido a Nuestro Hotel',
            'hero_subtitle' => 'Experimenta la comodidad y elegancia en cada detalle',
            'hero_cta_text' => 'Reservar Ahora',
            'hero_cta_link' => '#contacto',
            'hero_carousel_duration' => 5000,
            'hero_overlay_opacity' => 0.5,
            'hero_show_carousel' => true,
            'about_title' => 'Sobre Nosotros',
            'restaurant_title' => 'Restaurante',
            'experiences_title' => 'Experiencias',
            'gallery_title' => 'Galería',
            'testimonials_title' => 'Opiniones de Nuestros Huéspedes',
            'is_active' => true,
            'rooms_per_carousel' => 6,
            'booking_system' => 'internal'
        ]);
    }
    
    /**
     * Create or update settings
     */
    public static function updateSettings($data)
    {
        $settings = self::first();
        
        if ($settings) {
            $settings->update($data);
        } else {
            $settings = self::create($data);
        }
        
        return $settings;
    }
}
