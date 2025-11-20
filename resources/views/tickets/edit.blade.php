@extends('layouts.app')

@section('title', 'Editar Ticket')

@section('content')
<div class="container mt-5">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-pencil-square"></i> Editar Ticket #{{ $ticket->id }}
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <!-- Mensajes -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- SOLO ADMIN PUEDE ASIGNAR TÉCNICO -->
                @if($user->role === 'admin')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asignar técnico</label>
                        <select name="assigned_to" class="form-select">

                            <option value="">Sin asignar</option>

                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}"
                                    {{ $ticket->assigned_to == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }} — {{ $tech->email }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                @endif


                <!-- CAMBIO DE ESTADO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado del ticket</label>
                    <select name="status" class="form-select">

                        <option value="open"           {{ $ticket->status=='open' ? 'selected' : '' }}>Abierto</option>
                        <option value="in_progress"    {{ $ticket->status=='in_progress' ? 'selected' : '' }}>En proceso</option>
                        <option value="waiting_client" {{ $ticket->status=='waiting_client' ? 'selected' : '' }}>Esperando cliente</option>
                        <option value="finished"       {{ $ticket->status=='finished' ? 'selected' : '' }}>Terminado</option>

                        @if($user->role === 'admin')
                            <option value="closed" {{ $ticket->status=='closed' ? 'selected' : '' }}>Cerrado</option>
                        @endif

                    </select>
                </div>

                <!-- BOTONES -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>

                    <button class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
