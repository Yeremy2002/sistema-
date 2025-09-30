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
      'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
      'session_lifetime' => 'required|integer|min:1|max:1440',
      'checkin_hora_inicio' => 'required|date_format:H:i',
      'checkin_hora_anticipado' => 'required|date_format:H:i',
      'checkout_hora_inicio' => 'required|date_format:H:i',
      'checkout_hora_fin' => 'required|date_format:H:i',
      'permitir_checkin_anticipado' => 'nullable|boolean',
      'permitir_estancias_horas' => 'nullable|boolean',
      'minimo_horas_estancia' => 'required_if:permitir_estancias_horas,1|nullable|integer|min:1|max:12',
      'checkout_mismo_dia_limite' => 'required_if:permitir_estancias_horas,1|nullable|date_format:H:i',
      'reservas_vencidas_horas' => 'required|integer|min:1|max:168',
      'scheduler_frecuencia' => 'required|in:12h,24h,48h,72h',
      'notificacion_intervalo_segundos' => 'required|integer|min:10|max:300',
      'notificacion_activa' => 'nullable|boolean',
      'notificacion_badge_color' => 'required|in:primary,secondary,success,danger,warning,info,light,dark',
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
    $hotel->session_lifetime = $request->session_lifetime;
    $hotel->checkin_hora_inicio = $request->checkin_hora_inicio;
    $hotel->checkin_hora_anticipado = $request->checkin_hora_anticipado;
    $hotel->checkout_hora_inicio = $request->checkout_hora_inicio;
    $hotel->checkout_hora_fin = $request->checkout_hora_fin;
    $hotel->permitir_checkin_anticipado = $request->has('permitir_checkin_anticipado');
    $hotel->permitir_estancias_horas = $request->has('permitir_estancias_horas');
    $hotel->minimo_horas_estancia = $request->minimo_horas_estancia ?? 2;
    $hotel->checkout_mismo_dia_limite = $request->checkout_mismo_dia_limite;
    $hotel->reservas_vencidas_horas = $request->reservas_vencidas_horas;
    $hotel->scheduler_frecuencia = $request->scheduler_frecuencia;
    $hotel->notificacion_intervalo_segundos = $request->notificacion_intervalo_segundos;
    $hotel->notificacion_activa = $request->has('notificacion_activa');
    $hotel->notificacion_badge_color = $request->notificacion_badge_color;
    $hotel->save();

    return redirect()->route('configuracion.hotel')
      ->with('success', 'Información del hotel actualizada correctamente.');
  }
}
