@extends('layouts.app')

@section('title', 'Panel Técnico')

@section('content')
<div class="container mt-4">

    <h2 class="fw-bold mb-4">🔧 Panel del Técnico</h2>

    <div class="row g-3">
        <x-dashboard-card title="Tickets asignados" :value="$asignados" color="primary"/>
        <x-dashboard-card title="En proceso" :value="$en_proceso" color="info"/>
        <x-dashboard-card title="Esperando cliente" :value="$espera_cliente" color="warning"/>
        <x-dashboard-card title="Terminados" :value="$terminados" color="success"/>
    </div>

    <hr class="my-4">

    <h4 class="fw-bold">📌 Mis tickets asignados</h4>

    <ul class="list-group">
        @foreach($misTickets as $t)
            <li class="list-group-item d-flex justify-content-between">
                <span>#{{ $t->id }} — {{ $t->subject }}</span>
                <a href="{{ route('tickets.show', $t) }}" class="btn btn-sm btn-outline-primary">Ver</a>
            </li>
        @endforeach
    </ul>
</div>
@endsection
