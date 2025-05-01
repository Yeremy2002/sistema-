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
            // Permisos de habitaciones
            'ver habitaciones',
            'crear habitaciones',
            'editar habitaciones',
            'eliminar habitaciones',
            'cambiar estado habitaciones',

            // Permisos de reservas
            'ver reservas',
            'crear reservas',
            'editar reservas',
            'eliminar reservas',
            'hacer checkin',
            'hacer checkout',

            // Permisos de clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',

            // Permisos de caja
            'ver cajas',
            'abrir caja',
            'cerrar caja',
            'ver movimientos caja',
            'registrar ingreso',
            'registrar egreso',
            'ver arqueo caja',
            'realizar arqueo',
            'asignar caja',

            // Permisos para gastos
            'ver gastos',
            'crear gastos',
            'editar gastos',
            'eliminar gastos',
            'aprobar gastos',

            // Permisos de usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'desactivar usuarios',

            // Permisos de reportes
            'ver reportes',
            'generar reportes',

            // Permisos de configuración
            'ver configuracion',
            'editar configuracion',

            // Permisos de mantenimiento
            'registrar limpieza',
            'registrar reparacion'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Super Administrador
        $role = Role::create(['name' => 'Super Administrador']);
        $role->givePermissionTo(Permission::all());

        // Gerente
        $role = Role::create(['name' => 'Gerente']);
        $role->givePermissionTo([
            'ver habitaciones',
            'ver arqueo caja',
            'realizar arqueo',
            'ver clientes',
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'desactivar usuarios',
            'ver reportes',
            'generar reportes',
            'ver configuracion',
            'editar configuracion',
            'aprobar gastos'
        ]);

        // Administrador
        $role = Role::create(['name' => 'Administrador']);
        $role->givePermissionTo([
            'ver habitaciones',
            'crear habitaciones',
            'editar habitaciones',
            'cambiar estado habitaciones',
            'ver reservas',
            'crear reservas',
            'editar reservas',
            'hacer checkin',
            'hacer checkout',
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
            'ver cajas',
            'abrir caja',
            'cerrar caja',
            'ver movimientos caja',
            'registrar ingreso',
            'registrar egreso',
            'ver arqueo caja',
            'realizar arqueo',
            'ver gastos',
            'crear gastos',
            'editar gastos',
            'eliminar gastos'
        ]);

        // Conserje
        $role = Role::create(['name' => 'Conserje']);
        $role->givePermissionTo([
            'ver habitaciones',
            'registrar limpieza',
            'registrar reparacion'
        ]);
    }
}
