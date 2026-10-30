<?php

namespace App\Policies;

use App\Enums\EstadoSolicitud;
use App\Enums\Rol;
use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    public function viewAny(User $usuario): bool
    {
        return true; // el listado se limita con el scopeVisiblesPara()
    }

    public function view(User $usuario, Solicitud $solicitud): bool
    {
        return match ($usuario->rol) {
            Rol::Administrador => true,
            Rol::Aprobador => $solicitud->aprobaciones()->where('aprobador_id', $usuario->id)->exists(),
            default => $this->esSolicitante($usuario, $solicitud),
        };
    }

    public function create(User $usuario): bool
    {
        return $usuario->rol === Rol::Colaborador; // solo colaboradores crean
    }

    public function update(User $usuario, Solicitud $solicitud): bool
    {
        return $this->esSolicitante($usuario, $solicitud)
            && in_array($solicitud->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Rechazada], true);
    }

    public function enviar(User $usuario, Solicitud $solicitud): bool
    {
        return $this->update($usuario, $solicitud);
    }

    /* Aprueba o rechaza */
    public function resolver(User $usuario, Solicitud $solicitud): bool
    {
        return $solicitud->estado === EstadoSolicitud::PendienteRevision
            && $solicitud->aprobacionPendientePara($usuario) !== null;
    }

    public function comentar(User $usuario, Solicitud $solicitud): bool
    {
        // El admin es de solo lectura
        return $usuario->rol !== Rol::Administrador && $this->view($usuario, $solicitud);
    }

    private function esSolicitante(User $usuario, Solicitud $solicitud): bool
    {
        return (int) $solicitud->solicitante_id === $usuario->id; // compara ids
    }
}