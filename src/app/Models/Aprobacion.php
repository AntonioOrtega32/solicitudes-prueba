<?php

namespace App\Models;

use App\Enums\EstadoAprobacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aprobacion extends Model
{
    protected $table = 'aprobaciones'; // tabla de aprobaciones por nivel

    protected $fillable = [
        'solicitud_id', 'aprobador_id', 'nivel', 'ciclo',
        'estado', 'comentario', 'decidida_en',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoAprobacion::class,
            'decidida_en' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }
}