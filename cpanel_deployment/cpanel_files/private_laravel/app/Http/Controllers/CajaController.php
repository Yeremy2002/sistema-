<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
  public function __construct()
  {
    $this->middleware('permission:ver cajas')->only(['index', 'show']);
    $this->middleware('permission:abrir caja')->only(['create', 'store']);
    $this->middleware('permission:cerrar caja')->only(['edit', 'update']);
    $this->middleware('permission:ver movimientos caja')->only(['movimientos', 'show']);
    $this->middleware('permission:registrar movimiento')->only(['createMovimiento', 'storeMovimiento', 'registrarPago']);
    $this->middleware('permission:ver arqueo caja')->only('arqueo');
    $this->middleware('permission:realizar arqueo')->only('realizarArqueo');
    $this->middleware('permission:asignar caja')->only(['asignar', 'asignarStore']);
  }

  public function index()
  {
    $cajas = Caja::with('user')->latest()->paginate(10);
    return view('cajas.index', compact('cajas'));
  }

  public function asignar()
  {
    // Obtener usuarios con rol de administrador que no tengan caja abierta
    $usuarios = User::role('Administrador')
      ->whereDoesntHave('cajas', function ($query) {
        $query->where('estado', true);
      })
      ->get();

    // Obtener cajas activas
    $cajasActivas = Caja::with(['user', 'user.roles'])
      ->where('estado', true)
      ->get();

    return view('cajas.asignar', compact('usuarios', 'cajasActivas'));
  }

  public function asignarStore(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
      'turno' => 'required|in:matutino,nocturno',
      'saldo_inicial' => 'required|numeric|min:0',
      'observaciones' => 'nullable|string'
    ]);

    // Verificar si el usuario ya tiene una caja abierta
    $cajaAbierta = Caja::where('user_id', $request->user_id)
      ->where('estado', true)
      ->first();

    if ($cajaAbierta) {
      return redirect()->back()
        ->with('error', 'El usuario ya tiene una caja abierta.');
    }

    // Verificar si ya existe una caja abierta para ese turno
    $cajaAbiertaTurno = Caja::where('user_id', $request->user_id)
      ->where('turno', $request->turno)
      ->where('estado', true)
      ->first();

    if ($cajaAbiertaTurno) {
      return redirect()->back()
        ->with('error', 'Ya tienes una caja abierta para este turno.');
    }

    $caja = new Caja([
      'user_id' => $request->user_id,
      'saldo_inicial' => $request->saldo_inicial,
      'saldo_actual' => $request->saldo_inicial,
      'turno' => $request->turno,
      'fecha_apertura' => now(),
      'observaciones_apertura' => $request->observaciones,
      'estado' => true
    ]);

    $caja->save();

    return redirect()->route('cajas.show', $caja)
      ->with('success', 'Caja asignada exitosamente.');
  }

  public function create()
  {
    // Verificar si ya tiene una caja abierta
    $cajaAbierta = Caja::where('user_id', Auth::id())
      ->where('estado', true)
      ->first();

    if ($cajaAbierta) {
      return redirect()->route('cajas.index')
        ->with('error', 'Ya tienes una caja abierta.');
    }

    // Determinar el turno sugerido basado en la hora actual
    $horaActual = Carbon::now()->hour;
    $turnoSugerido = ($horaActual >= 6 && $horaActual < 18) ? 'matutino' : 'nocturno';

    // Verificar si ya existe una caja abierta para el turno sugerido
    $cajaExistente = Caja::where('turno', $turnoSugerido)
      ->where('estado', true)
      ->first();

    if ($cajaExistente) {
      $mensaje = 'Ya existe una caja abierta para el turno ' . ($turnoSugerido == 'matutino' ? 'de mañana' : 'de noche') .
        ' por el usuario ' . $cajaExistente->user->name . '.';
      $turnoAlternativo = $turnoSugerido == 'matutino' ? 'nocturno' : 'matutino';

      // Verificar si también hay una caja abierta para el turno alternativo
      $cajaAlternativa = Caja::where('turno', $turnoAlternativo)
        ->where('estado', true)
        ->first();

      if ($cajaAlternativa) {
        return redirect()->route('cajas.index')
          ->with('error', 'Ya existen cajas abiertas para ambos turnos. No se puede abrir una nueva caja.');
      }
    }

    return view('cajas.create', compact('turnoSugerido'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'saldo_inicial' => 'required|numeric|min:0',
      'turno' => 'required|in:matutino,nocturno',
      'observaciones_apertura' => 'nullable|string'
    ]);

    // Verificar si el usuario ya tiene una caja abierta
    $cajaUsuario = Caja::where('user_id', Auth::id())
      ->where('estado', true)
      ->first();

    if ($cajaUsuario) {
      return redirect()->back()
        ->with('error', 'Ya tienes una caja abierta. Debes cerrarla antes de abrir una nueva.');
    }

    // Verificar si ya existe una caja abierta para ese turno
    $cajaAbiertaTurno = Caja::where('user_id', Auth::id())
      ->where('turno', $request->turno)
      ->where('estado', true)
      ->first();

    if ($cajaAbiertaTurno) {
      return redirect()->back()
        ->with('error', 'Ya tienes una caja abierta para este turno.');
    }

    // Determinar el turno sugerido basado en la hora actual
    $horaActual = Carbon::now()->hour;
    $turnoSugerido = ($horaActual >= 6 && $horaActual < 18) ? 'matutino' : 'nocturno';

    // Mostrar advertencia si el turno seleccionado no coincide con la hora actual
    $mensajeAdicional = '';
    if ($request->turno != $turnoSugerido) {
      $mensajeAdicional = ' Nota: Has seleccionado el turno ' .
        ($request->turno == 'matutino' ? 'matutino (mañana)' : 'nocturno (noche)') .
        ' pero la hora actual sugiere el turno ' .
        ($turnoSugerido == 'matutino' ? 'matutino (mañana)' : 'nocturno (noche)') . '.';
    }

    $caja = new Caja([
      'user_id' => Auth::id(),
      'saldo_inicial' => $request->saldo_inicial,
      'saldo_actual' => $request->saldo_inicial,
      'turno' => $request->turno,
      'fecha_apertura' => now(),
      'observaciones_apertura' => $request->observaciones_apertura,
      'estado' => true
    ]);

    $caja->save();

    return redirect()->route('cajas.show', $caja)
      ->with('success', 'Caja abierta exitosamente.' . $mensajeAdicional);
  }

  public function show(Caja $caja)
  {
    $this->authorize('view', $caja);
    $caja->load(['user']);
    $movimientos = $caja->movimientos()->paginate(10); // Paginar los movimientos
    return view('cajas.show', compact('caja', 'movimientos'));
  }

  public function edit(Caja $caja)
  {
    $this->authorize('update', $caja);

    // Los administradores pueden cerrar cualquier caja, incluso cerradas para correcciones
    $esAdmin = \Auth::user()->hasRole('Administrador');
    
    if (!$caja->estado && !$esAdmin) {
      return redirect()->route('cajas.index')
        ->with('error', 'Esta caja ya está cerrada.');
    }

    return view('cajas.edit', compact('caja', 'esAdmin'));
  }

  public function update(Request $request, Caja $caja)
  {
    $this->authorize('update', $caja);

    $esAdmin = \Auth::user()->hasRole('Administrador');
    
    // Validaciones específicas para administradores
    if ($esAdmin) {
      $request->validate([
        'saldo_final' => 'required|numeric|min:0',
        'observaciones_cierre' => 'nullable|string|max:1000',
        'justificacion_admin' => 'required|string|max:500'
      ]);
    } else {
      $request->validate([
        'saldo_final' => 'required|numeric|min:0',
        'observaciones_cierre' => 'nullable|string|max:1000'
      ]);
    }

    // Validar que la caja esté abierta (excepto para administradores)
    if (!$caja->estado && !$esAdmin) {
      return redirect()->route('cajas.index')
        ->with('error', 'Esta caja ya está cerrada.');
    }

    try {
      DB::beginTransaction();

      // Calcular diferencia antes de guardar
      $saldoEsperado = $caja->saldo_actual;
      $saldoReal = $request->saldo_final;
      $diferencia = $saldoReal - $saldoEsperado;

      // Actualizar campos de la caja
      $caja->fecha_cierre = now();
      $caja->saldo_final = $saldoReal;
      
      // Combinar observaciones regulares con justificación administrativa si aplica
      $observacionesCierre = $request->observaciones_cierre;
      if ($esAdmin && $request->justificacion_admin) {
        $observacionesCierre .= ($observacionesCierre ? "\n\n" : '') . 
          "[CIERRE ADMINISTRATIVO POR: " . \Auth::user()->name . "]\n" .
          "Justificación: " . $request->justificacion_admin;
      }
      
      $caja->observaciones_cierre = $observacionesCierre;
      $caja->estado = false;
      $caja->save();

      // Si hay diferencia, registrar un movimiento de ajuste
      if (abs($diferencia) > 0.01) { // Evitar problemas de precisión de decimales
        $tipo = $diferencia > 0 ? 'ingreso' : 'egreso';
        $concepto = $diferencia > 0 ? 'Sobrante en cierre de caja' : 'Faltante en cierre de caja';
        $descripcion = sprintf(
          'Ajuste automático al cierre de caja. Saldo esperado: %s, Saldo real: %s, Diferencia: %s',
          number_format($saldoEsperado, 2),
          number_format($saldoReal, 2),
          number_format($diferencia, 2)
        );

        // Crear el movimiento de ajuste sin afectar el saldo_actual (la caja ya está cerrada)
        $movimiento = new \App\Models\MovimientoCaja([
          'user_id' => \Auth::id(),
          'tipo' => $tipo,
          'monto' => abs($diferencia),
          'concepto' => $concepto,
          'descripcion' => $descripcion
        ]);
        $caja->movimientos()->save($movimiento);
      }

      DB::commit();

      $mensaje = 'Caja cerrada exitosamente.';
      if (abs($diferencia) > 0.01) {
        $mensaje .= sprintf(' Se registró una diferencia de %s %s.', 
          $diferencia > 0 ? '+' : '',
          number_format($diferencia, 2)
        );
      }

      // Responder según el tipo de petición
      if ($request->ajax()) {
        return response()->json([
          'success' => true,
          'message' => $mensaje,
          'redirect' => route('cajas.index')
        ]);
      }

      return redirect()->route('cajas.index')
        ->with('success', $mensaje);
    } catch (\Exception $e) {
      DB::rollback();
      \Log::error('Error al cerrar caja: ' . $e->getMessage(), [
        'caja_id' => $caja->id,
        'user_id' => \Auth::id(),
        'saldo_final' => $request->saldo_final
      ]);
      
      $errorMessage = 'Error al cerrar la caja: ' . $e->getMessage();
      
      // Responder según el tipo de petición
      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => $errorMessage
        ], 500);
      }
      
      return redirect()->back()
        ->with('error', $errorMessage)
        ->withInput();
    }
  }

  public function createMovimiento(Caja $caja)
  {
    $this->authorize('createMovimiento', $caja);

    if (!$caja->estado) {
      return redirect()->route('cajas.show', $caja)
        ->with('error', 'No se pueden registrar movimientos en una caja cerrada.');
    }

    return view('cajas.movimientos.create', compact('caja'));
  }

  public function storeMovimiento(Request $request, Caja $caja)
  {
    $this->authorize('createMovimiento', $caja);

    $request->validate([
      'tipo' => 'required|in:ingreso,egreso',
      'monto' => 'required|numeric|min:0.01',
      'concepto' => 'required|string|max:255',
      'observaciones' => 'nullable|string'
    ]);

    if (!$caja->estado) {
      return redirect()->route('cajas.show', $caja)
        ->with('error', 'No se pueden registrar movimientos en una caja cerrada.');
    }

    try {
      $caja->registrarMovimiento(
        $request->tipo,
        $request->monto,
        $request->concepto,
        $request->observaciones
      );

      return redirect()->route('cajas.show', $caja)
        ->with('success', 'Movimiento registrado exitosamente.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Error al registrar el movimiento: ' . $e->getMessage());
    }
  }

  public function movimientos(Caja $caja)
  {
    $this->authorize('viewMovimientos', $caja);

    $movimientos = $caja->movimientos()
      ->with(['user', 'movimientable'])
      ->latest()
      ->paginate(15);

    return view('cajas.movimientos', compact('caja', 'movimientos'));
  }

  public function arqueo(Caja $caja)
  {
    $this->authorize('realizarArqueo', $caja);

    if (!$caja->estado) {
      return redirect()->route('cajas.index')
        ->with('error', 'No se puede realizar arqueo en una caja cerrada.');
    }

    return view('cajas.arqueo', compact('caja'));
  }

  public function realizarArqueo(Request $request, Caja $caja)
  {
    $this->authorize('realizarArqueo', $caja);

    $request->validate([
      'monto_fisico' => 'required|numeric|min:0',
      'observaciones' => 'nullable|string'
    ]);

    if (!$caja->estado) {
      return redirect()->route('cajas.index')
        ->with('error', 'No se puede realizar arqueo en una caja cerrada.');
    }

    $diferencia = $request->monto_fisico - $caja->saldo_actual;

    try {
      // Registrar el arqueo como un movimiento
      $caja->registrarMovimiento(
        $diferencia >= 0 ? 'ingreso' : 'egreso',
        abs($diferencia),
        'Ajuste por arqueo de caja',
        $request->observaciones
      );

      return redirect()->route('cajas.show', $caja)
        ->with('success', 'Arqueo realizado exitosamente.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Error al realizar el arqueo: ' . $e->getMessage());
    }
  }

  public function registrarPago(Request $request, \App\Models\Reserva $reserva)
  {
    $request->validate([
      'monto' => 'required|numeric|min:0.01',
      'concepto' => 'required|string|max:255'
    ]);

    // Verificar que la reserva esté en Check-in
    if ($reserva->estado !== 'Check-in') {
      return redirect()->back()
        ->with('error', 'Solo se pueden registrar pagos para reservas en estado Check-in.');
    }

    // Verificar que el monto no exceda lo pendiente
    if ($request->monto > $reserva->pendiente) {
      return redirect()->back()
        ->with('error', 'El monto no puede ser mayor al pendiente de pago.')
        ->withInput();
    }

    // Buscar la caja activa del usuario
    $caja = \App\Models\Caja::where('user_id', \Auth::id())
      ->where('estado', true)
      ->first();

    if (!$caja) {
      return redirect()->back()
        ->with('error', 'No hay una caja abierta para registrar el pago.');
    }

    try {
      // Registrar el pago en la caja
      $caja->registrarMovimiento(
        'ingreso',
        $request->monto,
        $request->concepto,
        'Pago parcial para habitación ' . $reserva->habitacion->numero . ' a nombre de ' . $reserva->nombre_cliente,
        $reserva
      );

      return redirect()->back()
        ->with('success', 'Pago registrado exitosamente.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Error al registrar el pago: ' . $e->getMessage());
    }
  }
}
