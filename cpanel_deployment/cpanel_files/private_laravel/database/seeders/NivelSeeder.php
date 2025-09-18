<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $niveles = [
      [
        'nombre' => 'Planta Baja',
        'descripcion' => 'Nivel de acceso principal'
      ],
      [
        'nombre' => 'Primer Piso',
        'descripcion' => 'Primer nivel de habitaciones'
      ],
      [
        'nombre' => 'Segundo Piso',
        'descripcion' => 'Segundo nivel de habitaciones'
      ]
    ];

    foreach ($niveles as $nivel) {
      Nivel::create($nivel);
    }
  }
}
