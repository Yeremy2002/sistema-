<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'nit',
        'dpi',
        'telefono',
        'email',
        'direccion',
        'documento',
        'origen',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    // Mutador para siempre guardar el nombre en mayúsculas
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }
}
