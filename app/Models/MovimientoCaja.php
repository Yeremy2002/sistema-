<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoCaja extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'movimientos_caja';

  protected $fillable = [
    'caja_id',
    'user_id',
    'tipo',
    'monto',
    'concepto',
    'observaciones',
    'movimientable_type',
    'movimientable_id'
  ];

  protected $casts = [
    'monto' => 'decimal:2'
  ];

  public function caja()
  {
    return $this->belongsTo(Caja::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function movimientable()
  {
    return $this->morphTo();
  }
}
