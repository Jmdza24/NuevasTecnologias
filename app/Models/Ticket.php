<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'status',
        'created_by',
        'assigned_to',
        'closed_at'
    ];

    // Ticket pertenece al cliente que lo creó
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Ticket pertenece al técnico asignado
    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Un ticket puede tener muchos registros de actividad
    public function logs()
    {
        return $this->hasMany(TicketLog::class);
}

}
