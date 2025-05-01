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
            // Dashboard
            'dashboard.ver',

            // Gestión de Habitaciones
            'habitaciones.ver',
            'habitaciones.crear',
            'habitaciones.editar',
            'habitaciones.eliminar',
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',
            'niveles.ver',
            'niveles.crear',
            'niveles.editar',
            'niveles.eliminar',

            // Reservas
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.eliminar',
            'reservas.cancelar',

            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // Reportes
            'reportes.ver',
            'reportes.crear',
            'reportes.editar',
            'reportes.eliminar',

            // Mantenimiento General
            'mantenimiento.ver',
            'mantenimiento.crear',
            'mantenimiento.editar',
            'mantenimiento.eliminar',
            // Limpieza
            'limpieza.ver',
            'limpieza.registrar',
            'limpieza.editar',
            'limpieza.eliminar',
            // Reparaciones
            'reparaciones.ver',
            'reparaciones.registrar',
            'reparaciones.editar',
            'reparaciones.eliminar',

            // Configuración General
            'configuracion.ver',
            'configuracion.editar',
            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            // Roles y Permisos
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // Gestión de Caja
            'cajas.ver',
            'cajas.apertura',
            'cajas.cierre',
            'cajas.arqueo',
            'cajas.asignar',
            // Movimientos de Caja
            'cajas.movimientos.ver',
            'cajas.movimientos.crear',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
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
