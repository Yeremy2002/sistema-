@extends('adminlte::page')

@section('title', 'Detalles del Cliente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalles del Cliente</h1>
        <div>
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre:</label>
                        <p>{{ $cliente->nombre }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">NIT:</label>
                        <p>{{ $cliente->nit }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">DPI:</label>
                        <p>{{ $cliente->dpi }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Teléfono:</label>
                        <p>{{ $cliente->telefono }}</p>
                    </div>
                </div>
            </div>

            @if ($cliente->created_at)
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Fecha de Registro:</label>
                            <p>{{ $cliente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Última Actualización:</label>
                            <p>{{ $cliente->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: #666;
            margin-bottom: 0.2rem;
        }

        .form-group p {
            font-size: 1.1rem;
            margin-bottom: 0;
        }
    </style>
@stop
