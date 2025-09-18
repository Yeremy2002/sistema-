<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'capacidad'
    ];

    // Relación con habitaciones
    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }
}
