@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Tickets</h2>
        <form method="GET" class="card p-3 mb-4">
            <div class="row">

                <!-- Buscar -->
                <div class="col-md-3 mb-2">
                    <label>Buscar por asunto</label>
                    <input type="text" name="buscar" class="form-control"
                        value="{{ request('buscar') }}">
                </div>

                <!-- Estado -->
                <div class="col-md-3 mb-2">
                    <label>Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos">Todos</option>
                        <option value="open" {{ request('estado')=='open' ? 'selected' : '' }}>Abierto</option>
                        <option value="in_progress" {{ request('estado')=='in_progress' ? 'selected' : '' }}>En proceso</option>
                        <option value="waiting_client" {{ request('estado')=='waiting_client' ? 'selected' : '' }}>Esperando cliente</option>
                        <option value="finished" {{ request('estado')=='finished' ? 'selected' : '' }}>Terminado</option>
                        <option value="closed" {{ request('estado')=='closed' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>

                <!-- Fechas -->
                <div class="col-md-3 mb-2">
                    <label>Fecha inicial</label>
                    <input type="date" name="fecha_inicial" class="form-control"
                        value="{{ request('fecha_inicial') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label>Fecha final</label>
                    <input type="date" name="fecha_final" class="form-control"
                        value="{{ request('fecha_final') }}">
                </div>

                <!-- Filtros extra para ADMIN -->
                @if($user->role === 'admin')
                    <div class="col-md-3 mb-2">
                        <label>Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                        {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Técnico</label>
                        <select name="tecnico_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}"
                                        {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                    {{ $tecnico->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Filtrar</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>

        </form>

        @if($user->role === 'cliente')
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                Crear Ticket
            </a>
        @endif
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Asunto</th>

                        @if($user->role !== 'cliente')
                            <th>Cliente</th>
                        @endif

                        <th>Estado</th>
                        <th>Técnico</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="text-nowrap">{{ $ticket->id }}</td>

                            <td class="text-nowrap">{{ $ticket->subject }}</td>

                            @if($user->role !== 'cliente')
                                <td class="text-nowrap">{{ $ticket->creator->name }}</td>
                            @endif

                            <td class="text-nowrap">
                                @php
                                    $colors = [
                                        'open'           => 'primary',
                                        'in_progress'    => 'warning text-dark',
                                        'waiting_client' => 'info text-dark',
                                        'finished'       => 'success',
                                        'closed'         => 'secondary'
                                    ];

                                    $labels = [
                                        'open'           => 'Abierto',
                                        'in_progress'    => 'En proceso',
                                        'waiting_client' => 'Esperando cliente',
                                        'finished'       => 'Terminado',
                                        'closed'         => 'Cerrado'
                                    ];
                                @endphp


                                <span class="badge bg-{{ $colors[$ticket->status] ?? 'dark' }} px-3 py-2">
                                    {{ $labels[$ticket->status] ?? $ticket->status }}
                                </span>
                            </td>

                            <td class="text-nowrap">{{ $ticket->technician->name ?? 'Sin asignar' }}</td>

                            <td class="text-nowrap">{{ $ticket->created_at->format('d/m/Y') }}</td>

                            <td class="text-nowrap">
                                <!-- Ver -->
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>


                                <!-- Tomar ticket para técnicos -->
                                @if($user->role === 'tecnico' && $ticket->assigned_to === null)
                                    <form action="{{ route('tickets.take', $ticket) }}" 
                                        method="POST" 
                                        class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-hand-index-thumb"></i> Tomar
                                        </button>

                                    </form>
                                @endif

                                <!-- Editar para técnicos y admin -->
                                @if($user->role === 'tecnico' || $user->role === 'admin')
                                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>

                                @endif

                                <!-- Cerrar para cliente -->
                                @if($user->role === 'cliente' && $ticket->status !== 'closed')
                                    <form action="{{ route('tickets.close', $ticket) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Cerrar
                                        </button>

                                    </form>
                                @endif

                                <!-- Eliminar solo admin -->
                                @if($user->role === 'admin')
                                    <form action="{{ route('tickets.destroy', $ticket) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar ticket?')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endif

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-3">
                                No hay tickets para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
