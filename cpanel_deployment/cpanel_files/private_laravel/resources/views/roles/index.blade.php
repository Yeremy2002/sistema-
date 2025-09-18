@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <h1>Roles del Sistema</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('roles.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Rol
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Permisos</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach ($role->permissions as $perm)
                                        <span class="badge badge-info">{{ $perm->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $role->users_count ?? $role->users()->count() }}</td>
                                <td>
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary"><i
                                            class="fas fa-edit"></i></a>
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar rol?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay roles registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
