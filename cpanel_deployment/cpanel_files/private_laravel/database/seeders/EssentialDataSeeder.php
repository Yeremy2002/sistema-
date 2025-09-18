<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Nivel;
use App\Models\Habitacion;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class EssentialDataSeeder extends Seeder
{
  public function run()
  {
    // Crear roles si no existen
    $roles = ['admin', 'recepcionista', 'cajero'];
    foreach ($roles as $roleName) {
      Role::firstOrCreate(['name' => $roleName]);
    }

    // Crear usuario administrador si no existe
    $admin = User::firstOrCreate(
      ['email' => 'admin@hotel.com'],
      [
        'name' => 'Administrador',
        'password' => Hash::make('password'),
        'active' => true
      ]
    );
    $admin->assignRole('admin');

    // Crear categorías si no existen
    $categorias = [
      [
        'nombre' => 'Individual',
        'descripcion' => 'Habitación para una persona',
        'precio' => 200.00,
        'capacidad' => 1
      ],
      [
        'nombre' => 'Doble',
        'descripcion' => 'Habitación con dos camas',
        'precio' => 350.00,
        'capacidad' => 2
      ],
      [
        'nombre' => 'Suite',
        'descripcion' => 'Habitación de lujo',
        'precio' => 500.00,
        'capacidad' => 4
      ]
    ];

    foreach ($categorias as $categoria) {
      Categoria::firstOrCreate(
        ['nombre' => $categoria['nombre']],
        [
          'descripcion' => $categoria['descripcion'],
          'precio' => $categoria['precio'],
          'capacidad' => $categoria['capacidad']
        ]
      );
    }

    // Crear niveles si no existen
    $niveles = [
      ['nombre' => 'Primer Nivel', 'descripcion' => 'Planta baja'],
      ['nombre' => 'Segundo Nivel', 'descripcion' => 'Primera planta'],
      ['nombre' => 'Tercer Nivel', 'descripcion' => 'Segunda planta']
    ];

    foreach ($niveles as $nivel) {
      Nivel::firstOrCreate(
        ['nombre' => $nivel['nombre']],
        ['descripcion' => $nivel['descripcion']]
      );
    }

    // Crear habitaciones si no existen
    $habitaciones = [
      ['numero' => '101', 'categoria_id' => 1, 'nivel_id' => 1, 'estado' => 'Disponible'],
      ['numero' => '102', 'categoria_id' => 2, 'nivel_id' => 1, 'estado' => 'Disponible'],
      ['numero' => '201', 'categoria_id' => 2, 'nivel_id' => 2, 'estado' => 'Disponible'],
      ['numero' => '202', 'categoria_id' => 3, 'nivel_id' => 2, 'estado' => 'Disponible'],
      ['numero' => '301', 'categoria_id' => 3, 'nivel_id' => 3, 'estado' => 'Disponible']
    ];

    foreach ($habitaciones as $habitacion) {
      Habitacion::firstOrCreate(
        ['numero' => $habitacion['numero']],
        [
          'categoria_id' => $habitacion['categoria_id'],
          'nivel_id' => $habitacion['nivel_id'],
          'estado' => $habitacion['estado']
        ]
      );
    }
  }
}
