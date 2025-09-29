@extends('adminlte::page')

@section('title', 'Gestión de Landing Page')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-globe mr-2"></i>
                        Gestión de Landing Page
                    </h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('landing.index') }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-eye mr-1"></i> Ver Landing
                        </a>
                        <a href="{{ route('admin.landing.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Estadísticas rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="card-text">Estado</p>
                                            <h5 class="card-title">
                                                {{ $settings->is_active ? 'Activo' : 'Inactivo' }}
                                            </h5>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-power-off fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="card-text">Carrusel</p>
                                            <h5 class="card-title">
                                                {{ $settings->hero_show_carousel ? 'Habilitado' : 'Deshabilitado' }}
                                            </h5>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-images fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="card-text">Galería</p>
                                            <h5 class="card-title">
                                                {{ is_array($settings->gallery_images) ? count($settings->gallery_images) : 0 }} imágenes
                                            </h5>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-photo-video fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="card-text">Testimonios</p>
                                            <h5 class="card-title">
                                                {{ is_array($settings->testimonials) ? count($settings->testimonials) : 0 }} testimonios
                                            </h5>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-comments fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración actual -->
                    <div class="row">
                        <!-- Hero Section -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-banner mr-2"></i>Sección Hero</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Título:</strong></td>
                                            <td>{{ $settings->hero_title ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Subtítulo:</strong></td>
                                            <td>{{ Str::limit($settings->hero_subtitle, 50) ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Botón CTA:</strong></td>
                                            <td>{{ $settings->hero_cta_text ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Duración carrusel:</strong></td>
                                            <td>{{ $settings->hero_carousel_duration / 1000 }} segundos</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Secciones -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-list mr-2"></i>Secciones</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Sobre Nosotros:</strong></td>
                                            <td>{{ $settings->about_title ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Restaurante:</strong></td>
                                            <td>{{ $settings->restaurant_title ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Experiencias:</strong></td>
                                            <td>{{ $settings->experiences_title ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Galería:</strong></td>
                                            <td>{{ $settings->gallery_title ?: 'No configurado' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Información de contacto -->
                        <div class="col-md-6 mt-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-address-book mr-2"></i>Información de Contacto</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Teléfono:</strong></td>
                                            <td>{{ $settings->contact_phone ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $settings->contact_email ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dirección:</strong></td>
                                            <td>{{ Str::limit($settings->contact_address, 50) ?: 'No configurado' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración SEO -->
                        <div class="col-md-6 mt-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-search mr-2"></i>Configuración SEO</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Meta Título:</strong></td>
                                            <td>{{ Str::limit($settings->meta_title, 40) ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Meta Descripción:</strong></td>
                                            <td>{{ Str::limit($settings->meta_description, 50) ?: 'No configurado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Palabras clave:</strong></td>
                                            <td>{{ Str::limit($settings->meta_keywords, 40) ?: 'No configurado' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-tools mr-2"></i>Acciones Rápidas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.landing.edit') }}" class="btn btn-primary">
                                            <i class="fas fa-edit mr-1"></i> Editar Configuración
                                        </a>
                                        <a href="{{ route('landing.index') }}" target="_blank" class="btn btn-success">
                                            <i class="fas fa-external-link-alt mr-1"></i> Vista Previa
                                        </a>
                                        <button type="button" class="btn btn-info" onclick="clearCache()">
                                            <i class="fas fa-sync-alt mr-1"></i> Limpiar Caché
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    // Función para limpiar caché si es necesario
    alert('Funcionalidad de caché pendiente de implementar');
}
</script>
@endsection