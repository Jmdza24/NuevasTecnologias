<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    // Función interna para registrar actividad
    private function addLog(Ticket $ticket, string $action, ?string $description = null)
    {
        \App\Models\TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => $action,
            'description' => $description,
        ]);
    }

    /**
     * Lista de tickets (según el rol).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base de la consulta
        $query = Ticket::query();

        // CLIENTE → solo sus tickets
        if ($user->role === 'cliente') {
            $query->where('created_by', $user->id);
        }

        // TÉCNICO → solo tickets asignados
        if ($user->role === 'tecnico') {
            $query->where('assigned_to', $user->id);
        }

        // BUSCADOR
        if ($request->filled('buscar')) {
            $query->where('subject', 'like', '%' . $request->buscar . '%');
        }

        // ESTADO
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('status', $request->estado);
        }

        // FECHAS
        if ($request->filled('fecha_inicial')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicial);
        }

        if ($request->filled('fecha_final')) {
            $query->whereDate('created_at', '<=', $request->fecha_final);
        }

        // FILTROS EXCLUSIVOS ADMIN
        if ($user->role === 'admin') {

            if ($request->filled('cliente_id')) {
                $query->where('created_by', $request->cliente_id);
            }

            if ($request->filled('tecnico_id')) {
                $query->where('assigned_to', $request->tecnico_id);
            }
        }

        $tickets = $query->latest()->get();

        // Listas para filtros del admin
        $clientes = $user->role === 'admin'
            ? \App\Models\User::where('role', 'cliente')->get()
            : null;

        $tecnicos = $user->role === 'admin'
            ? \App\Models\User::where('role', 'tecnico')->get()
            : null;

        return view('tickets.index', compact('tickets', 'user', 'clientes', 'tecnicos'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Guardar ticket (solo cliente).
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
        ]);

        // Crear ticket correctamente
        $ticket = Ticket::create([
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'open',
            'created_by' => Auth::id(),
            'assigned_to' => null,
            'closed_at' => null,
        ]);

        // Registrar log de creación
        $this->addLog($ticket, 'ticket creado', 'El cliente creó el ticket.');

        return redirect()
            ->route('tickets.index')
            ->with('success', 'El ticket fue creado correctamente.');
    }

    /**
     * Ver ticket.
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role === 'cliente' && $ticket->created_by !== $user->id) {
            abort(403, 'No puedes ver este ticket.');
        }

        if ($user->role === 'tecnico' && $ticket->assigned_to !== $user->id) {
            abort(403, 'No puedes ver este ticket.');
        }

        return view('tickets.show', compact('ticket', 'user'));
    }

    /**
     * Editar ticket.
     */
    public function edit(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role === 'cliente') {
            abort(403, 'No tienes permiso para editar este ticket.');
        }

        if ($user->role === 'tecnico' && $ticket->assigned_to !== $user->id) {
            abort(403, 'No puedes editar este ticket.');
        }

        $technicians = $user->role === 'admin'
            ? \App\Models\User::where('role', 'tecnico')->get()
            : null;

        return view('tickets.edit', compact('ticket', 'user', 'technicians'));
    }

    /**
     * Actualizar ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role === 'cliente') abort(403);
        if ($user->role === 'tecnico' && $ticket->assigned_to !== $user->id) abort(403);

        $rules = [
            'status' => 'required|in:open,in_progress,waiting_client,finished,closed',
        ];

        if ($user->role === 'admin') {
            $rules['assigned_to'] = 'nullable|exists:users,id';
        }

        $data = $request->validate($rules);

        // ASIGNACIÓN DEL TÉCNICO (ADMIN SOLO)
        if ($user->role === 'admin') {

            if ($ticket->assigned_to != ($data['assigned_to'] ?? null)) {

                // Validación extra (solo técnicos o null)
                if (!empty($data['assigned_to'])) {

                    $tecnico = \App\Models\User::find($data['assigned_to']);

                    if (!$tecnico || $tecnico->role !== 'tecnico') {
                        abort(403, 'Solo se puede asignar técnicos.');
                    }
                }

                $this->addLog(
                    $ticket,
                    'técnico asignado',
                    'Asignado a usuario ID ' . ($data['assigned_to'] ?? 'Ninguno')
                );
            }

            $ticket->assigned_to = $data['assigned_to'] ?? null;
        }

        // TÉCNICO no puede cerrar
        if ($user->role === 'tecnico' && $data['status'] === 'closed') {
            abort(403, 'El técnico no puede cerrar tickets.');
        }

        // CAMBIO DE ESTADO
        if ($ticket->status !== $data['status']) {
            $this->addLog(
                $ticket,
                'estado cambiado',
                'Estado cambiado de ' . $ticket->status . ' a ' . $data['status']
            );
        }

        $ticket->status = $data['status'];

        if ($ticket->status === 'closed') {
            $ticket->closed_at = now();
        }

        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'El ticket fue actualizado correctamente.');
    }

    /**
     * Cerrar (solo cliente).
     */
    public function close(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role !== 'cliente') abort(403);
        if ($ticket->created_by !== $user->id) abort(403);

        if ($ticket->status === 'closed') {
            return redirect()->route('tickets.show', $ticket);
        }

        $ticket->status = 'closed';
        $ticket->closed_at = now();
        $ticket->save();

        // Registrar log
        $this->addLog($ticket, 'ticket cerrado', 'El cliente cerró el ticket.');

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket cerrado exitosamente.');
    }

    /**
     * Eliminar ticket (solo admin).
     */
    public function destroy(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') abort(403);

        $this->addLog($ticket, 'ticket eliminado', 'Ticket eliminado por administrador.');

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'El ticket fue eliminado correctamente.');
    }

    /**
     * Tomar ticket (solo técnico).
     */
    public function take(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role !== 'tecnico') abort(403);

        if ($ticket->assigned_to !== null) {
            return redirect()
                ->route('tickets.index')
                ->with('success', 'Este ticket ya está asignado.');
        }

        $ticket->assigned_to = $user->id;
        $ticket->status = 'in_progress';
        $ticket->save();

        // Registrar log
        $this->addLog($ticket, 'ticket tomado', 'El técnico tomó el ticket.');

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Has tomado el ticket exitosamente.');
    }
}
