<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
  protected $fillable = [
    'nombre',
    'nit',
    'nombre_fiscal',
    'direccion',
    'simbolo_moneda',
    'logo',
  ];

  // Solo habrá un registro en la tabla
  public static function getInfo()
  {
    return static::firstOrCreate([]);
  }
}
