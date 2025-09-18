<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categorias = [
      [
        'nombre' => 'Estandar',
        'precio' => 300.00,
        'descripcion' => 'Habitación estándar con cama matrimonial',
        'capacidad' => 2
      ],
      [
        'nombre' => 'Superior',
        'precio' => 400.00,
        'descripcion' => 'Habitación superior con vista exterior',
        'capacidad' => 2
      ],
      [
        'nombre' => 'Suite',
        'precio' => 600.00,
        'descripcion' => 'Suite con sala de estar y jacuzzi',
        'capacidad' => 4
      ]
    ];

    foreach ($categorias as $categoria) {
      Categoria::create($categoria);
    }
  }
}
