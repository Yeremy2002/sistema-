<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Categoria;
use App\Models\Nivel;
use Illuminate\Http\Request;

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
        ]);

        $habitacion = Habitacion::create($request->all());

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
        ]);

        $habitacione->update($request->all());

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habitacion $habitacione)
    {
        $habitacione->delete();
        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación eliminada exitosamente.');
    }
}
