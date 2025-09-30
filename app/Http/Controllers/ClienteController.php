<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth')->except(['buscar', 'buscarPorDpi', 'buscarPorNit']);
  }

  public function index()
  {
    $clientes = Cliente::latest()->paginate(10);
    return view('clientes.index', compact('clientes'));
  }

  public function create()
  {
    return view('clientes.create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string|max:255',
      'nit' => 'required|string|max:20|unique:clientes',
      'dpi' => 'required|string|max:20|unique:clientes',
      'telefono' => 'required|string|max:20',
    ]);

    // Convertir nombre a mayúsculas
    $data = $request->all();
    $data['nombre'] = strtoupper($data['nombre']);

    $cliente = Cliente::create($data);

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json($cliente, 201);
    }

    return redirect()->route('clientes.index')
      ->with('success', 'Cliente creado exitosamente.');
  }

  public function show(Cliente $cliente)
  {
    return view('clientes.show', compact('cliente'));
  }

  public function edit(Cliente $cliente)
  {
    return view('clientes.edit', compact('cliente'));
  }

  public function update(Request $request, Cliente $cliente)
  {
    $request->validate([
      'nombre' => 'required|string|max:255',
      'nit' => 'required|string|max:20|unique:clientes,nit,' . $cliente->id,
      'dpi' => 'required|string|max:20|unique:clientes,dpi,' . $cliente->id,
      'telefono' => 'required|string|max:20',
    ]);

    // Convertir nombre a mayúsculas
    $data = $request->all();
    $data['nombre'] = strtoupper($data['nombre']);

    $cliente->update($data);

    return redirect()->route('clientes.index')
      ->with('success', 'Cliente actualizado exitosamente.');
  }

  public function destroy(Cliente $cliente)
  {
    $cliente->delete();

    return redirect()->route('clientes.index')
      ->with('success', 'Cliente eliminado exitosamente.');
  }

  public function buscar(Request $request)
  {
    $query = $request->get('q');

    // Search for a single client by name (for landing page integration)
    $cliente = Cliente::where('nombre', 'like', "%{$query}%")
      ->orWhere('nit', 'like', "%{$query}%")
      ->orWhere('dpi', 'like', "%{$query}%")
      ->orWhere('email', 'like', "%{$query}%")
      ->orWhere('telefono', 'like', "%{$query}%")
      ->first();

    if ($cliente) {
      // Return only basic client info (email and phone) for security
      return response()->json([
        'success' => true,
        'cliente' => [
          'id' => $cliente->id,
          'nombre' => $cliente->nombre,
          'email' => $cliente->email,
          'telefono' => $cliente->telefono
        ]
      ]);
    }

    return response()->json([
      'success' => false,
      'cliente' => null
    ]);
  }

  /**
   * Buscar cliente por DPI
   */
  public function buscarPorDpi($dpi)
  {
    $cliente = Cliente::where('dpi', $dpi)->first();

    if ($cliente) {
      return response()->json($cliente);
    } else {
      return response()->json(null, 404);
    }
  }

  /**
   * Buscar cliente por NIT
   */
  public function buscarPorNit($nit)
  {
    $cliente = Cliente::where('nit', $nit)->first();

    if ($cliente) {
      return response()->json($cliente);
    } else {
      return response()->json(null, 404);
    }
  }
}
