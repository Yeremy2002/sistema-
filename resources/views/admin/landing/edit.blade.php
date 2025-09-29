@extends('adminlte::page')

@section('title', 'Editar Landing Page')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Editar Configuración de Landing Page
                    </h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.landing.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <a href="{{ route('landing.index') }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-eye me-1"></i> Vista Previa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</h6>
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero" type="button" role="tab">
                                    <i class="fas fa-banner me-1"></i>Hero
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections" type="button" role="tab">
                                    <i class="fas fa-list me-1"></i>Secciones
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab">
                                    <i class="fas fa-images me-1"></i>Galería
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testimonials-tab" data-bs-toggle="tab" data-bs-target="#testimonials" type="button" role="tab">
                                    <i class="fas fa-comments me-1"></i>Testimonios
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                    <i class="fas fa-address-book me-1"></i>Contacto
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                                    <i class="fas fa-search me-1"></i>SEO
                                </button>
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

                            <!-- Gallery Tab -->
                            <div class="tab-pane fade" id="gallery" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Gestión de Galería</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            La gestión de imágenes de galería se implementará en una siguiente fase.
                                            Por ahora las imágenes se obtienen automáticamente de las habitaciones registradas.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Testimonials Tab -->
                            <div class="tab-pane fade" id="testimonials" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Gestión de Testimonios</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            La gestión de testimonios se implementará en una siguiente fase.
                                        </div>
                                    </div>
                                </div>
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
                                <i class="fas fa-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update opacity preview
    const opacitySlider = document.getElementById('hero_overlay_opacity');
    const opacityValue = document.getElementById('opacity-value');
    const overlay = document.querySelector('.hero-preview .overlay');
    
    opacitySlider.addEventListener('input', function() {
        opacityValue.textContent = this.value;
        overlay.style.backgroundColor = `rgba(0,0,0,${this.value})`;
    });
    
    // Update title preview
    document.getElementById('hero_title').addEventListener('input', function() {
        document.getElementById('preview-title').textContent = this.value;
    });
    
    // Update subtitle preview
    document.getElementById('hero_subtitle').addEventListener('input', function() {
        document.getElementById('preview-subtitle').textContent = this.value;
    });
    
    // Update CTA preview
    document.getElementById('hero_cta_text').addEventListener('input', function() {
        document.getElementById('preview-cta').textContent = this.value;
    });
});
</script>
@endsection