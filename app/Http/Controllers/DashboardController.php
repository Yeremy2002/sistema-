<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function __construct()
  {
    $this->middleware('permission:ver habitaciones|ver reservas|ver clientes|ver reportes');
  }

  public function index()
  {
    $habitaciones = Habitacion::with(['categoria', 'nivel'])
      ->orderBy('numero')
      ->get();

    return view('admin.dashboard', compact('habitaciones'));
  }
}
