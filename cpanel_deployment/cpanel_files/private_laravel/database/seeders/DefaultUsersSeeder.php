<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
  public function run(): void
  {
    // Crear roles si no existen
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Administrador']);
    $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

    // Verificar si hay usuarios
    if (User::count() === 0) {
      // Crear usuario Super Administrador
      $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'active' => true
      ]);
      $superAdmin->assignRole($superAdminRole);

      // Crear usuario Gerente
      $gerente = User::create([
        'name' => 'Gerente',
        'email' => 'gerente@hotel.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'active' => true
      ]);
      $gerente->assignRole('Gerente');

      // Crear usuario Administrador
      $admin = User::create([
        'name' => 'Administrador',
        'email' => 'administrador@hotel.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'active' => true
      ]);
      $admin->assignRole($adminRole);

      // Crear usuario Conserje
      $conserje = User::create([
        'name' => 'Conserje',
        'email' => 'conserje@hotel.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'active' => true
      ]);
      $conserje->assignRole('Conserje');

      $this->command->info('Usuarios por defecto creados exitosamente.');
    } else {
      $this->command->info('Ya existen usuarios en el sistema.');
    }
  }
}
