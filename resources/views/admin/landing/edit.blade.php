@extends('adminlte::page')

@section('title', 'Editar Landing Page')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Configuración de Landing Page
                    </h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.landing.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                        <a href="{{ route('landing.index') }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-eye mr-1"></i> Vista Previa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle mr-2"></i>Errores de validación:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.landing.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Navigation tabs -->
                        <ul class="nav nav-tabs" id="landingTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="hero-tab" data-toggle="tab" href="#hero" role="tab" aria-controls="hero" aria-selected="true">
                                    <i class="fas fa-banner mr-1"></i>Hero
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="sections-tab" data-toggle="tab" href="#sections" role="tab" aria-controls="sections" aria-selected="false">
                                    <i class="fas fa-list mr-1"></i>Secciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="experiences-tab" data-toggle="tab" href="#experiences" role="tab" aria-controls="experiences" aria-selected="false">
                                    <i class="fas fa-hiking mr-1"></i>Experiencias
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="gallery-tab" data-toggle="tab" href="#gallery" role="tab" aria-controls="gallery" aria-selected="false">
                                    <i class="fas fa-images mr-1"></i>Galería
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="testimonials-tab" data-toggle="tab" href="#testimonials" role="tab" aria-controls="testimonials" aria-selected="false">
                                    <i class="fas fa-comments mr-1"></i>Testimonios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">
                                    <i class="fas fa-address-book mr-1"></i>Contacto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="seo" aria-selected="false">
                                    <i class="fas fa-search mr-1"></i>SEO
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="landingTabsContent">
                            <!-- Hero Tab -->
                            <div class="tab-pane fade show active" id="hero" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Configuración del Hero</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="hero_title" class="form-label">Título Principal *</label>
                                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                                value="{{ old('hero_title', $settings->hero_title) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="hero_cta_text" class="form-label">Texto del Botón *</label>
                                                            <input type="text" class="form-control" id="hero_cta_text" name="hero_cta_text" 
                                                                value="{{ old('hero_cta_text', $settings->hero_cta_text) }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="hero_subtitle" class="form-label">Subtítulo</label>
                                                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="2">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="hero_cta_link" class="form-label">Enlace del Botón *</label>
                                                            <input type="text" class="form-control" id="hero_cta_link" name="hero_cta_link" 
                                                                value="{{ old('hero_cta_link', $settings->hero_cta_link) }}" required>
                                                            <div class="form-text">Ej: #contacto, /reservas, etc.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="hero_carousel_duration" class="form-label">Duración del Carrusel (ms) *</label>
                                                            <input type="number" class="form-control" id="hero_carousel_duration" name="hero_carousel_duration" 
                                                                value="{{ old('hero_carousel_duration', $settings->hero_carousel_duration) }}" min="1000" max="10000" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="hero_overlay_opacity" class="form-label">Opacidad del Overlay *</label>
                                                            <input type="range" class="form-range" id="hero_overlay_opacity" name="hero_overlay_opacity" 
                                                                min="0" max="1" step="0.1" value="{{ old('hero_overlay_opacity', $settings->hero_overlay_opacity) }}">
                                                            <div class="form-text">Valor actual: <span id="opacity-value">{{ $settings->hero_overlay_opacity }}</span></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="rooms_per_carousel" class="form-label">Habitaciones en Carrusel *</label>
                                                            <input type="number" class="form-control" id="rooms_per_carousel" name="rooms_per_carousel" 
                                                                value="{{ old('rooms_per_carousel', $settings->rooms_per_carousel) }}" min="1" max="12" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="hero_show_carousel" name="hero_show_carousel" value="1" 
                                                            {{ old('hero_show_carousel', $settings->hero_show_carousel) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="hero_show_carousel">
                                                            Mostrar carrusel de habitaciones
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Vista Previa</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="hero-preview p-3 bg-dark text-white rounded position-relative">
                                                    <div class="overlay" style="background-color: rgba(0,0,0,{{ $settings->hero_overlay_opacity }}); position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 0.375rem;"></div>
                                                    <div class="position-relative">
                                                        <h5 id="preview-title">{{ $settings->hero_title }}</h5>
                                                        <p id="preview-subtitle" class="mb-3">{{ $settings->hero_subtitle }}</p>
                                                        <button type="button" id="preview-cta" class="btn btn-primary btn-sm">{{ $settings->hero_cta_text }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sections Tab -->
                            <div class="tab-pane fade" id="sections" role="tabpanel">
                                <div class="row">
                                    <!-- About Section -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Sección "Sobre Nosotros"</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="about_title" class="form-label">Título *</label>
                                                    <input type="text" class="form-control" id="about_title" name="about_title" 
                                                        value="{{ old('about_title', $settings->about_title) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="about_content" class="form-label">Contenido</label>
                                                    <textarea class="form-control" id="about_content" name="about_content" rows="4">{{ old('about_content', $settings->about_content) }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="about_image" class="form-label">Imagen</label>
                                                    <input type="file" class="form-control" id="about_image" name="about_image" accept="image/*">
                                                    @if($settings->about_image)
                                                        <div class="mt-2">
                                                            <img src="{{ Storage::url($settings->about_image) }}" alt="Imagen actual" class="img-thumbnail" style="max-height: 100px;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Restaurant Section -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Sección Restaurante</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="restaurant_title" class="form-label">Título *</label>
                                                    <input type="text" class="form-control" id="restaurant_title" name="restaurant_title" 
                                                        value="{{ old('restaurant_title', $settings->restaurant_title) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="restaurant_content" class="form-label">Contenido</label>
                                                    <textarea class="form-control" id="restaurant_content" name="restaurant_content" rows="4">{{ old('restaurant_content', $settings->restaurant_content) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Experiences Section -->
                                    <div class="col-md-6 mt-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Sección Experiencias</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="experiences_title" class="form-label">Título *</label>
                                                    <input type="text" class="form-control" id="experiences_title" name="experiences_title" 
                                                        value="{{ old('experiences_title', $settings->experiences_title) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="experiences_content" class="form-label">Contenido</label>
                                                    <textarea class="form-control" id="experiences_content" name="experiences_content" rows="4">{{ old('experiences_content', $settings->experiences_content) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Gallery Section -->
                                    <div class="col-md-6 mt-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Sección Galería</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="gallery_title" class="form-label">Título *</label>
                                                    <input type="text" class="form-control" id="gallery_title" name="gallery_title" 
                                                        value="{{ old('gallery_title', $settings->gallery_title) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Testimonials Section -->
                                    <div class="col-md-12 mt-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Sección Testimonios</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="testimonials_title" class="form-label">Título *</label>
                                                    <input type="text" class="form-control" id="testimonials_title" name="testimonials_title" 
                                                        value="{{ old('testimonials_title', $settings->testimonials_title) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Experiences Tab -->
                            <div class="tab-pane fade" id="experiences" role="tabpanel">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Gestión de Experiencias Únicas</h6>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addExperience()">
                                            <i class="fas fa-plus mr-1"></i> Agregar Experiencia
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Agrega las experiencias únicas que ofrece tu hotel. Cada experiencia se mostrará como una tarjeta en la landing page.</p>
                                        
                                        <div id="experiences-list">
                                            @if($settings->experiences_list && is_array($settings->experiences_list))
                                                @foreach($settings->experiences_list as $index => $experience)
                                                <div class="experience-item border p-3 mb-3 rounded" data-index="{{ $index }}">
                                                    <div class="row">
                                                        <div class="col-md-11">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="mb-2">
                                                                        <label class="form-label">Título</label>
                                                                        <input type="text" class="form-control" name="experiences_list[{{ $index }}][title]" 
                                                                            value="{{ $experience['title'] ?? '' }}" placeholder="Ej: Senderismo Guiado">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-2">
                                                                        <label class="form-label">Ícono (Remix Icon)</label>
                                                                        <input type="text" class="form-control" name="experiences_list[{{ $index }}][icon]" 
                                                                            value="{{ $experience['icon'] ?? '' }}" placeholder="Ej: ri-mountain-line">
                                                                        <small class="text-muted"><a href="https://remixicon.com" target="_blank">Ver iconos</a></small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-2">
                                                                        <label class="form-label">Vista Previa</label>
                                                                        <div class="border p-2 rounded text-center">
                                                                            <i class="{{ $experience['icon'] ?? 'ri-star-line' }}" style="font-size: 24px;"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label class="form-label">Descripción</label>
                                                                    <textarea class="form-control" name="experiences_list[{{ $index }}][description]" rows="2" 
                                                                        placeholder="Describe esta experiencia única...">{{ $experience['description'] ?? '' }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-center justify-content-center">
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeExperience({{ $index }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        
                                        <div class="alert alert-info mt-3">
                                            <h6><i class="fas fa-info-circle mr-2"></i>Íconos Sugeridos:</h6>
                                            <div class="d-flex flex-wrap">
                                                <span class="badge bg-secondary mr-2 mb-2">ri-mountain-line (Montaña)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-fire-line (Fogata)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-camera-line (Fotografía)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-plant-line (Huerto)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-bike-line (Ciclismo)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-moon-line (Astronomía)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-restaurant-line (Gastronomía)</span>
                                                <span class="badge bg-secondary mr-2 mb-2">ri-footprint-line (Senderismo)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gallery Tab -->
                            <div class="tab-pane fade" id="gallery" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Gestión de Galería</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            La gestión de imágenes de galería se implementará en una siguiente fase.
                                            Por ahora las imágenes se obtienen automáticamente de las habitaciones registradas.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Testimonials Tab -->
                            <div class="tab-pane fade" id="testimonials" role="tabpanel">
                                <div class="row">
                                    <!-- Configuración General -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Configuración de Testimonios</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="testimonials_title" class="form-label">Título de la Sección *</label>
                                                    <input type="text" class="form-control" id="testimonials_title" name="testimonials_title" 
                                                        value="{{ old('testimonials_title', $settings->testimonials_title) }}" required>
                                                </div>
                                                
                                                <div class="alert alert-success">
                                                    <h6><i class="fab fa-facebook mr-2"></i>Integración Facebook</h6>
                                                    <p class="mb-2">Los comentarios de Facebook se mostrarán automáticamente en tu landing page.</p>
                                                    <p class="mb-0"><strong>Configuración actual:</strong></p>
                                                    <ul class="mb-0">
                                                        <li>Plugin: Facebook Comments</li>
                                                        <li>Comentarios mostrados: 10</li>
                                                        <li>Orden: Más recientes primero</li>
                                                        <li>Moderación: Automática por Facebook</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Testimonios Destacados -->
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Testimonios Destacados</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted">Testimonios que aparecerán arriba de los comentarios de Facebook:</p>
                                                
                                                <div id="testimonials-list">
                                                    @if($settings->testimonials && is_array($settings->testimonials))
                                                        @foreach($settings->testimonials as $index => $testimonial)
                                                        <div class="testimonial-item border p-3 mb-2 rounded">
                                                            <div class="row">
                                                                <div class="col-md-8">
                                                                    <strong>{{ $testimonial['name'] ?? 'Sin nombre' }}</strong>
                                                                    <br><small class="text-muted">{{ Str::limit($testimonial['comment'] ?? 'Sin comentario', 60) }}</small>
                                                                </div>
                                                                <div class="col-md-4 text-right">
                                                                    <div class="stars">
                                                                        @for($i = 0; $i < ($testimonial['rating'] ?? 5); $i++)
                                                                            <i class="fas fa-star text-warning"></i>
                                                                        @endfor
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeTestimonial({{ $index }})">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                
                                                <button type="button" class="btn btn-success btn-sm" onclick="addTestimonial()">
                                                    <i class="fas fa-plus mr-1"></i> Agregar Testimonio
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Vista Previa Facebook -->
                                    <div class="col-md-12 mt-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="fab fa-facebook mr-2"></i>Vista Previa - Comentarios de Facebook</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Vista previa del plugin de Facebook:</strong><br>
                                                    Los comentarios de Facebook aparecerán debajo de los testimonios destacados en tu landing page.
                                                    Los usuarios podrán comentar directamente desde Facebook y aparecerán automáticamente.
                                                </div>
                                                
                                                <div class="facebook-preview bg-light p-3 rounded">
                                                    <h6>Cómo se verá:</h6>
                                                    <div class="border p-2 bg-white rounded">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fab fa-facebook text-primary mr-2"></i>
                                                            <strong>Comentarios de Facebook</strong>
                                                        </div>
                                                        <small class="text-muted">Los comentarios reales aparecerán aquí cuando los usuarios comenten en tu página.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hidden inputs para testimonials JSON -->
                                <input type="hidden" name="testimonials" id="testimonials-data" 
                                       value="{{ old('testimonials', json_encode($settings->testimonials ?? [])) }}">
                            </div>

                            <!-- Contact Tab -->
                            <div class="tab-pane fade" id="contact" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Información de Contacto</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="contact_phone" class="form-label">Teléfono</label>
                                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                                        value="{{ old('contact_phone', $settings->contact_phone) }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="contact_email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                                        value="{{ old('contact_email', $settings->contact_email) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="contact_address" class="form-label">Dirección</label>
                                                    <textarea class="form-control" id="contact_address" name="contact_address" rows="3">{{ old('contact_address', $settings->contact_address) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_maps_embed" class="form-label">Código embed de Google Maps</label>
                                            <textarea class="form-control" id="contact_maps_embed" name="contact_maps_embed" rows="3" placeholder="<iframe src=...>">{{ old('contact_maps_embed', $settings->contact_maps_embed) }}</textarea>
                                            <div class="form-text">Pega aquí el código iframe de Google Maps</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Configuración SEO</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label">Meta Título</label>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                                value="{{ old('meta_title', $settings->meta_title) }}" maxlength="60">
                                            <div class="form-text">Recomendado: 50-60 caracteres</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">Meta Descripción</label>
                                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $settings->meta_description) }}</textarea>
                                            <div class="form-text">Recomendado: 150-160 caracteres</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="meta_keywords" class="form-label">Palabras Clave</label>
                                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                                value="{{ old('meta_keywords', $settings->meta_keywords) }}">
                                            <div class="form-text">Separar con comas. Ej: hotel, hospedaje, turismo</div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="booking_system" class="form-label">Sistema de Reservas *</label>
                                                    <select class="form-control" id="booking_system" name="booking_system" required>
                                                        <option value="internal" {{ old('booking_system', $settings->booking_system) === 'internal' ? 'selected' : '' }}>Sistema Interno</option>
                                                        <option value="external" {{ old('booking_system', $settings->booking_system) === 'external' ? 'selected' : '' }}>Sistema Externo</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="external_booking_url" class="form-label">URL Externa de Reservas</label>
                                                    <input type="url" class="form-control" id="external_booking_url" name="external_booking_url" 
                                                        value="{{ old('external_booking_url', $settings->external_booking_url) }}">
                                                    <div class="form-text">Solo si usas sistema externo</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.landing.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.tab-pane {
    min-height: 400px;
}
.hero-preview {
    background-image: url('/hotel-landing/images/hero-bg.svg');
    background-size: cover;
    background-position: center;
    min-height: 200px;
}
.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
.nav-tabs .nav-link.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}
.nav-tabs .nav-link:hover {
    border-color: #28a745;
}
.preview-updated {
    animation: pulse 0.5s;
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs manually if needed
    if (typeof $ !== 'undefined') {
        $('#landingTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    } else {
        // Fallback for manual tab switching
        const tabLinks = document.querySelectorAll('#landingTabs a[data-toggle="tab"]');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active from all tabs and panes
                tabLinks.forEach(l => l.classList.remove('active'));
                tabPanes.forEach(p => {
                    p.classList.remove('show', 'active');
                });
                
                // Add active to clicked tab
                this.classList.add('active');
                
                // Show corresponding pane
                const target = this.getAttribute('href');
                const targetPane = document.querySelector(target);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
    }
    
    // Update opacity preview
    const opacitySlider = document.getElementById('hero_overlay_opacity');
    const opacityValue = document.getElementById('opacity-value');
    const overlay = document.querySelector('.hero-preview .overlay');
    
    if (opacitySlider && opacityValue && overlay) {
        opacitySlider.addEventListener('input', function() {
            opacityValue.textContent = this.value;
            overlay.style.backgroundColor = `rgba(0,0,0,${this.value})`;
            animatePreviewUpdate(overlay.parentElement);
        });
    }
    
    // Update title preview with animation
    const heroTitle = document.getElementById('hero_title');
    const previewTitle = document.getElementById('preview-title');
    if (heroTitle && previewTitle) {
        heroTitle.addEventListener('input', function() {
            previewTitle.textContent = this.value || 'Título del Hero';
            animatePreviewUpdate(previewTitle);
        });
    }
    
    // Update subtitle preview with animation
    const heroSubtitle = document.getElementById('hero_subtitle');
    const previewSubtitle = document.getElementById('preview-subtitle');
    if (heroSubtitle && previewSubtitle) {
        heroSubtitle.addEventListener('input', function() {
            previewSubtitle.textContent = this.value || 'Subtítulo del hero';
            animatePreviewUpdate(previewSubtitle);
        });
    }
    
    // Update CTA preview with animation
    const heroCta = document.getElementById('hero_cta_text');
    const previewCta = document.getElementById('preview-cta');
    if (heroCta && previewCta) {
        heroCta.addEventListener('input', function() {
            previewCta.textContent = this.value || 'Botón CTA';
            animatePreviewUpdate(previewCta);
        });
    }
    
    // Form validation helpers
    function validateField(field, rules) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';
        
        if (rules.required && !value) {
            isValid = false;
            message = 'Este campo es requerido';
        }
        
        if (rules.minLength && value.length < rules.minLength) {
            isValid = false;
            message = `Mínimo ${rules.minLength} caracteres`;
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            isValid = false;
            message = `Máximo ${rules.maxLength} caracteres`;
        }
        
        if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            isValid = false;
            message = 'Email inválido';
        }
        
        if (rules.url && value && !/^https?:\/\/.+/.test(value) && !value.startsWith('#')) {
            isValid = false;
            message = 'URL inválida (debe empezar con http/https o #)';
        }
        
        // Visual feedback
        const feedbackEl = field.parentElement.querySelector('.invalid-feedback');
        if (feedbackEl) feedbackEl.remove();
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            field.parentElement.appendChild(feedback);
        }
        
        return isValid;
    }
    
    // Add validation to key fields
    const validationRules = {
        'hero_title': { required: true, maxLength: 255 },
        'hero_cta_text': { required: true, maxLength: 100 },
        'hero_cta_link': { required: true, maxLength: 255, url: true },
        'contact_email': { email: true },
        'external_booking_url': { url: true },
        'meta_title': { maxLength: 60 },
        'meta_description': { maxLength: 160 }
    };
    
    Object.keys(validationRules).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', () => {
                validateField(field, validationRules[fieldId]);
            });
        }
    });
    
    // Form submission with loading state and validation
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            let isFormValid = true;
            
            // Validate all fields
            Object.keys(validationRules).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    const fieldValid = validateField(field, validationRules[fieldId]);
                    if (!fieldValid) isFormValid = false;
                }
            });
            
            if (!isFormValid) {
                e.preventDefault();
                alert('Por favor, corrige los errores en el formulario antes de guardar.');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';
            submitBtn.disabled = true;
        });
    }
    
    // Add preview update animation
    function animatePreviewUpdate(element) {
        element.classList.add('preview-updated');
        setTimeout(() => element.classList.remove('preview-updated'), 500);
    }
    
    // Testimonials management
    let testimonials = JSON.parse(document.getElementById('testimonials-data').value || '[]');
    
    window.addTestimonial = function() {
        const name = prompt('Nombre del cliente:');
        if (!name) return;
        
        const comment = prompt('Comentario del cliente:');
        if (!comment) return;
        
        const rating = parseInt(prompt('Calificación (1-5 estrellas):', '5'));
        if (isNaN(rating) || rating < 1 || rating > 5) {
            alert('Calificación debe ser entre 1 y 5');
            return;
        }
        
        testimonials.push({
            name: name,
            comment: comment,
            rating: rating
        });
        
        updateTestimonialsList();
        updateTestimonialsData();
    };
    
    window.removeTestimonial = function(index) {
        if (confirm('¿Estás seguro de eliminar este testimonio?')) {
            testimonials.splice(index, 1);
            updateTestimonialsList();
            updateTestimonialsData();
        }
    };
    
    function updateTestimonialsList() {
        const list = document.getElementById('testimonials-list');
        list.innerHTML = '';
        
        testimonials.forEach((testimonial, index) => {
            const div = document.createElement('div');
            div.className = 'testimonial-item border p-3 mb-2 rounded';
            
            let stars = '';
            for (let i = 0; i < testimonial.rating; i++) {
                stars += '<i class="fas fa-star text-warning"></i>';
            }
            
            div.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <strong>${testimonial.name}</strong>
                        <br><small class="text-muted">${testimonial.comment.substring(0, 60)}${testimonial.comment.length > 60 ? '...' : ''}</small>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="stars">${stars}</div>
                        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeTestimonial(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            list.appendChild(div);
        });
    }
    
    function updateTestimonialsData() {
        document.getElementById('testimonials-data').value = JSON.stringify(testimonials);
    }
    
    // Experiences management
    let experienceCounter = document.querySelectorAll('.experience-item').length;
    
    window.addExperience = function() {
        const list = document.getElementById('experiences-list');
        const index = experienceCounter++;
        
        const div = document.createElement('div');
        div.className = 'experience-item border p-3 mb-3 rounded';
        div.setAttribute('data-index', index);
        
        div.innerHTML = `
            <div class="row">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" name="experiences_list[${index}][title]" 
                                    placeholder="Ej: Senderismo Guiado">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Ícono (Remix Icon)</label>
                                <input type="text" class="form-control icon-input" name="experiences_list[${index}][icon]" 
                                    placeholder="Ej: ri-mountain-line" onchange="updateIconPreview(this, ${index})">
                                <small class="text-muted"><a href="https://remixicon.com" target="_blank">Ver iconos</a></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Vista Previa</label>
                                <div class="border p-2 rounded text-center" id="icon-preview-${index}">
                                    <i class="ri-star-line" style="font-size: 24px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="experiences_list[${index}][description]" rows="2" 
                                placeholder="Describe esta experiencia única..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeExperience(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        list.appendChild(div);
    };
    
    window.removeExperience = function(index) {
        if (confirm('¿Estás seguro de eliminar esta experiencia?')) {
            const item = document.querySelector(`.experience-item[data-index="${index}"]`);
            if (item) {
                item.remove();
            }
        }
    };
    
    window.updateIconPreview = function(input, index) {
        const preview = document.getElementById(`icon-preview-${index}`);
        if (preview) {
            preview.innerHTML = `<i class="${input.value || 'ri-star-line'}" style="font-size: 24px;"></i>`;
        }
    };
});
</script>
@endsection
