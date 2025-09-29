<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->meta_title ?? 'Preview - Landing Page' }}</title>
    <meta name="description" content="{{ $settings->meta_description ?? 'Vista previa de la landing page' }}">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('landing/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/responsive-fixes.css') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        /* Preview banner */
        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 8px 0;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        body {
            margin-top: 40px;
        }
        
        .preview-controls {
            position: fixed;
            top: 45px;
            right: 20px;
            z-index: 9998;
        }
    </style>
</head>
<body>
    <!-- Preview Banner -->
    <div class="preview-banner">
        📋 VISTA PREVIA - Landing Page
    </div>
    
    <!-- Preview Controls -->
    <div class="preview-controls">
        <a href="{{ route('admin.landing.edit') }}" class="btn btn-primary btn-sm">
            ← Volver a Editar
        </a>
    </div>

    <!-- Hero Section Preview -->
    <section class="hero" id="inicio">
        <div class="hero__carousel">
            <!-- Sample slide for preview -->
            <div class="hero__slide active">
                <div class="hero__bg">
                    <img src="{{ url('/hotel-landing/images/hero-bg.svg') }}" 
                         alt="Vista panorámica del hotel" 
                         class="hero__bg-img">
                </div>
            </div>
            
            <!-- Overlay with configured transparency -->
            <div class="hero__overlay" style="background-color: rgba(0, 0, 0, {{ $settings->hero_overlay_opacity ?? 0.4 }})"></div>
            
            <!-- Content with configured text -->
            <div class="hero__content container">
                <div class="hero__text">
                    <div class="hero__text-content active">
                        <h1 class="hero__title">{{ $settings->hero_title ?? 'Título del Hero' }}</h1>
                        <p class="hero__subtitle">{{ $settings->hero_subtitle ?? 'Subtítulo del hero section' }}</p>
                    </div>
                </div>
                
                <div class="hero__actions">
                    <button class="btn btn--primary btn--large">
                        {{ $settings->hero_cta_text ?? 'RESERVA YA' }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Sections Preview -->
    <section class="section" style="padding: 60px 0; background: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $settings->about_title ?? 'Sobre Nosotros' }}</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ Str::limit($settings->about_content, 200) ?: 'Contenido de la sección sobre nosotros...' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $settings->restaurant_title ?? 'Restaurante' }}</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ Str::limit($settings->restaurant_content, 200) ?: 'Contenido de la sección del restaurante...' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $settings->experiences_title ?? 'Experiencias' }}</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ Str::limit($settings->experiences_content, 200) ?: 'Contenido de la sección de experiencias...' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $settings->gallery_title ?? 'Galería' }}</h5>
                        </div>
                        <div class="card-body">
                            <p>Galería de imágenes del hotel</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Preview -->
    <section class="section" style="padding: 60px 0; background: #fff;">
        <div class="container">
            <h2 class="text-center mb-4">Información de Contacto</h2>
            <div class="row">
                <div class="col-md-4 text-center">
                    <h5>Teléfono</h5>
                    <p>{{ $settings->contact_phone ?: 'No configurado' }}</p>
                </div>
                <div class="col-md-4 text-center">
                    <h5>Email</h5>
                    <p>{{ $settings->contact_email ?: 'No configurado' }}</p>
                </div>
                <div class="col-md-4 text-center">
                    <h5>Dirección</h5>
                    <p>{{ Str::limit($settings->contact_address, 100) ?: 'No configurado' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SEO Info -->
    <section class="section" style="padding: 40px 0; background: #f8f9fa; border-top: 1px solid #dee2e6;">
        <div class="container">
            <h3 class="text-center mb-4">Información SEO</h3>
            <div class="row">
                <div class="col-md-4">
                    <strong>Meta Título:</strong>
                    <p class="small">{{ $settings->meta_title ?: 'No configurado' }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Meta Descripción:</strong>
                    <p class="small">{{ Str::limit($settings->meta_description, 150) ?: 'No configurado' }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Palabras Clave:</strong>
                    <p class="small">{{ $settings->meta_keywords ?: 'No configurado' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Preview Info -->
    <section class="section" style="padding: 20px 0; background: #343a40; color: white;">
        <div class="container text-center">
            <p class="mb-0">
                <strong>Configuración del Carrusel:</strong> 
                Duración: {{ $settings->hero_carousel_duration ?? 5000 }}ms | 
                Overlay: {{ ($settings->hero_overlay_opacity ?? 0.4) * 100 }}% | 
                Habitaciones: {{ $settings->rooms_per_carousel ?? 6 }} |
                Sistema: {{ $settings->booking_system ?? 'internal' }}
            </p>
        </div>
    </section>

    <script>
        // Simple button styles
        const style = document.createElement('style');
        style.textContent = `
            .btn {
                display: inline-block;
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
                font-weight: 500;
                text-align: center;
            }
            .btn-primary {
                background-color: #007bff;
                color: white;
            }
            .btn-sm {
                padding: 4px 8px;
                font-size: 14px;
            }
            .btn--primary {
                background: linear-gradient(135deg, #DC8711, #E89A20);
                color: white;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 700;
                text-transform: uppercase;
            }
            .btn--large {
                padding: 16px 32px;
                font-size: 18px;
            }
            .card {
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
            }
            .card-header {
                background: #f8f9fa;
                padding: 12px 16px;
                border-bottom: 1px solid #dee2e6;
            }
            .card-body {
                padding: 16px;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 15px;
            }
            .row {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -15px;
            }
            .col-md-4, .col-md-6 {
                flex: 1;
                padding: 0 15px;
            }
            .col-md-6 {
                flex: 0 0 50%;
            }
            .col-md-4 {
                flex: 0 0 33.333%;
            }
            .mb-4 {
                margin-bottom: 1.5rem;
            }
            .text-center {
                text-align: center;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>