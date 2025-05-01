<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable = [
    'hotel_name',
    'address',
    'nit',
    'logo_path',
    'phone',
    'email',
    'description'
  ];

  public static function getInstance()
  {
    return self::first() ?? self::create([
      'hotel_name' => 'Mi Hotel',
      'address' => 'Dirección por defecto',
      'nit' => '000000000',
      'phone' => '',
      'email' => '',
      'description' => ''
    ]);
  }
}
