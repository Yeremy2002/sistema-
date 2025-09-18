<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaNivelSeeder extends Seeder
{
  public function run()
  {
    // Limpiar tablas
    DB::table('categorias')->truncate();
    DB::table('nivels')->truncate();

    // Categorías
    DB::table('categorias')->insert([
      ['id' => 1, 'nombre' => 'Sencilla', 'descripcion' => 'Habitación sencilla', 'estado' => true],
      ['id' => 2, 'nombre' => 'Doble', 'descripcion' => 'Habitación doble', 'estado' => true],
      ['id' => 3, 'nombre' => 'Triple', 'descripcion' => 'Habitación triple', 'estado' => true],
      ['id' => 4, 'nombre' => 'Minisuite', 'descripcion' => 'Minisuite', 'estado' => true],
      ['id' => 5, 'nombre' => 'Suite', 'descripcion' => 'Suite', 'estado' => true],
    ]);

    // Niveles
    DB::table('nivels')->insert([
      ['id' => 1, 'nombre' => 'Primer Piso', 'descripcion' => 'Planta baja', 'estado' => true],
      ['id' => 2, 'nombre' => 'Segundo Piso', 'descripcion' => 'Primera planta', 'estado' => true],
      ['id' => 3, 'nombre' => 'Tercer Piso', 'descripcion' => 'Segunda planta', 'estado' => true],
    ]);
  }
}
