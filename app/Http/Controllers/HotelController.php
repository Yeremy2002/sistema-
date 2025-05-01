<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
  public function __construct()
  {
    $this->middleware('permission:ver configuracion')->only('edit');
    $this->middleware('permission:editar configuracion')->only('update');
  }

  public function edit()
  {
    $hotel = Hotel::getInfo();
    return view('configuracion.hotel', compact('hotel'));
  }

  public function update(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string|max:255',
      'nit' => 'required|string|max:20',
      'nombre_fiscal' => 'required|string|max:255',
      'direccion' => 'required|string|max:500',
      'simbolo_moneda' => 'required|string|max:5',
      'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    $hotel = Hotel::getInfo();

    // Manejar la subida del logo
    if ($request->hasFile('logo')) {
      // Eliminar el logo anterior si existe
      if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
        Storage::disk('public')->delete($hotel->logo);
      }

      // Guardar el nuevo logo
      $path = $request->file('logo')->store('logos', 'public');
      $hotel->logo = $path;
    }

    $hotel->nombre = $request->nombre;
    $hotel->nit = $request->nit;
    $hotel->nombre_fiscal = $request->nombre_fiscal;
    $hotel->direccion = $request->direccion;
    $hotel->simbolo_moneda = $request->simbolo_moneda;
    $hotel->save();

    return redirect()->route('configuracion.hotel')
      ->with('success', 'Información del hotel actualizada correctamente.');
  }
}
