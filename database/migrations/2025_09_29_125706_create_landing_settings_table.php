<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();
            
            // Hero Section Configuration
            $table->string('hero_title')->default('Bienvenido a Nuestro Hotel');
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_cta_text')->default('Reservar Ahora');
            $table->string('hero_cta_link')->default('#contacto');
            $table->integer('hero_carousel_duration')->default(5000); // milliseconds
            $table->decimal('hero_overlay_opacity', 3, 2)->default(0.5);
            $table->boolean('hero_show_carousel')->default(true);
            
            // Sections Configuration
            $table->string('about_title')->default('Sobre Nosotros');
            $table->longText('about_content')->nullable();
            $table->string('about_image')->nullable();
            
            $table->string('restaurant_title')->default('Restaurante');
            $table->longText('restaurant_content')->nullable();
            $table->json('restaurant_images')->nullable();
            
            $table->string('experiences_title')->default('Experiencias');
            $table->longText('experiences_content')->nullable();
            $table->json('experiences_list')->nullable(); // Array of experiences
            
            $table->string('gallery_title')->default('Galería');
            $table->json('gallery_images')->nullable();
            
            $table->string('testimonials_title')->default('Opiniones');
            $table->json('testimonials')->nullable(); // Array of testimonials
            
            // Contact Information
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->longText('contact_address')->nullable();
            $table->longText('contact_maps_embed')->nullable();
            $table->json('social_media')->nullable();
            
            // SEO Configuration
            $table->string('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Additional Settings
            $table->boolean('is_active')->default(true);
            $table->integer('rooms_per_carousel')->default(6);
            $table->string('booking_system')->default('internal'); // internal, external
            $table->string('external_booking_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
