<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Categoria;
use App\Models\Nivel;
use App\Models\HabitacionImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HabitacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver habitaciones')->only('index');
        $this->middleware('permission:crear habitaciones')->only(['create', 'store']);
        $this->middleware('permission:editar habitaciones')->only(['edit', 'update']);
        $this->middleware('permission:eliminar habitaciones')->only('destroy');
        $this->middleware('permission:cambiar estado habitaciones')->only('cambiarEstado');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $habitaciones = Habitacion::with(['categoria', 'nivel'])->get();
        return view('habitaciones.index', compact('habitaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::where('estado', true)->get();
        $niveles = Nivel::where('estado', true)->get();
        return view('habitaciones.create', compact('categorias', 'niveles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:habitacions',
            'descripcion' => 'nullable|string|max:255',
            'caracteristicas' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'nivel_id' => 'required|exists:nivels,id',
            'estado' => 'required|in:Disponible,Ocupada,Mantenimiento',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagen_principal' => 'nullable|integer|min:0'
        ]);

        $habitacion = Habitacion::create($request->except(['imagenes', 'imagen_principal']));

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $ruta = $imagen->store('habitaciones', 'public');
                $esPrincipal = $request->imagen_principal == $index;

                HabitacionImagen::create([
                    'habitacion_id' => $habitacion->id,
                    'ruta' => $ruta,
                    'es_principal' => $esPrincipal
                ]);
            }
        }

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Habitacion $habitacione)
    {
        return view('habitaciones.show', compact('habitacione'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Habitacion $habitacione)
    {
        $categorias = Categoria::where('estado', true)->get();
        $niveles = Nivel::where('estado', true)->get();
        return view('habitaciones.edit', compact('habitacione', 'categorias', 'niveles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Habitacion $habitacione)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:habitacions,numero,' . $habitacione->id,
            'descripcion' => 'nullable|string|max:255',
            'caracteristicas' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'nivel_id' => 'required|exists:nivels,id',
            'estado' => 'required|in:Disponible,Ocupada,Mantenimiento',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagen_principal' => 'nullable|integer|min:0',
            'eliminar_imagenes' => 'nullable|array',
            'eliminar_imagenes.*' => 'exists:habitacion_imagenes,id'
        ]);

        $habitacione->update($request->except(['imagenes', 'imagen_principal', 'eliminar_imagenes']));

        // Eliminar imágenes seleccionadas
        if ($request->has('eliminar_imagenes')) {
            foreach ($request->eliminar_imagenes as $imagenId) {
                $imagen = HabitacionImagen::find($imagenId);
                if ($imagen) {
                    Storage::disk('public')->delete($imagen->ruta);
                    $imagen->delete();
                }
            }
        }

        // Agregar nuevas imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $ruta = $imagen->store('habitaciones', 'public');
                $esPrincipal = $request->imagen_principal == $index;

                HabitacionImagen::create([
                    'habitacion_id' => $habitacione->id,
                    'ruta' => $ruta,
                    'es_principal' => $esPrincipal
                ]);
            }
        }

        // Actualizar imagen principal si se especificó
        if ($request->has('imagen_principal')) {
            HabitacionImagen::where('habitacion_id', $habitacione->id)
                ->update(['es_principal' => false]);

            $imagenPrincipal = HabitacionImagen::find($request->imagen_principal);
            if ($imagenPrincipal) {
                $imagenPrincipal->update(['es_principal' => true]);
            }
        }

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habitacion $habitacione)
    {
        // Eliminar todas las imágenes asociadas
        foreach ($habitacione->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
        }

        $habitacione->delete();
        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación eliminada exitosamente.');
    }

    public function cambiarEstado(Request $request, Habitacion $habitacion)
    {
        $request->validate([
            'estado' => 'required|in:Disponible,Ocupada,Limpieza,Mantenimiento'
        ]);

        try {
            $habitacion->update([
                'estado' => $request->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }
}
