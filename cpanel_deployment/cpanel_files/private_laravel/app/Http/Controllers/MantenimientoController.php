<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Limpieza;
use App\Models\Reparacion;
use App\Models\Gasto;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoController extends Controller
{
    public function storeLimpieza(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha' => 'required|date',
            'hora' => 'required',
            'estado' => 'required|in:pendiente,en_proceso,completada',
            'observaciones' => 'nullable|string'
        ]);

        $habitacion = Habitacion::findOrFail($request->habitacion_id);
        if ($habitacion->estado === 'Ocupada') {
            return back()->with('error', 'No se puede realizar la limpieza. La habitación está ocupada.');
        }

        $limpieza = new Limpieza($request->all());
        $limpieza->user_id = Auth::id();
        $limpieza->save();

        // Actualizar el estado de la habitación según el estado de la limpieza
        if ($request->estado === 'completada') {
            $habitacion->estado = 'Disponible';
        } else {
            $habitacion->estado = 'Limpieza';
        }
        $habitacion->save();

        return redirect()->route('mantenimiento.limpieza.index')
            ->with('success', 'Registro de limpieza creado exitosamente y estado de la habitación actualizado.');
    }

    public function storeReparacion(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha' => 'required|date',
            'hora' => 'required',
            'estado' => 'required|in:pendiente,en_proceso,completada',
            'tipo_reparacion' => 'required|string',
            'costo' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'observaciones' => 'nullable|string'
        ]);

        $habitacion = Habitacion::findOrFail($request->habitacion_id);
        if ($habitacion->estado === 'Ocupada') {
            return back()->with('error', 'No se puede realizar la reparación. La habitación está ocupada.');
        }

        $reparacion = new Reparacion($request->all());
        $reparacion->user_id = Auth::id();
        $reparacion->save();

        // Crear gasto automáticamente si hay costo
        if ($reparacion->costo > 0) {
            $gasto = new Gasto([
                'user_id' => Auth::id(),
                'concepto' => 'Reparación - ' . $reparacion->tipo_reparacion . ' (Habitación ' . $habitacion->numero . ')',
                'monto' => $reparacion->costo,
                'descripcion' => $reparacion->descripcion,
                'fecha' => $reparacion->fecha,
                'estado' => 'aprobado'
            ]);
            $gasto->save();

            // Registrar el gasto en la caja si hay una abierta
            $cajaAbierta = Caja::where('user_id', Auth::id())
                ->where('estado', true)
                ->first();

            if ($cajaAbierta) {
                $cajaAbierta->registrarMovimiento(
                    'egreso',
                    $reparacion->costo,
                    'Gasto por reparación - ' . $reparacion->tipo_reparacion . ' (Habitación ' . $habitacion->numero . ')',
                    $reparacion->descripcion,
                    $gasto
                );
            }
        }

        // Solo cambiar el estado si la reparación no está completada
        if (in_array($request->estado, ['pendiente', 'en_proceso'])) {
            $habitacion->estado = 'Mantenimiento';
        } else {
            $habitacion->estado = 'Disponible';
        }
        $habitacion->save();

        return redirect()->route('mantenimiento.reparacion.index')
            ->with('success', 'Registro de reparación creado exitosamente.');
    }

    public function updateLimpieza(Request $request, Limpieza $limpieza)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,completada',
            'observaciones' => 'nullable|string'
        ]);

        $limpieza->update($request->all());

        // Refrescar la relación
        $limpieza->refresh();
        $habitacion = $limpieza->habitacion;

        if ($request->estado === 'completada' && $habitacion) {
            $habitacion->estado = 'Disponible';
            $habitacion->save();
        } else if (in_array($request->estado, ['pendiente', 'en_proceso']) && $habitacion) {
            $habitacion->estado = 'Limpieza';
            $habitacion->save();
        }

        return redirect()->route('mantenimiento.limpieza.index')
            ->with('success', 'Registro de limpieza actualizado exitosamente y estado de la habitación actualizado.');
    }

    public function updateReparacion(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,completada',
            'costo' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string'
        ]);

        $oldCost = $reparacion->costo;
        $reparacion->update($request->all());

        // Si el costo cambió, actualizar o crear el gasto
        if ($reparacion->costo != $oldCost) {
            // Buscar si existe un gasto relacionado
            $gasto = Gasto::where('concepto', 'LIKE', 'Reparación - ' . $reparacion->tipo_reparacion . ' (Habitación ' . $reparacion->habitacion->numero . ')')
                ->where('fecha', $reparacion->fecha)
                ->first();

            if ($reparacion->costo > 0) {
                if ($gasto) {
                    // Actualizar el gasto existente
                    $gasto->monto = $reparacion->costo;
                    $gasto->save();

                    // Actualizar el movimiento de caja si existe
                    $cajaAbierta = Caja::where('user_id', Auth::id())
                        ->where('estado', true)
                        ->first();

                    if ($cajaAbierta) {
                        $movimiento = MovimientoCaja::where('movimientable_type', 'App\Models\Gasto')
                            ->where('movimientable_id', $gasto->id)
                            ->first();

                        if ($movimiento) {
                            // Ajustar el saldo de la caja
                            $diferencia = $reparacion->costo - $oldCost;
                            $cajaAbierta->saldo_actual -= $diferencia;
                            $cajaAbierta->save();

                            // Actualizar el movimiento
                            $movimiento->monto = $reparacion->costo;
                            $movimiento->save();
                        }
                    }
                } else {
                    // Crear nuevo gasto
                    $gasto = new Gasto([
                        'user_id' => Auth::id(),
                        'concepto' => 'Reparación - ' . $reparacion->tipo_reparacion . ' (Habitación ' . $reparacion->habitacion->numero . ')',
                        'monto' => $reparacion->costo,
                        'descripcion' => $reparacion->descripcion,
                        'fecha' => $reparacion->fecha,
                        'estado' => 'aprobado'
                    ]);
                    $gasto->save();

                    // Registrar en caja si hay una abierta
                    $cajaAbierta = Caja::where('user_id', Auth::id())
                        ->where('estado', true)
                        ->first();

                    if ($cajaAbierta) {
                        $cajaAbierta->registrarMovimiento(
                            'egreso',
                            $reparacion->costo,
                            'Gasto por reparación - ' . $reparacion->tipo_reparacion . ' (Habitación ' . $reparacion->habitacion->numero . ')',
                            $reparacion->descripcion,
                            $gasto
                        );
                    }
                }
            }
        }

        // Refrescar la relación
        $reparacion->refresh();
        $habitacion = $reparacion->habitacion;

        if ($request->estado === 'completada' && $habitacion) {
            $habitacion->estado = 'Disponible';
            $habitacion->save();
        } else if (in_array($request->estado, ['pendiente', 'en_proceso']) && $habitacion) {
            $habitacion->estado = 'Mantenimiento';
            $habitacion->save();
        }

        return redirect()->route('mantenimiento.reparacion.index')
            ->with('success', 'Registro de reparación actualizado exitosamente.');
    }

    public function createLimpieza()
    {
        // Solo mostrar habitaciones que no estén ocupadas
        $habitaciones = Habitacion::where('estado', '!=', 'Ocupada')->get();
        return view('mantenimiento.limpieza.create', compact('habitaciones'));
    }

    public function createReparacion()
    {
        // Solo mostrar habitaciones que no estén ocupadas
        $habitaciones = Habitacion::where('estado', '!=', 'Ocupada')->get();
        return view('mantenimiento.reparacion.create', compact('habitaciones'));
    }

    public function indexLimpieza()
    {
        $limpiezas = Limpieza::with(['habitacion', 'user'])->latest()->get();
        return view('mantenimiento.limpieza.index', compact('limpiezas'));
    }

    public function indexReparacion()
    {
        $reparaciones = Reparacion::with(['habitacion', 'user'])->latest()->get();
        return view('mantenimiento.reparacion.index', compact('reparaciones'));
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}
