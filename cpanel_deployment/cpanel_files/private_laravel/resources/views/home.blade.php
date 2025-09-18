@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>{{$hotel->nombre}}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            {{ __('You are logged in!') }}
        </div>
    </div>
@stop
