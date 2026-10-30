<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitud extends Model
{
    protected $table = 'tipos_solicitud';

    protected $fillable = [
        'nombre', 'codigo', 'descripcion',
        'requiere_monto', 'esquema_campos', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_monto' => 'boolean',
            'esquema_campos' => 'array',
            'activo' => 'boolean',
        ];
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tipo_solicitud_id');
    }
}
