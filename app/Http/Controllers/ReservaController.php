<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservaController extends Controller
{
  public function __construct()
  {
    $this->middleware('permission:ver reservas')->only('index');
    $this->middleware('permission:crear reservas')->only(['create', 'store']);
    $this->middleware('permission:editar reservas')->only(['edit', 'update']);
    $this->middleware('permission:eliminar reservas')->only('destroy');
    $this->middleware('permission:hacer checkin')->only('checkin');
    $this->middleware('permission:hacer checkout')->only('checkout');

    // Configurar zona horaria para Guatemala
    date_default_timezone_set('America/Guatemala');
  }

  public function index()
  {
    $reservas = Reserva::with('habitacion')->latest()->paginate(10);
    return view('reservas.index', compact('reservas'));
  }

  public function create(Request $request)
  {
    $habitaciones = Habitacion::where('estado', 'disponible')
      ->with(['categoria', 'nivel'])
      ->get();

    $habitacionSeleccionada = null;
    if ($request->has('habitacion_id')) {
      $habitacionSeleccionada = $habitaciones->find($request->habitacion_id);
    }

    return view('reservas.create', compact('habitaciones', 'habitacionSeleccionada'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'habitacion_id' => 'required|exists:habitacions,id',
      'nombre_cliente' => 'required|string|max:255',
      'documento_identidad' => 'required|string|max:20',
      'telefono' => 'required|string|max:20',
      'fecha_entrada' => 'required|date',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'adelanto' => 'required|numeric|min:0',
      'observaciones' => 'nullable|string'
    ]);

    // Verificar si hay una caja abierta
    $cajaAbierta = Caja::where('user_id', auth()->id())
      ->where('estado', 'abierta')
      ->first();

    if (!$cajaAbierta) {
      return back()->withInput()
        ->withErrors(['error' => 'Debe abrir una caja antes de registrar una reserva.']);
    }

    // Verificar disponibilidad de la habitación
    $habitacion = Habitacion::findOrFail($request->habitacion_id);
    if ($habitacion->estado !== 'disponible') {
      return back()->withInput()
        ->withErrors(['habitacion_id' => 'La habitación seleccionada ya no está disponible.']);
    }

    // Validar hora de check-out (12:30)
    $fechaSalida = Carbon::parse($request->fecha_salida);
    $horaSalida = $fechaSalida->format('H:i');
    if ($horaSalida !== '12:30') {
      return back()->withInput()
        ->withErrors(['fecha_salida' => 'La hora de check-out debe ser a las 12:30 PM.']);
    }

    // Verificar si hay reservas que se solapan
    $reservasSolapadas = Reserva::where('habitacion_id', $request->habitacion_id)
      ->where(function ($query) use ($request) {
        $query->whereBetween('fecha_entrada', [$request->fecha_entrada, $request->fecha_salida])
          ->orWhereBetween('fecha_salida', [$request->fecha_entrada, $request->fecha_salida])
          ->orWhere(function ($q) use ($request) {
            $q->where('fecha_entrada', '<=', $request->fecha_entrada)
              ->where('fecha_salida', '>=', $request->fecha_salida);
          });
      })
      ->where('estado', '!=', 'cancelada')
      ->exists();

    if ($reservasSolapadas) {
      return back()->withInput()
        ->withErrors(['fecha_entrada' => 'La habitación no está disponible para las fechas seleccionadas.']);
    }

    // Crear la reserva
    $reserva = new Reserva($request->all());
    $reserva->estado = 'pendiente';
    $reserva->user_id = auth()->id();
    $reserva->save();

    // Registrar el adelanto en la caja
    if ($request->adelanto > 0) {
      $reserva->registrarPago(
        $request->adelanto,
        $cajaAbierta,
        'Adelanto de reserva #' . $reserva->id
      );
    }

    // Actualizar estado de la habitación
    $habitacion->estado = 'ocupado';
    $habitacion->save();

    return redirect()->route('reservas.index')
      ->with('success', 'Reserva creada exitosamente.');
  }

  public function show(Reserva $reserva)
  {
    return view('reservas.show', compact('reserva'));
  }

  public function edit(Reserva $reserva)
  {
    $habitaciones = Habitacion::where('estado', 'disponible')
      ->orWhere('id', $reserva->habitacion_id)
      ->get();
    return view('reservas.edit', compact('reserva', 'habitaciones'));
  }

  public function update(Request $request, Reserva $reserva)
  {
    $request->validate([
      'habitacion_id' => 'required|exists:habitacions,id',
      'nombre_cliente' => 'required|string|max:255',
      'documento_identidad' => 'required|string|max:20',
      'telefono' => 'required|string|max:20',
      'fecha_entrada' => 'required|date',
      'fecha_salida' => 'required|date|after:fecha_entrada',
      'adelanto' => 'required|numeric|min:0',
      'estado' => 'required|in:pendiente,activa,completada,cancelada',
      'observaciones' => 'nullable|string'
    ]);

    $reserva->update($request->all());

    return redirect()->route('reservas.index')
      ->with('success', 'Reserva actualizada exitosamente.');
  }

  public function destroy(Reserva $reserva)
  {
    $reserva->delete();

    return redirect()->route('reservas.index')
      ->with('success', 'Reserva eliminada exitosamente.');
  }

  public function checkin(Habitacion $habitacion)
  {
    if ($habitacion->estado !== 'disponible') {
      return redirect()->route('habitaciones.index')
        ->with('error', 'La habitación no está disponible para check-in.');
    }

    return view('reservas.checkin', compact('habitacion'));
  }

  public function checkout(Reserva $reserva)
  {
    if ($reserva->estado !== 'activa') {
      return redirect()->route('reservas.index')
        ->with('error', 'La reserva ya no está activa.');
    }

    $fechaEntrada = Carbon::parse($reserva->fecha_entrada);
    $fechaSalida = Carbon::parse($reserva->fecha_salida);
    $diasEstancia = $fechaEntrada->diffInDays($fechaSalida);

    $reserva->estado = 'completada';
    $reserva->save();

    $habitacion = $reserva->habitacion;
    $habitacion->estado = 'disponible';
    $habitacion->save();

    $totalPagar = $diasEstancia * $habitacion->precio;
    $pendientePago = $totalPagar - $reserva->adelanto;

    return redirect()->route('reservas.index')
      ->with('success', "Check-out realizado exitosamente. Total a pagar: S/. " . number_format($totalPagar, 2) .
        ". Pendiente: S/. " . number_format($pendientePago, 2));
  }
}
