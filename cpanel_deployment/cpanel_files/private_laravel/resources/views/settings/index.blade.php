@extends('adminlte::page')

@section('title', 'Configuración del Hotel')

@section('content_header')
    <h1>Configuración del Hotel</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="hotel_name">Nombre del Hotel</label>
                    <input type="text" name="hotel_name" id="hotel_name" class="form-control"
                        value="{{ $settings->hotel_name ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" name="address" id="address" class="form-control"
                        value="{{ $settings->address ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label for="nit">NIT</label>
                    <input type="text" name="nit" id="nit" class="form-control"
                        value="{{ $settings->nit ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label for="logo_path">Logo</label>
                    <input type="file" name="logo_path" id="logo_path" class="form-control">
                    @if ($hotel && $hotel->logo)
                        <img src="{{ Storage::url($hotel->logo) }}" alt="Logo del Hotel" class="img-fluid"
                            style="max-height: 100px;">
                    @elseif ($settings && $settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo del Hotel" class="img-fluid"
                            style="max-height: 100px;">
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>
@stop
