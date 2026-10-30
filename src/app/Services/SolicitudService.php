<?php
// app/Services/SolicitudService.php
namespace App\Services;

use App\Enums\AccionHistorial;
use App\Enums\EstadoAprobacion;
use App\Enums\EstadoSolicitud;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Aprobacion;
use App\Models\HistorialSolicitud;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Adjunto;

class SolicitudService
{
    // servicio principal para el flujo de solicitudes, todo pasa por aca

    /* crea la solicitud en borrador, la variable de $datos debe estar validada. */
    public function crear(User $solicitante, array $datos): Solicitud
    {
        return DB::transaction(function () use ($solicitante, $datos) {
            $solicitud = Solicitud::create([
                ...$datos,
                'solicitante_id' => $solicitante->id,
                'estado' => EstadoSolicitud::Borrador, // arranca en borrador, despues se envia
            ]);

            $this->registrar($solicitud, $solicitante, AccionHistorial::Creacion); // log de creacion

            return $solicitud;
        });
    }

    /* solo se edita en borrador o rechazada */
    public function actualizar(Solicitud $solicitud, User $usuario, array $datos): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $usuario, $datos) {
            $solicitud = $this->bloquear($solicitud);

            if (! in_array($solicitud->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Rechazada], true)) {
                // solo se toca si esta en borrador o rechazada
                throw new ReglaNegocioException('Solo se pueden editar solicitudes en borrador o rechazadas.'); // validacion de estado
            }

            $solicitud->fill($datos);
            $campos = array_keys($solicitud->getDirty());

            if ($campos === []) {
                return $solicitud;
            }

            $solicitud->save();
            $this->registrar($solicitud, $usuario, AccionHistorial::Edicion, null, ['campos' => $campos]);

            return $solicitud;
        });
    }

    /**
     * envia o reenvia a revision.
     * $aprobadores: [nivel => user_id], ej. [1 => 2, 2 => 3]
     */
    public function enviar(Solicitud $solicitud, User $usuario, array $aprobadores): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $usuario, $aprobadores) {
            $solicitud = $this->bloquear($solicitud);

            $esReenvio = $solicitud->estado === EstadoSolicitud::Rechazada;

            if (! $esReenvio && $solicitud->estado !== EstadoSolicitud::Borrador) {
                throw new ReglaNegocioException('Solo se pueden enviar solicitudes en borrador o rechazadas.');
            }

            $solicitud->loadMissing('tipo');

            if ($solicitud->tipo->requiere_monto && $solicitud->monto === null) {
                throw new ReglaNegocioException('Este tipo de solicitud requiere un monto.');
            }

            $niveles = $this->nivelesRequeridos($solicitud);
            $this->validarAprobadores($solicitud, $aprobadores, $niveles);

            $ciclo = $esReenvio ? $solicitud->ciclo + 1 : $solicitud->ciclo;

            foreach ($aprobadores as $nivel => $aprobadorId) {
                Aprobacion::create([
                    'solicitud_id' => $solicitud->id,
                    'aprobador_id' => $aprobadorId,
                    'nivel' => $nivel,
                    'ciclo' => $ciclo,
                    'estado' => EstadoAprobacion::Pendiente,
                ]);
            }

            $solicitud->update([
                'estado' => EstadoSolicitud::PendienteRevision,
                'niveles_requeridos' => $niveles,
                'nivel_actual' => 1,
                'ciclo' => $ciclo,
                'enviada_en' => now(),
                'resuelta_en' => null,
            ]);

            $this->registrar(
                $solicitud,
                $usuario,
                $esReenvio ? AccionHistorial::Reenvio : AccionHistorial::Envio,
                null,
                ['ciclo' => $ciclo, 'niveles_requeridos' => $niveles],
            );

            return $solicitud;
        });
    }

    public function aprobar(Solicitud $solicitud, User $aprobador, ?string $comentario = null): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $aprobador, $comentario) {
            $solicitud = $this->bloquear($solicitud);
            $aprobacion = $this->aprobacionPendiente($solicitud, $aprobador);

            $aprobacion->update([
                'estado' => EstadoAprobacion::Aprobada,
                'comentario' => $comentario,
                'decidida_en' => now(),
            ]);

            $this->registrar($solicitud, $aprobador, AccionHistorial::Aprobacion, $comentario, [
                'nivel' => $aprobacion->nivel,
                'ciclo' => $solicitud->ciclo,
            ]);

            if ($solicitud->nivel_actual < $solicitud->niveles_requeridos) {
                $solicitud->update(['nivel_actual' => $solicitud->nivel_actual + 1]);
            } else {
                $solicitud->update([
                    'estado' => EstadoSolicitud::Aprobada,
                    'resuelta_en' => now(),
                ]);
            }

            return $solicitud;
        });
    }

    public function rechazar(Solicitud $solicitud, User $aprobador, string $comentario): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $aprobador, $comentario) {
            if (blank($comentario)) {
                throw new ReglaNegocioException('Debes indicar el motivo del rechazo.');
            }

            $solicitud = $this->bloquear($solicitud);
            $aprobacion = $this->aprobacionPendiente($solicitud, $aprobador);

            $aprobacion->update([
                'estado' => EstadoAprobacion::Rechazada,
                'comentario' => $comentario,
                'decidida_en' => now(),
            ]);

            $solicitud->update([
                'estado' => EstadoSolicitud::Rechazada,
                'resuelta_en' => now(),
            ]);

            $this->registrar($solicitud, $aprobador, AccionHistorial::Rechazo, $comentario, [
                'nivel' => $aprobacion->nivel,
                'ciclo' => $solicitud->ciclo,
            ]);

            return $solicitud;
        });
    }

    public function comentar(Solicitud $solicitud, User $usuario, string $comentario): HistorialSolicitud
    {
        if (blank($comentario)) {
            throw new ReglaNegocioException('El comentario no puede estar vacío.');
        }

        return $this->registrar($solicitud, $usuario, AccionHistorial::Comentario, $comentario);
    }

    /* ---------- reglas internas ---------- */

    public function nivelesRequeridos(Solicitud $solicitud): int
    {
        $umbral = config('solicitudes.umbral_segunda_aprobacion');

        return $solicitud->monto !== null && (float) $solicitud->monto > $umbral ? 2 : 1;
    }

    private function validarAprobadores(Solicitud $solicitud, array $aprobadores, int $niveles): void
    {
        $nivelesRecibidos = array_map('intval', array_keys($aprobadores));
        sort($nivelesRecibidos);

        if ($nivelesRecibidos !== range(1, $niveles)) {
            throw new ReglaNegocioException("Debes indicar un aprobador para cada uno de los {$niveles} nivel(es) requeridos.");
        }

        $ids = array_map('intval', array_values($aprobadores));

        if (count(array_unique($ids)) !== count($ids)) {
            throw new ReglaNegocioException('Cada nivel de aprobación debe tener un aprobador distinto.');
        }

        if (in_array((int) $solicitud->solicitante_id, $ids, true)) {
            throw new ReglaNegocioException('No puedes asignarte a ti mismo como aprobador.');
        }

        $validos = User::whereIn('id', $ids)->where('rol', Rol::Aprobador->value)->count();

        if ($validos !== count($ids)) {
            throw new ReglaNegocioException('Alguno de los aprobadores seleccionados no es válido.');
        }
    }

    private function aprobacionPendiente(Solicitud $solicitud, User $aprobador): Aprobacion
        {
            if ($solicitud->estado !== EstadoSolicitud::PendienteRevision) {
                throw new ReglaNegocioException('La solicitud no está pendiente de revisión.');
            }

            return $solicitud->aprobacionPendientePara($aprobador)
                ?? throw new ReglaNegocioException('No tienes una aprobación pendiente para esta solicitud.');
        }

    /* relee la fila con bloqueo para evitar decisiones concurrentes, solo dentro de una transaccion */
    private function bloquear(Solicitud $solicitud): Solicitud
    {
        return Solicitud::whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
    }

    private function registrar(
        Solicitud $solicitud,
        User $usuario,
        AccionHistorial $accion,
        ?string $comentario = null,
        array $metadatos = [],
    ): HistorialSolicitud {
        return HistorialSolicitud::create([
            'solicitud_id' => $solicitud->id,
            'usuario_id' => $usuario->id,
            'accion' => $accion,
            'comentario' => $comentario,
            'metadatos' => $metadatos ?: null,
        ]);
    }

    public function adjuntar(Solicitud $solicitud, User $usuario, array $archivos): void
        {
            DB::transaction(function () use ($solicitud, $usuario, $archivos) {
                $solicitud = $this->bloquear($solicitud);

                if (! in_array($solicitud->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Rechazada], true)) {
                    throw new ReglaNegocioException('Solo se pueden adjuntar archivos en borrador o rechazadas.');
                }

                $nombres = [];

                foreach ($archivos as $archivo) {
                    $nombre = $archivo->getClientOriginalName();
                    $mime = $archivo->getMimeType();
                    $tamano = $archivo->getSize();

                    Adjunto::create([
                        'solicitud_id' => $solicitud->id,
                        'subido_por' => $usuario->id,
                        'nombre_original' => $nombre,
                        'ruta' => $archivo->store("solicitudes/{$solicitud->id}"),
                        'mime' => $mime,
                        'tamano' => $tamano,
                    ]);

                    $nombres[] = $nombre;
                }

                $this->registrar($solicitud, $usuario, AccionHistorial::Edicion, null, ['adjuntos' => $nombres]);
            });
        }
}