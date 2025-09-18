                @extends('adminlte::page')
                @section('title', 'Editar Rol')
                @section('content_header')
                    <h1>Editar Rol</h1>
                @stop
                @section('content')
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('roles.update', $role) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="name">Nombre del Rol</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ old('name', $role->name) }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Permisos</label>
                                    <div id="accordionPerms">
                                        @php
                                            $permsMap = [
                                                'Gestión de Habitaciones' => [
                                                    'Habitaciones' => [
                                                        ['ver habitaciones', 'Ver Habitaciones'],
                                                        ['crear habitaciones', 'Crear Habitaciones'],
                                                        ['editar habitaciones', 'Editar Habitaciones'],
                                                        ['eliminar habitaciones', 'Eliminar Habitaciones'],
                                                    ],
                                                    'Categorías' => [
                                                        ['ver categorias', 'Ver Categorías'],
                                                        ['crear categorias', 'Crear Categorías'],
                                                        ['editar categorias', 'Editar Categorías'],
                                                        ['eliminar categorias', 'Eliminar Categorías'],
                                                    ],
                                                    'Niveles' => [
                                                        ['ver niveles', 'Ver Niveles'],
                                                        ['crear niveles', 'Crear Niveles'],
                                                        ['editar niveles', 'Editar Niveles'],
                                                        ['eliminar niveles', 'Eliminar Niveles'],
                                                    ],
                                                ],
                                                'Reservas' => [
                                                    'Reservas' => [
                                                        ['ver reservas', 'Ver Reservas'],
                                                        ['crear reservas', 'Crear Reservas'],
                                                        ['editar reservas', 'Editar Reservas'],
                                                        ['eliminar reservas', 'Eliminar Reservas'],
                                                        ['cancelar reservas', 'Cancelar Reservas'],
                                                    ],
                                                ],
                                                'Clientes' => [
                                                    'Clientes' => [
                                                        ['ver clientes', 'Ver Clientes'],
                                                        ['crear clientes', 'Crear Clientes'],
                                                        ['editar clientes', 'Editar Clientes'],
                                                        ['eliminar clientes', 'Eliminar Clientes'],
                                                    ],
                                                ],
                                                'Reportes' => [
                                                    'Reportes' => [['ver reportes', 'Ver Reportes']],
                                                ],
                                                'Mantenimiento' => [
                                                    'Mantenimiento General' => [
                                                        ['ver mantenimiento', 'Ver Mantenimiento'],
                                                    ],
                                                    'Limpieza' => [['registrar limpieza', 'Registrar Limpieza']],
                                                    'Reparaciones' => [
                                                        ['registrar reparaciones', 'Registrar Reparación'],
                                                    ],
                                                ],
                                                'Configuración' => [
                                                    'Configuración General' => [
                                                        ['ver configuracion', 'Ver Configuración'],
                                                        ['editar configuracion', 'Editar Configuración'],
                                                    ],
                                                    'Usuarios' => [
                                                        ['ver usuarios', 'Ver Usuarios'],
                                                        ['crear usuarios', 'Crear Usuarios'],
                                                        ['editar usuarios', 'Editar Usuarios'],
                                                        ['eliminar usuarios', 'Eliminar Usuarios'],
                                                    ],
                                                    'Roles y Permisos' => [
                                                        ['ver roles', 'Ver Roles y Permisos'],
                                                        ['crear roles', 'Crear Roles y Permisos'],
                                                        ['editar roles', 'Editar Roles y Permisos'],
                                                        ['eliminar roles', 'Eliminar Roles y Permisos'],
                                                    ],
                                                ],
                                                'Gestión de Caja' => [
                                                    'Caja' => [
                                                        ['ver cajas', 'Ver Cajas'],
                                                        ['abrir caja', 'Abrir Caja'],
                                                        ['cerrar caja', 'Cerrar Caja'],
                                                        ['ver arqueo caja', 'Ver/Realizar Arqueo'],
                                                        ['realizar arqueo', 'Realizar Arqueo'],
                                                        ['asignar caja', 'Asignar Caja'],
                                                    ],
                                                    'Movimientos de Caja' => [
                                                        ['ver movimientos caja', 'Ver Movimientos de Caja'],
                                                        ['registrar movimiento', 'Registrar Movimiento de Caja'],
                                                    ],
                                                ],
                                            ];
                                        @endphp
                                        @foreach ($permsMap as $modulo => $submodulos)
                                            <div class="card card-primary card-outline mb-2">
                                                <div class="card-header d-flex align-items-center"
                                                    id="heading_{{ Str::slug($modulo) }}" style="cursor:pointer;"
                                                    data-toggle="collapse" data-target="#collapse_{{ Str::slug($modulo) }}"
                                                    aria-expanded="true" aria-controls="collapse_{{ Str::slug($modulo) }}">
                                                    <h5 class="mb-0 flex-grow-1">{{ $modulo }}</h5>
                                                    <div class="card-tools">
                                                        <button type="button" class="btn btn-tool" data-toggle="collapse"
                                                            data-target="#collapse_{{ Str::slug($modulo) }}"
                                                            aria-expanded="true"
                                                            aria-controls="collapse_{{ Str::slug($modulo) }}">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="collapse_{{ Str::slug($modulo) }}" class="collapse show"
                                                    aria-labelledby="heading_{{ Str::slug($modulo) }}"
                                                    data-parent="#accordionPerms">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            @foreach ($submodulos as $submodulo => $perms)
                                                                <div class="col-md-6 col-lg-4 mb-3">
                                                                    <strong>{{ $submodulo }}</strong>
                                                                    <div class="row">
                                                                        @foreach ($perms as [$permName, $permLabel])
                                                                            <div class="col-12">
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input"
                                                                                        type="checkbox" name="permissions[]"
                                                                                        value="{{ $permName }}"
                                                                                        id="perm_{{ $permName }}"
                                                                                        {{ in_array($permName, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                                                                                    <label class="form-check-label"
                                                                                        for="perm_{{ $permName }}">{{ $permLabel }}</label>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Actualizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @stop
