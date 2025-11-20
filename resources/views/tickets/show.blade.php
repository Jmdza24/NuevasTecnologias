@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-ticket-detailed"></i> Ticket #{{ $ticket->id }}
    </h2>

    <div class="row">

        <!-- Columna izquierda: info -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    Información del Ticket
                </div>
                <div class="card-body">

                    <p><strong>Asunto:</strong> {{ $ticket->subject }}</p>

                    <p><strong>Descripción:</strong><br>
                        {{ $ticket->description }}
                    </p>

                    <p><strong>Estado:</strong><br>
                        <span class="badge bg-{{ $colors[$ticket->status] ?? 'dark' }} px-3 py-2">
                            {{ $labels[$ticket->status] ?? $ticket->status }}
                        </span>
                    </p>

                    <p><strong>Cliente:</strong> {{ $ticket->creator->name }}</p>

                    <p><strong>Técnico asignado:</strong>
                        {{ $ticket->technician->name ?? 'Sin asignar' }}
                    </p>

                    <p><strong>Creado:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>

                    @if($ticket->closed_at)
                        <p><strong>Cerrado:</strong> {{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                    @endif

                    <hr>

                    <!-- Acciones -->
                    @include('tickets.partials.actions')

                </div>
            </div>
        </div>

        <!-- Columna derecha: historial -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    Historial de Actividad
                </div>
                <div class="card-body">
                    @if($ticket->logs->isEmpty())
                        <p>No hay actividad registrada.</p>
                    @else
                        <ul class="list-group">
                            @foreach($ticket->logs as $log)
                                <li class="list-group-item">
                                    <strong>{{ $log->action }}</strong><br>
                                    <small>{{ $log->description }}</small><br>
                                    <small class="text-muted">
                                        Por: {{ $log->user->name }} — 
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
