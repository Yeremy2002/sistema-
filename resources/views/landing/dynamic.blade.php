<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landingSettings->meta_title ?? ($hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante') }}</title>
    <meta name="description" content="{{ $landingSettings->meta_description ?? 'Habitaciones acogedoras, comida de casa y vistas que enamoran en ' . ($hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante') . '. Reserva tu experiencia rústica en la montaña.' }}">
    <meta name="keywords" content="{{ $landingSettings->meta_keywords ?? 'hotel villa de leyva, hotel montaña colombia, hotel rustico boyaca, restaurante villa de leyva, turismo rural colombia, hospedaje montaña' }}">
    <meta name="author" content="{{ $hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante' }}">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' http: https:; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.google.com https://maps.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data: http: https: blob:; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self' http: https: wss:; frame-src 'self' https://www.google.com; object-src 'none'; base-uri 'self'; form-action 'self';">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}" />
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante' }} - Tu hogar en el corazón de la montaña">
    <meta property="og:description" content="Habitaciones acogedoras, comida de casa y vistas que enamoran en {{ $hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante' }}.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url('/hotel-landing/images/hero-bg.jpg') }}">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('landing/styles.css') }}" as="style" />
    <link rel="preload" href="{{ asset('landing/responsive-fixes.css') }}" as="style" />
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" as="style" />
    
    <!-- Fonts (simplified) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('landing/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/responsive-fixes.css') }}">
    
    <!-- Favicon - Fixed 404 errors -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <link rel="apple-touch-icon" href="/logo.svg">
    <!-- Removed missing PWA files to prevent 404 errors -->
    <!-- <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"> -->
    <!-- <link rel="manifest" href="/site.webmanifest"> -->
