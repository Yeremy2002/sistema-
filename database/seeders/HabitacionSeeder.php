<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Categoria;
use App\Models\Nivel;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categorias = Categoria::all();
    $niveles = Nivel::all();

    // Crear 3 habitaciones por nivel
    foreach ($niveles as $nivel) {
      foreach ($categorias as $index => $categoria) {
        Habitacion::create([
          'numero' => $nivel->id . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
          'nivel_id' => $nivel->id,
          'categoria_id' => $categoria->id,
          'estado' => 'disponible',
          'descripcion' => 'Habitación ' . $categoria->nombre . ' en ' . $nivel->nombre
        ]);
      }
    }
  }
}
