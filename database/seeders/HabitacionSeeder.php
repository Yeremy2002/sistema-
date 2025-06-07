<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habitacion;
use App\Models\Categoria;
use App\Models\Nivel;

class HabitacionSeeder extends Seeder
{
  public function run()
  {
    // Obtener las categorías existentes
    $categorias = Categoria::all();

    if ($categorias->isEmpty()) {
      // Si no hay categorías, crear algunas básicas
      $categorias = [
        Categoria::create(['nombre' => 'Estandar', 'descripcion' => 'Habitación estándar']),
        Categoria::create(['nombre' => 'Suite', 'descripcion' => 'Suite de lujo']),
        Categoria::create(['nombre' => 'Familiar', 'descripcion' => 'Habitación familiar']),
      ];
    }

    // Obtener los niveles existentes
    $niveles = Nivel::all();

    if ($niveles->isEmpty()) {
      // Si no hay niveles, crear algunos básicos
      $niveles = [
        Nivel::create(['nombre' => 'Nivel 1']),
        Nivel::create(['nombre' => 'Nivel 2']),
      ];
    }

    // Crear habitaciones para cada categoría y nivel
    foreach ($categorias as $categoria) {
      foreach ($niveles as $nivel) {
        for ($i = 1; $i <= 2; $i++) {
          $numero = $categoria->id . $nivel->id . $i;
          Habitacion::create([
            'numero' => $numero,
            'nivel_id' => $nivel->id,
            'categoria_id' => $categoria->id,
            'estado' => 'Disponible',
            'precio' => $categoria->nombre === 'Suite' ? 200 : ($categoria->nombre === 'Familiar' ? 150 : 100),
            'descripcion' => "Habitación {$categoria->nombre} número {$numero}"
          ]);
        }
      }
    }
  }
}
