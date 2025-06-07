<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoCaja;

class ReporteController extends Controller
{
  public function ingresos()
  {
    $totalIngresos = MovimientoCaja::where('tipo', 'ingreso')->sum('monto');
    $totalEgresos = MovimientoCaja::where('tipo', 'egreso')->sum('monto');
    $movimientos = MovimientoCaja::with(['caja', 'user'])
      ->orderByDesc('created_at')
      ->limit(50)
      ->get();
    return view('reportes.ingresos', compact('totalIngresos', 'totalEgresos', 'movimientos'));
  }
}
