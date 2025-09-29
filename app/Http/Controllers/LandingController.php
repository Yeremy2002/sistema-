<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Habitacion;
use App\Models\Categoria;
use App\Models\Hotel;
use App\Models\HabitacionImagen;
use Illuminate\Support\Facades\Storage;

class LandingController extends Controller
{
    /**
     * Mostrar la landing page principal del hotel
     */
    public function index()
    {
        // Obtener datos reales del backend
        $hotel = Hotel::first();
        $habitaciones = $this->getHabitacionesParaLanding();
        $categorias = Categoria::all();
        $heroImages = $this->getHeroCarouselImages();
        
        return view('landing.dynamic', compact('hotel', 'habitaciones', 'categorias', 'heroImages'));
    }
    
    /**
     * Obtener habitaciones para mostrar en la landing page
     */
    private function getHabitacionesParaLanding()
    {
        // Obtener una habitación representativa de cada categoría con su imagen principal
        $habitaciones = collect();
        
        $categorias = Categoria::with(['habitaciones' => function($query) {
            $query->with(['imagenes' => function($q) {
                $q->where('es_principal', true);
            }])
            ->where('estado', 'Disponible')
            ->limit(1);
        }])->get();
        
        foreach ($categorias as $categoria) {
            if ($categoria->habitaciones->isNotEmpty()) {
                $habitacion = $categoria->habitaciones->first();
                $habitacion->categoria_info = $categoria;
                $habitaciones->push($habitacion);
            }
        }
        
        return $habitaciones;
    }
    
    /**
     * Obtener imágenes para el carrusel del hero
     */
    private function getHeroCarouselImages()
    {
        // Obtener imágenes destacadas de diferentes categorías para el hero
        $heroImages = collect();
        
        // Títulos atractivos para cada categoría
        $categoryTitles = [
            'Estandar' => [
                'title' => 'Comodidad Auténtica',
                'subtitle' => 'Habitaciones cálidas con todas las comodidades'
            ],
            'Superior' => [
                'title' => 'Elegancia en la Montaña', 
                'subtitle' => 'Espacios superiores con vistas espectaculares'
            ],
            'Suite' => [
                'title' => 'Lujo y Naturaleza',
                'subtitle' => 'Suites exclusivas para una experiencia única'
            ]
        ];
        
        // Obtener imágenes principales de diferentes categorías
        $imagenes = HabitacionImagen::where('es_principal', true)
            ->with(['habitacion.categoria'])
            ->limit(6)
            ->get();
        
        foreach ($imagenes as $imagen) {
            $categoria = $imagen->habitacion->categoria->nombre ?? 'Estandar';
            $titles = $categoryTitles[$categoria] ?? $categoryTitles['Estandar'];
            
            $heroImages->push([
                'url' => asset('storage/' . $imagen->ruta),
                'alt' => 'Habitación ' . $categoria . ' - ' . ($imagen->habitacion->numero ?? ''),
                'title' => $titles['title'],
                'subtitle' => $titles['subtitle']
            ]);
        }
        
        // Si no hay suficientes imágenes, agregar algunas por defecto
        if ($heroImages->count() < 3) {
            $heroImages->push([
                'url' => url('/hotel-landing/images/hero-bg.svg'),
                'alt' => 'Vista panorámica del hotel',
                'title' => 'Casa Vieja Hotel',
                'subtitle' => 'Tu hogar en el corazón de la montaña'
            ]);
            
            $heroImages->push([
                'url' => url('/hotel-landing/images/panoramic-view.svg'),
                'alt' => 'Vista de la montaña',
                'title' => 'Vistas Espectaculares',
                'subtitle' => 'Paisajes que enamoran'
            ]);
            
            $heroImages->push([
                'url' => url('/hotel-landing/images/hero-mountain.svg'),
                'alt' => 'Experiencia de montaña',
                'title' => 'Experiencia Única',
                'subtitle' => 'Naturaleza y comodidad'
            ]);
        }
        
        return $heroImages->take(6); // Limitar a 6 imágenes para el carrusel
    }
    
    /**
     * Servir archivos estáticos de la landing page (CSS, JS, imágenes)
     */
    public function assets($filename)
    {
        // Buscar en public/landing
        $landingPath = public_path("landing/{$filename}");
        if (File::exists($landingPath)) {
            return $this->serveFile($landingPath, $filename);
        }
        
        // Buscar en public/hotel_landing (fallback)
        $publicPath = public_path("hotel_landing/{$filename}");
        if (File::exists($publicPath)) {
            return $this->serveFile($publicPath, $filename);
        }
        
        // Luego buscar en la carpeta del proyecto (fallback)
        $projectPath = base_path("hotel_landing_page/{$filename}");
        if (File::exists($projectPath)) {
            return $this->serveFile($projectPath, $filename);
        }
        
        abort(404);
    }
    
    /**
     * Servir archivo con el tipo MIME correcto
     */
    private function serveFile($path, $filename)
    {
        $mimeType = $this->getMimeType($filename, $path);
        return response(File::get($path))->header('Content-Type', $mimeType);
    }
    
    /**
     * Obtener tipo MIME basado en el contenido real del archivo
     */
    private function getMimeType($filename, $path = null)
    {
        // Si tenemos la ruta del archivo, detectar el tipo MIME real
        if ($path && File::exists($path)) {
            // Verificar si es SVG leyendo el contenido
            $content = File::get($path);
            if (strpos($content, '<svg') !== false || strpos($content, '<?xml') !== false && strpos($content, 'svg') !== false) {
                return 'image/svg+xml';
            }
            
            // Usar finfo como backup
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
            
            if ($mimeType) {
                return $mimeType;
            }
        }
        
        // Fallback: detectar por extensión
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'json' => 'application/json'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
