<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $adminRole = Role::create(['name' => 'Administrador']);
        $receptionistRole = Role::create(['name' => 'Recepcionista']);

        // Crear permisos
        $permissions = [
            // Cajas
            'ver cajas',
            'abrir caja',
            'cerrar caja',
            'ver movimientos caja',
            'registrar movimiento',
            'ver arqueo caja',
            'realizar arqueo',
            'asignar caja',

            // Habitaciones
            'ver habitaciones',
            'crear habitaciones',
            'editar habitaciones',
            'eliminar habitaciones',

            // Categorías y Niveles
            'ver categorias',
            'crear categorias',
            'editar categorias',
            'eliminar categorias',
            'ver niveles',
            'crear niveles',
            'editar niveles',
            'eliminar niveles',

            // Reservas
            'ver reservas',
            'crear reservas',
            'editar reservas',
            'cancelar reservas',

            // Clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',

            // Reportes y Mantenimiento
            'ver reportes',
            'ver mantenimiento',
            'registrar limpieza',
            'registrar reparaciones',

            // Configuración y Usuarios
            'ver configuracion',
            'editar configuracion',
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar todos los permisos al rol Super Admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@hotel.com',
            'password' => bcrypt('password'),
            'active' => true
        ]);

        $admin->assignRole('Super Admin');

        // Crear datos básicos del hotel
        $this->call([
            HotelSeeder::class,
            CategoriaSeeder::class,
            NivelSeeder::class,
            HabitacionSeeder::class
        ]);
    }
}
