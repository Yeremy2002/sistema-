<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver niveles')->only('index');
        $this->middleware('permission:crear niveles')->only(['create', 'store']);
        $this->middleware('permission:editar niveles')->only(['edit', 'update']);
        $this->middleware('permission:eliminar niveles')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $niveles = Nivel::all();
        return view('niveles.index', compact('niveles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('niveles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:nivels',
            'descripcion' => 'required|string',
            'estado' => 'required|boolean'
        ]);

        Nivel::create($request->all());

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nivel $nivele)
    {
        return view('niveles.edit', ['nivel' => $nivele]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nivel $nivele)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:nivels,nombre,' . $nivele->id,
            'descripcion' => 'required|string',
            'estado' => 'required|boolean'
        ]);

        $nivele->update($request->all());

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nivel $nivele)
    {
        if ($nivele->habitaciones()->exists()) {
            return redirect()->route('niveles.index')
                ->with('error', 'No se puede eliminar el nivel porque tiene habitaciones asociadas.');
        }

        $nivele->delete();

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel eliminado exitosamente.');
    }
}
