<?php
// app/Enums/EstadoSolicitud.php
namespace App\Enums;

enum EstadoSolicitud: string
{
    case Borrador = 'borrador';
    case PendienteRevision = 'pendiente_revision';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::PendienteRevision => 'Pendiente de revisión',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
        };
    }
}