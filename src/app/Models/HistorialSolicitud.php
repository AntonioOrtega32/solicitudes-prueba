<?php

namespace App\Models;

use App\Enums\AccionHistorial;
use LogicException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialSolicitud extends Model
{
    protected $table = 'historial_solicitudes';

    const UPDATED_AT = null;

    protected $fillable = [
        'solicitud_id', 'usuario_id', 'accion', 'comentario', 'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'accion' => AccionHistorial::class,
            'metadatos' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('El historial es inmutable.'));
        static::deleting(fn () => throw new LogicException('El historial no se puede eliminar.'));
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}