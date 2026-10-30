<?php

namespace App\Models;

use App\Enums\EstadoSolicitud;
use App\Enums\Prioridad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\EstadoAprobacion;
use App\Enums\Rol;
use Illuminate\Database\Eloquent\Builder;



class Solicitud extends Model
{
    protected $table = 'solicitudes'; 

    protected $fillable = [
        'solicitante_id', 'tipo_solicitud_id', 'titulo', 'descripcion',
        'prioridad', 'monto', 'datos', 'estado',
        'niveles_requeridos', 'nivel_actual', 'ciclo',
        'enviada_en', 'resuelta_en',
    ];

    protected function casts(): array
    {
        return [
            'prioridad' => Prioridad::class,
            'estado' => EstadoSolicitud::class,
            'monto' => 'decimal:2',
            'datos' => 'array',
            'enviada_en' => 'datetime',
            'resuelta_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Solicitud $solicitud) {
            $solicitud->folio = sprintf('SOL-%s-%06d', $solicitud->created_at->format('Y'), $solicitud->id);
            $solicitud->saveQuietly();
        });
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class, 'solicitud_id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(Adjunto::class, 'solicitud_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialSolicitud::class, 'solicitud_id')->orderBy('created_at');
    }

    public function esUrgente(): bool
    {
        return $this->prioridad === Prioridad::Urgente;
    }

    public function aprobacionPendientePara(User $usuario): ?Aprobacion
    {
        return $this->aprobaciones()
            ->where('ciclo', $this->ciclo)
            ->where('nivel', $this->nivel_actual)
            ->where('aprobador_id', $usuario->id)
            ->where('estado', EstadoAprobacion::Pendiente->value)
            ->first();
    }

    /** solicitudes puede listar cada rol */
    public function scopeVisiblesPara(Builder $query, User $usuario): Builder
    {
        return match ($usuario->rol) {
            Rol::Administrador => $query,
            Rol::Aprobador => $query->whereHas(
                'aprobaciones',
                fn ($q) => $q->where('aprobador_id', $usuario->id)
            ),
            default => $query->where('solicitante_id', $usuario->id),
        };
    }

    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($filtros['prioridad'] ?? null, fn ($q, $v) => $q->where('prioridad', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo_solicitud_id', $v))
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', "{$v} 00:00:00"))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', "{$v} 23:59:59"))
            ->when($filtros['q'] ?? null, fn ($q, $v) => $q->where(
                fn ($w) => $w->where('folio', 'like', "%{$v}%")->orWhere('titulo', 'like', "%{$v}%")
            ));
    }

    /** Solicitudes donde es el turno de decidir de este aprobador. */
    public function scopePendientesDeDecision(Builder $query, User $usuario): Builder
    {
        return $query
            ->where('estado', EstadoSolicitud::PendienteRevision->value)
            ->whereHas('aprobaciones', fn ($q) => $q
                ->where('aprobador_id', $usuario->id)
                ->where('estado', EstadoAprobacion::Pendiente->value)
                ->whereColumn('aprobaciones.nivel', 'solicitudes.nivel_actual')
                ->whereColumn('aprobaciones.ciclo', 'solicitudes.ciclo'));
    }
}