</head>
<body>
    <!-- Skip Navigation -->
    <a class="skip-link" href="#main-content">Saltar al contenido principal</a>
    <a class="skip-link" href="#nav-menu">Saltar a navegación</a>
    
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav container">
            <div class="nav__logo">
                <!-- Logo dinámico del hotel -->
                @php use Illuminate\Support\Str; @endphp
                @if ($hotel && $hotel->logo)
                    @if (Str::startsWith($hotel->logo, ['http://', 'https://']))
                        <img src="{{ $hotel->logo }}" alt="{{ $hotel->nombre ?? 'Hotel' }}" class="nav__logo-img" width="120" height="40">
                    @else
                        <img src="{{ asset('storage/' . $hotel->logo) }}" alt="{{ $hotel->nombre ?? 'Hotel' }}" class="nav__logo-img" width="120" height="40">
                    @endif
                @else
                    <img src="{{ url('/hotel-landing/images/logo.svg') }}" alt="Casa Vieja Hotel y Restaurante" class="nav__logo-img" width="120" height="40">
                @endif
            </div>
            
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="#inicio" class="nav__link">Inicio</a>
                    </li>
                    <li class="nav__item">
                        <a href="#habitaciones" class="nav__link">Habitaciones</a>
                    </li>
                    <li class="nav__item">
                        <a href="#restaurante" class="nav__link">Restaurante</a>
                    </li>
                    <li class="nav__item">
                        <a href="#experiencias" class="nav__link">Experiencias</a>
                    </li>
                    <li class="nav__item">
                        <a href="#galeria" class="nav__link">Galería</a>
                    </li>
                    <li class="nav__item">
                        <a href="#opiniones" class="nav__link">Opiniones</a>
                    </li>
                    <li class="nav__item">
                        <a href="#ubicacion" class="nav__link">Ubicación</a>
                    </li>
                    <li class="nav__item">
                        <a href="#contacto" class="nav__link">Contacto</a>
                    </li>
                </ul>
                
                <div class="nav__close" id="nav-close">
                    <i class="ri-close-line"></i>
                </div>
            </div>
            
            <!-- Overlay para cerrar menú al hacer clic fuera -->
            <div class="nav__overlay" id="nav-overlay"></div>
            
            <div class="nav__actions">
                <button class="btn btn--primary nav__cta js-open-reservation" aria-label="Abrir formulario de reserva">
                    Reserva Ya
                </button>
                
                <a href="{{ route('login') }}" class="btn btn--outline nav__login" aria-label="Acceder al sistema administrativo">
                    <i class="ri-user-line"></i>
                    Administración
                </a>
                
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-menu-line"></i>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main id="main-content">
    <section class="hero" id="inicio">
        <!-- Hero Carousel -->
        <div class="hero__carousel" id="hero-carousel">
            @forelse($heroImages as $index => $image)
                <div class="hero__slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                    <div class="hero__bg parallax-bg" data-speed="0.5">
                        <img src="{{ $image['url'] }}" 
                             alt="{{ $image['alt'] }}" 
                             class="hero__bg-img" 
                             width="1920" height="1080" 
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                    </div>
                </div>
            @empty
                <!-- Fallback si no hay imágenes -->
                <div class="hero__slide active" data-slide="0">
                    <div class="hero__bg parallax-bg" data-speed="0.5">
                        <img src="{{ url('/hotel-landing/images/hero-bg.svg') }}" 
                             alt="Vista panorámica de {{ $hotel ? $hotel->nombre : 'Casa Vieja Hotel' }}" 
                             class="hero__bg-img" width="1920" height="1080">
                    </div>
                </div>
            @endforelse
            
            <!-- Controles del carrusel -->
            @if(count($heroImages) > 1)
                <button class="hero__carousel-btn hero__carousel-btn--prev" id="hero-prev" aria-label="Imagen anterior">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <button class="hero__carousel-btn hero__carousel-btn--next" id="hero-next" aria-label="Siguiente imagen">
                    <i class="ri-arrow-right-line"></i>
                </button>
                
                <!-- Indicadores -->
                <div class="hero__indicators">
                    @foreach($heroImages as $index => $image)
                        <button class="hero__indicator {{ $index === 0 ? 'active' : '' }}" 
                                data-slide="{{ $index }}" 
                                aria-label="Ir a imagen {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Contenido del hero -->
        <div class="hero__content container">
            <div class="hero__text" id="hero-text">
                @if(count($heroImages) > 0)
                    @foreach($heroImages as $index => $image)
                        <div class="hero__text-content {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                            <h1 class="hero__title">{{ $image['title'] }}</h1>
                            <p class="hero__subtitle">{{ $image['subtitle'] }}</p>
                        </div>
                    @endforeach
                @else
                    <div class="hero__text-content active" data-slide="0">
                        <h1 class="hero__title">{{ $landingSettings->hero_title ?? ($hotel ? $hotel->nombre : 'Casa Vieja Hotel') }}</h1>
                        <p class="hero__subtitle">{{ $landingSettings->hero_subtitle ?? 'Habitaciones acogedoras, comida de casa y vistas que enamoran.' }}</p>
                    </div>
                @endif
            </div>
            
            <div class="hero__actions">
                <button class="btn btn--primary btn--large js-open-reservation" aria-label="Reservar habitación">
                    {{ $landingSettings->hero_cta_text ?? 'RESERVA YA' }}
                </button>
                <a href="https://wa.me/57XXXXXXXXX?text=Hola,%20me%20interesa%20información%20sobre%20{{ $hotel ? urlencode($hotel->nombre) : 'Casa%20Vieja%20Hotel' }}" 
                   class="btn btn--secondary btn--large" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   aria-label="Contactar por WhatsApp">
                    Escríbenos por WhatsApp
                </a>
            </div>
        </div>
        
        <div class="hero__scroll">
            <a href="#promociones" class="hero__scroll-link" aria-label="Ir a promociones">
                <span class="hero__scroll-text">Descubre más</span>
                <i class="ri-arrow-down-line"></i>
            </a>
        </div>
    </section>

    <!-- Promociones -->
    <section class="promotions section" id="promociones">
        <div class="container">
            <h2 class="section__title">Ofertas Especiales</h2>
            
            <div class="promotions__grid">
                <div class="promotion-card parallax-card" data-speed="1.0">
                    <div class="promotion-card__image">
                        <img src="{{ url('/hotel-landing/images/promo-romantic.svg') }}" alt="Fin de semana romántico" loading="lazy" width="400" height="300">
                    </div>
                    <div class="promotion-card__content">
                        <h3 class="promotion-card__title">Fin de Semana Romántico</h3>
                        <p class="promotion-card__description">Cena especial, decoración romántica y vista privilegiada</p>
                        <div class="promotion-card__price">
                            <span class="promotion-card__discount">15% OFF</span>
                            <span class="promotion-card__from">Desde $180.000</span>
                        </div>
                        <button class="btn btn--primary js-open-reservation" data-promo="romantico">
                            Reservar
                        </button>
                    </div>
                </div>
                
                <div class="promotion-card parallax-card" data-speed="1.2">
                    <div class="promotion-card__image">
                        <img src="{{ url('/hotel-landing/images/promo-family.svg') }}" alt="Plan familiar" loading="lazy" width="400" height="300">
                    </div>
                    <div class="promotion-card__content">
                        <h3 class="promotion-card__title">Plan Familiar</h3>
                        <p class="promotion-card__description">Habitaciones conectadas y actividades para toda la familia</p>
                        <div class="promotion-card__price">
                            <span class="promotion-card__discount">20% OFF</span>
                            <span class="promotion-card__from">Desde $250.000</span>
                        </div>
                        <button class="btn btn--primary js-open-reservation" data-promo="familiar">
                            Reservar
                        </button>
                    </div>
                </div>
                
                <div class="promotion-card parallax-card" data-speed="1.4">
                    <div class="promotion-card__image">
                        <img src="{{ url('/hotel-landing/images/promo-adventure.svg') }}" alt="Aventura en la montaña" loading="lazy" width="400" height="300">
                    </div>
                    <div class="promotion-card__content">
                        <h3 class="promotion-card__title">Aventura en la Montaña</h3>
                        <p class="promotion-card__description">Incluye senderismo guiado y fogata nocturna</p>
                        <div class="promotion-card__price">
                            <span class="promotion-card__discount">10% OFF</span>
                            <span class="promotion-card__from">Desde $200.000</span>
                        </div>
                        <button class="btn btn--primary js-open-reservation" data-promo="aventura">
                            Reservar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Habitaciones -->
    <section class="rooms section" id="habitaciones">
        <div class="container">
            <h2 class="section__title">Nuestras Habitaciones</h2>
            <p class="section__subtitle">Espacios diseñados para tu comodidad y descanso</p>
            
            <div class="rooms__grid">
                @forelse($habitaciones as $habitacion)
                    <div class="room-card">
                        <div class="room-card__image">
                            @php
                                $imagenPrincipal = $habitacion->imagenes->first();
                            @endphp
                            @if($imagenPrincipal && $imagenPrincipal->ruta)
                                <img src="{{ asset('storage/' . $imagenPrincipal->ruta) }}" 
                                     alt="{{ $habitacion->categoria_info->nombre ?? 'Habitación' }}" 
                                     loading="lazy" width="400" height="300">
                            @else
                                <img src="{{ url('/hotel-landing/images/room-standard.svg') }}" 
                                     alt="{{ $habitacion->categoria_info->nombre ?? 'Habitación' }}" 
                                     loading="lazy" width="400" height="300">
                            @endif
                        </div>
                        <div class="room-card__content">
                            <h3 class="room-card__title">{{ $habitacion->categoria_info->nombre ?? 'Habitación' }}</h3>
                            <div class="room-card__amenities">
                                <span class="amenity">{{ $habitacion->capacidad ?? 2 }} personas</span>
                                <span class="amenity">Baño privado</span>
                                @if($habitacion->categoria_info)
                                    <span class="amenity">{{ $habitacion->categoria_info->descripcion ?? 'Comodidades completas' }}</span>
                                @endif
                                <span class="amenity">WiFi</span>
                            </div>
                            <div class="room-card__price">
                                <span class="room-card__from">Desde</span>
                                <span class="room-card__amount">Q {{ number_format($habitacion->categoria_info->precio ?? $habitacion->precio ?? 100, 2) }}</span>
                                <span class="room-card__period">/noche</span>
                            </div>
                            <button class="btn btn--primary js-open-reservation" 
                                    data-room="{{ strtolower($habitacion->categoria_info->nombre ?? 'habitacion') }}">
                                Reservar
                            </button>
                        </div>
                    </div>
                @empty
                    <!-- Fallback con habitaciones por defecto si no hay datos -->
                    <div class="room-card">
                        <div class="room-card__image">
                            <img src="{{ url('/hotel-landing/images/room-standard.svg') }}" alt="Habitación Estándar" loading="lazy" width="400" height="300">
                        </div>
                        <div class="room-card__content">
                            <h3 class="room-card__title">Habitación Estándar</h3>
                            <div class="room-card__amenities">
                                <span class="amenity">2 personas</span>
                                <span class="amenity">Baño privado</span>
                                <span class="amenity">Vista al jardín</span>
                                <span class="amenity">WiFi</span>
                            </div>
                            <div class="room-card__price">
                                <span class="room-card__from">Desde</span>
                                <span class="room-card__amount">Q 120.00</span>
                                <span class="room-card__period">/noche</span>
                            </div>
                            <button class="btn btn--primary js-open-reservation" data-room="estandar">
                                Reservar
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    
    <!-- Restaurante -->
    <section class="restaurant section" id="restaurante">
        <div class="container">
            <h2 class="section__title">Restaurante Casa Vieja</h2>
            <p class="section__subtitle">Sabores auténticos de la montaña</p>
            
            <div class="restaurant__content">
                <div class="restaurant__info">
                    <h3>Cocina Tradicional</h3>
                    <p>Disfruta de los sabores auténticos de la cocina de montaña, preparados con ingredientes frescos de la región. Nuestro menú combina tradición y creatividad para ofrecerte una experiencia gastronómica única.</p>
                    <div class="restaurant__features">
                        <div class="feature">
                            <i class="ri-restaurant-line"></i>
                            <span>Especialidades regionales</span>
                        </div>
                        <div class="feature">
                            <i class="ri-leaf-line"></i>
                            <span>Ingredientes frescos</span>
                        </div>
                        <div class="feature">
                            <i class="ri-fire-line"></i>
                            <span>Fogata tradicional</span>
                        </div>
                    </div>
                </div>
                <div class="restaurant__image">
                    <img src="{{ url('/hotel-landing/images/restaurant.svg') }}" alt="Restaurante Casa Vieja" loading="lazy">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Experiencias -->
    <section class="experiences section" id="experiencias">
        <div class="container">
            <h2 class="section__title">Experiencias Únicas</h2>
            <p class="section__subtitle">Vive la montaña de manera auténtica</p>
            
            <div class="experiences__grid">
                <div class="experience-card">
                    <div class="experience-card__icon">
                        <i class="ri-mountain-line"></i>
                    </div>
                    <h3 class="experience-card__title">Senderismo Guiado</h3>
                    <p class="experience-card__description">Explora los senderos de la montaña con guías locales expertos que te mostrarán la flora y fauna única de la región.</p>
                </div>
                
                <div class="experience-card">
                    <div class="experience-card__icon">
                        <i class="ri-fire-line"></i>
                    </div>
                    <h3 class="experience-card__title">Fogatas Nocturnas</h3>
                    <p class="experience-card__description">Disfruta de noches mágicas alrededor del fuego, con historias locales y la mejor vista de las estrellas.</p>
                </div>
                
                <div class="experience-card">
                    <div class="experience-card__icon">
                        <i class="ri-camera-line"></i>
                    </div>
                    <h3 class="experience-card__title">Fotografía de Paisaje</h3>
                    <p class="experience-card__description">Captura los momentos más hermosos con nuestros tours fotográficos en los mejores miradores.</p>
                </div>
                
                <div class="experience-card">
                    <div class="experience-card__icon">
                        <i class="ri-plant-line"></i>
                    </div>
                    <h3 class="experience-card__title">Huerta Orgánica</h3>
                    <p class="experience-card__description">Conoce nuestro huerto orgánico y aprende sobre agricultura sostenible de montaña.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Galería -->
    <section class="gallery section" id="galeria">
        <div class="container">
            <h2 class="section__title">Galería</h2>
            <p class="section__subtitle">Descubre la belleza de Casa Vieja</p>
            
            <div class="gallery__grid">
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-1.svg') }}" alt="Vista panorámica" loading="lazy">
                </div>
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-2.svg') }}" alt="Habitación" loading="lazy">
                </div>
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-3.svg') }}" alt="Restaurante" loading="lazy">
                </div>
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-4.svg') }}" alt="Sendero" loading="lazy">
                </div>
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-5.svg') }}" alt="Fogata" loading="lazy">
                </div>
                <div class="gallery__item">
                    <img src="{{ url('/hotel-landing/images/gallery-6.svg') }}" alt="Vista nocturna" loading="lazy">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Opiniones -->
    <section class="testimonials section" id="opiniones">
        <div class="container">
            <h2 class="section__title">Lo que dicen nuestros huéspedes</h2>
            <p class="section__subtitle">Experiencias reales de quienes nos han visitado</p>
            
            <div class="testimonials__grid">
                <div class="testimonial-card">
                    <div class="testimonial__content">
                        <div class="testimonial__stars">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                        </div>
                        <p class="testimonial__text">"Una experiencia increíble. El lugar es hermoso, la atención excepcional y la comida deliciosa. Sin duda regresaremos."</p>
                    </div>
                    <div class="testimonial__author">
                        <img src="{{ url('/hotel-landing/images/testimonial-1.svg') }}" alt="María Gómez" class="testimonial__avatar">
                        <div class="testimonial__info">
                            <h4 class="testimonial__name">María Gómez</h4>
                            <span class="testimonial__location">Ciudad de Guatemala</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial__content">
                        <div class="testimonial__stars">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                        </div>
                        <p class="testimonial__text">"Perfecto para desconectar de la ciudad. Las vistas son espectaculares y el ambiente muy acogedor."</p>
                    </div>
                    <div class="testimonial__author">
                        <img src="{{ url('/hotel-landing/images/testimonial-2.svg') }}" alt="Carlos Rodríguez" class="testimonial__avatar">
                        <div class="testimonial__info">
                            <h4 class="testimonial__name">Carlos Rodríguez</h4>
                            <span class="testimonial__location">Antigua Guatemala</span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial__content">
                        <div class="testimonial__stars">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                        </div>
                        <p class="testimonial__text">"Un lugar mágico para pasar tiempo en familia. Los niños disfrutaron mucho las actividades al aire libre."</p>
                    </div>
                    <div class="testimonial__author">
                        <img src="{{ url('/hotel-landing/images/testimonial-3.svg') }}" alt="Ana Morales" class="testimonial__avatar">
                        <div class="testimonial__info">
                            <h4 class="testimonial__name">Ana Morales</h4>
                            <span class="testimonial__location">Quetzaltenango</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Ubicación y Contacto -->
    <section class="location section" id="ubicacion">
        <div class="container">
            <h2 class="section__title">Ubícanos</h2>
            <p class="section__subtitle">En el corazón de la montaña guatemalteca</p>
            
            <div class="location__content">
                <div class="location__info">
                    <div class="location__details">
                        <div class="detail">
                            <i class="ri-map-pin-line"></i>
                            <div>
                                <h4>Dirección</h4>
                                <p>Km 15 Carretera a la Montaña<br>San Lucas Sacatepéquez, Guatemala</p>
                            </div>
                        </div>
                        
                        <div class="detail">
                            <i class="ri-phone-line"></i>
                            <div>
                                <h4>Teléfono</h4>
                                <p>+502 1234-5678<br>+502 8765-4321</p>
                            </div>
                        </div>
                        
                        <div class="detail">
                            <i class="ri-mail-line"></i>
                            <div>
                                <h4>Email</h4>
                                <p>info@casaviejahotel.com<br>reservas@casaviejahotel.com</p>
                            </div>
                        </div>
                        
                        <div class="detail">
                            <i class="ri-time-line"></i>
                            <div>
                                <h4>Horarios</h4>
                                <p>Check-in: 3:00 PM<br>Check-out: 12:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="location__map">
                    <!-- Placeholder para mapa -->
                    <div class="map-placeholder">
                        <i class="ri-map-2-line"></i>
                        <p>Mapa interactivo</p>
                        <small>Disponible próximamente</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contacto -->
    <section class="contact section" id="contacto">
        <div class="container">
            <h2 class="section__title">Contáctanos</h2>
            <p class="section__subtitle">Estamos listos para hacer tu estadía inolvidable</p>
            
            <div class="contact__content">
                <div class="contact__info">
                    <h3>Hablemos de tu próxima aventura</h3>
                    <p>Nuestro equipo está disponible para ayudarte a planificar la escapada perfecta a la montaña.</p>
                    
                    <div class="contact__methods">
                        <a href="tel:+50212345678" class="contact__method">
                            <i class="ri-phone-line"></i>
                            <div>
                                <h4>Llámanos</h4>
                                <span>+502 1234-5678</span>
                            </div>
                        </a>
                        
                        <a href="https://wa.me/502XXXXXXXX" class="contact__method" target="_blank">
                            <i class="ri-whatsapp-line"></i>
                            <div>
                                <h4>WhatsApp</h4>
                                <span>Mensaje directo</span>
                            </div>
                        </a>
                        
                        <a href="mailto:info@casaviejahotel.com" class="contact__method">
                            <i class="ri-mail-line"></i>
                            <div>
                                <h4>Email</h4>
                                <span>info@casaviejahotel.com</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="contact__cta">
                    <h3>¡Tu aventura te espera!</h3>
                    <p>No esperes más para vivir una experiencia única en el corazón de la montaña guatemalteca.</p>
                    <button class="btn btn--primary btn--large js-open-reservation">
                        Reservar Ahora
                    </button>
                </div>
            </div>
        </div>
    </section>
    
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__section">
                    <!-- Logo dinámico en footer -->
                    @if ($hotel && $hotel->logo)
                        @if (Str::startsWith($hotel->logo, ['http://', 'https://']))
                            <img src="{{ $hotel->logo }}" alt="{{ $hotel->nombre ?? 'Hotel' }}" class="footer__logo" width="120" height="40">
                        @else
                            <img src="{{ asset('storage/' . $hotel->logo) }}" alt="{{ $hotel->nombre ?? 'Hotel' }}" class="footer__logo" width="120" height="40">
                        @endif
                    @else
                        <img src="{{ url('/hotel-landing/images/logo.svg') }}" alt="Casa Vieja Hotel y Restaurante" class="footer__logo" width="120" height="40">
                    @endif
                    <p class="footer__description">
                        Tu hogar en el corazón de la montaña. Experiencias auténticas en un ambiente rústico y acogedor.
                    </p>
                </div>
                
                <div class="footer__section">
                    <h3 class="footer__title">Enlaces Rápidos</h3>
                    <ul class="footer__links">
                        <li><a href="#habitaciones">Habitaciones</a></li>
                        <li><a href="#restaurante">Restaurante</a></li>
                        <li><a href="#experiencias">Experiencias</a></li>
                        <li><a href="#galeria">Galería</a></li>
                    </ul>
                </div>
                
                <div class="footer__section">
                    <h3 class="footer__title">Contacto</h3>
                    <ul class="footer__contact">
                        <li>+57 (8) 123-4567</li>
                        <li>info@{{ $hotel ? strtolower(str_replace(' ', '', $hotel->nombre)) : 'casaviejahotel' }}.com</li>
                        <li>Vereda La Montaña, Km 15</li>
                    </ul>
                </div>
                
                <div class="footer__section">
                    <h3 class="footer__title">Síguenos</h3>
                    <div class="footer__social">
                        <a href="#" class="footer__social-link" aria-label="Facebook">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="#" class="footer__social-link" aria-label="Instagram">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="#" class="footer__social-link" aria-label="TripAdvisor">
                            <i class="ri-map-pin-line"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer__bottom">
                <p>&copy; 2024 {{ $hotel ? $hotel->nombre : 'Casa Vieja Hotel y Restaurante' }}. Todos los derechos reservados.</p>
                <div class="footer__legal">
                    <a href="#">Política de Privacidad</a>
                    <a href="#">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating CTA -->
    <div class="floating-cta" id="floating-cta">
        <button class="floating-cta__btn js-open-reservation" aria-label="Reservar ahora">
            <i class="ri-calendar-line"></i>
            <span>Reserva Ya</span>
        </button>
    </div>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/57XXXXXXXXX?text=Hola,%20me%20interesa%20información%20sobre%20{{ $hotel ? urlencode($hotel->nombre) : 'Casa%20Vieja%20Hotel' }}" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer"
       aria-label="Contactar por WhatsApp">
        <i class="ri-whatsapp-fill"></i>
    </a>

    <!-- Reservation Modal -->
    <div class="modal" id="reservation-modal">
        <div class="modal__overlay js-close-modal"></div>
        <div class="modal__content">
            <div class="modal__header">
                <h3 class="modal__title">Reserva tu Estadía</h3>
                <button class="modal__close js-close-modal" aria-label="Cerrar modal">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            
            <form class="reservation-form" id="reservation-form">
                <div class="form__row">
                    <div class="form__group">
                        <label for="checkin" class="form__label">Fecha de llegada</label>
                        <input type="date" id="checkin" name="checkin" class="form__input" required>
                    </div>
                    
                    <div class="form__group">
                        <label for="checkout" class="form__label">Fecha de salida</label>
                        <input type="date" id="checkout" name="checkout" class="form__input" required>
                    </div>
                </div>
                
                <div class="form__row">
                    <div class="form__group">
                        <label for="guests" class="form__label">Número de huéspedes</label>
                        <select id="guests" name="guests" class="form__select" required>
                            <option value="">Seleccionar</option>
                            <option value="1">1 huésped</option>
                            <option value="2">2 huéspedes</option>
                            <option value="3">3 huéspedes</option>
                            <option value="4">4 huéspedes</option>
                            <option value="5">5 huéspedes</option>
                            <option value="6">6 huéspedes</option>
                        </select>
                    </div>
                    
                    <div class="form__group">
                        <label for="room-type" class="form__label">Tipo de habitación</label>
                        <select id="room-type" name="room-type" class="form__select" required>
                            <option value="">Seleccionar</option>
                            <option value="estandar">Habitación Estándar</option>
                            <option value="deluxe">Habitación Deluxe</option>
                            <option value="suite">Suite Familiar</option>
                        </select>
                    </div>
                </div>
                
                <div class="form__group">
                    <label for="guest-name" class="form__label">Nombre completo</label>
                    <input type="text" id="guest-name" name="guest-name" class="form__input" required>
                </div>
                
                <div class="form__group">
                    <label for="guest-email" class="form__label">Correo electrónico</label>
                    <input type="email" id="guest-email" name="guest-email" class="form__input" placeholder="ejemplo@correo.com" required>
                </div>
                
                <div class="form__group">
                    <label for="guest-phone" class="form__label">Teléfono</label>
                    <input type="tel" id="guest-phone" name="guest-phone" class="form__input" placeholder="(+502) 8888-7867" required>
                </div>
                
                <div class="form__group">
                    <label for="special-requests" class="form__label">Solicitudes especiales (opcional)</label>
                    <textarea id="special-requests" name="special-requests" class="form__textarea" rows="3"></textarea>
                </div>
                
                <!-- Price Summary -->
                <div class="price-summary" id="price-summary" style="display: none;">
                    <div class="price-summary__content">
                        <h4 class="price-summary__title">Resumen de Reserva</h4>
                        <div class="price-summary__details">
                            <div class="price-row">
                                <span>Habitación:</span>
                                <span id="price-room-type">-</span>
                            </div>
                            <div class="price-row">
                                <span>Fechas:</span>
                                <span id="price-dates">-</span>
                            </div>
                            <div class="price-row">
                                <span>Noches:</span>
                                <span id="price-nights">-</span>
                            </div>
                            <div class="price-row">
                                <span>Precio por noche:</span>
                                <span id="price-per-night">-</span>
                            </div>
                            <div class="price-row price-row--total">
                                <span>Total estimado:</span>
                                <span id="price-total">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form__actions">
                    <button type="button" class="btn btn--secondary js-close-modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn--primary">
                        Confirmar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RemixIcon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts -->
    <script>
        // Configuración del carrusel desde base de datos
        window.HERO_CAROUSEL_CONFIG = {
            duration: {{ $landingSettings->hero_carousel_duration ?? 5000 }},
            showCarousel: {{ $landingSettings->hero_show_carousel ? 'true' : 'false' }}
        };
    </script>
    <script src="{{ asset('landing/hero-carousel.js') }}"></script>
    <script src="{{ asset('landing/navigation-fixes.js') }}"></script>
    <script src="{{ asset('landing/navigation-fixes.js') }}"></script>
    <script src="{{ asset('landing/hero-carousel.js') }}"></script>
    <script src="{{ asset('landing/script.js') }}"></script>
    <script src="{{ asset('hotel-landing/main.js') }}"></script>
</body>
</html>
