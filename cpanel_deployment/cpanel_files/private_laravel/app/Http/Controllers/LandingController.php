<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LandingController extends Controller
{
    /**
     * Mostrar la landing page principal del hotel
     */
    public function index()
    {
        // Usar la vista Blade dinámica que incluye el logo del hotel
        // La variable $hotel ya está disponible gracias al View Composer en AppServiceProvider
        return view('landing.dynamic');
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
        $mimeType = $this->getMimeType($filename);
        return response(File::get($path))->header('Content-Type', $mimeType);
    }
    
    /**
     * Obtener tipo MIME basado en la extensión
     */
    private function getMimeType($filename)
    {
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
