<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LandingSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the landing settings form
     */
    public function index()
    {
        $settings = LandingSetting::getActive();
        return view('admin.landing.index', compact('settings'));
    }

    /**
     * Show the form for creating/editing landing settings
     */
    public function edit()
    {
        $settings = LandingSetting::getActive();
        return view('admin.landing.edit', compact('settings'));
    }

    /**
     * Update the landing settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'hero_cta_text' => 'required|string|max:100',
            'hero_cta_link' => 'required|string|max:255',
            'hero_carousel_duration' => 'required|integer|min:1000|max:10000',
            'hero_overlay_opacity' => 'required|numeric|min:0|max:1',
            'hero_show_carousel' => 'boolean',
            
            'about_title' => 'required|string|max:255',
            'about_content' => 'nullable|string',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            
            'restaurant_title' => 'required|string|max:255',
            'restaurant_content' => 'nullable|string',
            
            'experiences_title' => 'required|string|max:255',
            'experiences_content' => 'nullable|string',
            
            'gallery_title' => 'required|string|max:255',
            'testimonials_title' => 'required|string|max:255',
            
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'contact_address' => 'nullable|string',
            'contact_maps_embed' => 'nullable|string',
            
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'meta_keywords' => 'nullable|string|max:255',
            
            'rooms_per_carousel' => 'required|integer|min:1|max:12',
            'booking_system' => 'required|in:internal,external',
            'external_booking_url' => 'nullable|url'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $request->except(['_token', '_method']);
        $data['hero_show_carousel'] = $request->has('hero_show_carousel');
        $data['is_active'] = true;
        
        // Handle about image upload
        if ($request->hasFile('about_image')) {
            $settings = LandingSetting::first();
            
            // Delete old image
            if ($settings && $settings->about_image) {
                Storage::disk('public')->delete($settings->about_image);
            }
            
            $image = $request->file('about_image');
            $imageName = 'about_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('landing/about', $imageName, 'public');
            $data['about_image'] = $imagePath;
        }
        
        // Handle JSON fields
        $jsonFields = ['restaurant_images', 'experiences_list', 'gallery_images', 'testimonials', 'social_media'];
        
        foreach ($jsonFields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field, []);
            }
        }
        
        try {
            LandingSetting::updateSettings($data);
            
            return redirect()->route('admin.landing.index')
                ->with('success', 'Configuración de landing page actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la configuración: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Upload gallery images via AJAX
     */
    public function uploadGalleryImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Imagen inválida'
            ]);
        }
        
        try {
            $image = $request->file('image');
            $imageName = 'gallery_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('landing/gallery', $imageName, 'public');
            
            return response()->json([
                'success' => true,
                'path' => $imagePath,
                'url' => Storage::url($imagePath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete gallery image
     */
    public function deleteGalleryImage(Request $request)
    {
        $imagePath = $request->input('path');
        
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Imagen no encontrada'
        ]);
    }
    
    /**
     * Preview landing page with current settings
     */
    public function preview()
    {
        $settings = LandingSetting::getActive();
        return view('landing.preview', compact('settings'));
    }
}
