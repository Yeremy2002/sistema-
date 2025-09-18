<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // Roles
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // Habitaciones
            'habitaciones.ver',
            'habitaciones.crear',
            'habitaciones.editar',
            'habitaciones.eliminar',

            // Reservas
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.eliminar',
            'reservas.checkin',
            'reservas.checkout',

            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // Categorías
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',

            // Niveles
            'niveles.ver',
            'niveles.crear',
            'niveles.editar',
            'niveles.eliminar',

            // Caja
            'caja.ver',
            'caja.crear',
            'caja.editar',
            'caja.eliminar',

            // Movimientos de caja
            'movimientos.ver',
            'movimientos.crear',
            'movimientos.editar',
            'movimientos.eliminar',

            // Gastos
            'gastos.ver',
            'gastos.crear',
            'gastos.editar',
            'gastos.eliminar',

            // Mantenimiento
            'mantenimiento.ver',
            'mantenimiento.crear',
            'mantenimiento.editar',
            'mantenimiento.eliminar',

            // Limpieza
            'limpieza.ver',
            'limpieza.crear',
            'limpieza.editar',
            'limpieza.eliminar',

            // Reportes
            'reportes.ver',

            // Configuración
            'configuracion.ver',
            'configuracion.editar',

            // Dashboard
            'dashboard.ver',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $recepcionistaRole = Role::firstOrCreate(['name' => 'recepcionista', 'guard_name' => 'web']);
        $limpiezaRole = Role::firstOrCreate(['name' => 'limpieza', 'guard_name' => 'web']);

        // Asignar todos los permisos al admin
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos específicos al recepcionista
        $recepcionistaRole->givePermissionTo([
            'dashboard.ver',
            'habitaciones.ver',
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.checkin',
            'reservas.checkout',
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'categorias.ver',
            'niveles.ver',
            'caja.ver',
            'movimientos.ver',
            'movimientos.crear',
            'gastos.ver',
            'gastos.crear',
            'reportes.ver',
        ]);

        // Asignar permisos específicos al personal de limpieza
        $limpiezaRole->givePermissionTo([
            'dashboard.ver',
            'habitaciones.ver',
            'limpieza.ver',
            'limpieza.crear',
            'limpieza.editar',
            'mantenimiento.ver',
            'mantenimiento.crear',
        ]);
    }
}
