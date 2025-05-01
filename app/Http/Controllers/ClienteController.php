<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
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

    $cliente = Cliente::create($request->all());

    if ($request->ajax()) {
      return response()->json($cliente);
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

    $cliente->update($request->all());

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

    $clientes = Cliente::where('nombre', 'like', "%{$query}%")
      ->orWhere('nit', 'like', "%{$query}%")
      ->orWhere('dpi', 'like', "%{$query}%")
      ->get();

    return response()->json($clientes);
  }
}
