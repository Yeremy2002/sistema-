<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nivel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nivels';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    // Relación con habitaciones
    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }
}
