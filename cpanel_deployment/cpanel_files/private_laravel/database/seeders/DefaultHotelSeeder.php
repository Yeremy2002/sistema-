<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Nivel;
use App\Models\Habitacion;

class DefaultHotelSeeder extends Seeder
{
  public function run(): void
  {
    // Verificar si hay categorías
    if (Categoria::count() === 0) {
      $categorias = [
        [
          'nombre' => 'Individual',
          'descripcion' => 'Habitación para una persona',
          'estado' => true
        ],
        [
          'nombre' => 'Doble',
          'descripcion' => 'Habitación para dos personas',
          'estado' => true
        ],
        [
          'nombre' => 'Suite',
          'descripcion' => 'Habitación de lujo con sala de estar',
          'estado' => true
        ],
        [
          'nombre' => 'Familiar',
          'descripcion' => 'Habitación amplia para familias',
          'estado' => true
        ]
      ];

      foreach ($categorias as $categoria) {
        Categoria::create($categoria);
      }
      $this->command->info('Categorías creadas exitosamente.');
    }

    // Verificar si hay niveles
    if (Nivel::count() === 0) {
      $niveles = [
        [
          'nombre' => 'Primer Piso',
          'descripcion' => 'Planta baja del hotel',
          'estado' => true
        ],
        [
          'nombre' => 'Segundo Piso',
          'descripcion' => 'Primera planta del hotel',
          'estado' => true
        ],
        [
          'nombre' => 'Tercer Piso',
          'descripcion' => 'Segunda planta del hotel',
          'estado' => true
        ]
      ];

      foreach ($niveles as $nivel) {
        Nivel::create($nivel);
      }
      $this->command->info('Niveles creados exitosamente.');
    }

    // Verificar si hay habitaciones
    if (Habitacion::count() === 0) {
      // Obtener IDs de categorías
      $categoriaIndividual = Categoria::where('nombre', 'Individual')->first();
      $categoriaDoble = Categoria::where('nombre', 'Doble')->first();
      $categoriaSuite = Categoria::where('nombre', 'Suite')->first();
      $categoriaFamiliar = Categoria::where('nombre', 'Familiar')->first();

      // Obtener IDs de niveles
      $primerPiso = Nivel::where('nombre', 'Primer Piso')->first();
      $segundoPiso = Nivel::where('nombre', 'Segundo Piso')->first();
      $tercerPiso = Nivel::where('nombre', 'Tercer Piso')->first();

      $habitaciones = [
        // Primer Piso
        [
          'numero' => '101',
          'categoria_id' => $categoriaIndividual->id,
          'nivel_id' => $primerPiso->id,
          'precio' => 50.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación individual con vista al jardín'
        ],
        [
          'numero' => '102',
          'categoria_id' => $categoriaDoble->id,
          'nivel_id' => $primerPiso->id,
          'precio' => 80.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación doble con balcón'
        ],
        [
          'numero' => '103',
          'categoria_id' => $categoriaSuite->id,
          'nivel_id' => $primerPiso->id,
          'precio' => 150.00,
          'estado' => 'disponible',
          'descripcion' => 'Suite de lujo con jacuzzi'
        ],

        // Segundo Piso
        [
          'numero' => '201',
          'categoria_id' => $categoriaIndividual->id,
          'nivel_id' => $segundoPiso->id,
          'precio' => 55.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación individual con vista a la ciudad'
        ],
        [
          'numero' => '202',
          'categoria_id' => $categoriaDoble->id,
          'nivel_id' => $segundoPiso->id,
          'precio' => 85.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación doble con terraza'
        ],
        [
          'numero' => '203',
          'categoria_id' => $categoriaFamiliar->id,
          'nivel_id' => $segundoPiso->id,
          'precio' => 120.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación familiar con sala de estar'
        ],

        // Tercer Piso
        [
          'numero' => '301',
          'categoria_id' => $categoriaDoble->id,
          'nivel_id' => $tercerPiso->id,
          'precio' => 90.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación doble con vista panorámica'
        ],
        [
          'numero' => '302',
          'categoria_id' => $categoriaSuite->id,
          'nivel_id' => $tercerPiso->id,
          'precio' => 180.00,
          'estado' => 'disponible',
          'descripcion' => 'Suite presidencial con terraza privada'
        ],
        [
          'numero' => '303',
          'categoria_id' => $categoriaFamiliar->id,
          'nivel_id' => $tercerPiso->id,
          'precio' => 140.00,
          'estado' => 'disponible',
          'descripcion' => 'Habitación familiar con dos baños'
        ]
      ];

      foreach ($habitaciones as $habitacion) {
        Habitacion::create($habitacion);
      }
      $this->command->info('Habitaciones creadas exitosamente.');
    }
  }
}
