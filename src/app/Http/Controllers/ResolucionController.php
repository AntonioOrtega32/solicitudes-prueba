<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\SolicitudService;
use Illuminate\Http\Request;

class ResolucionController extends Controller
{
    public function aprobar(Request $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $this->authorize('resolver', $solicitud);

        $datos = $request->validate(['comentario' => ['nullable', 'string', 'max:2000']]);

        $servicio->aprobar($solicitud, $request->user(), $datos['comentario'] ?? null);

        return redirect()->route('solicitudes.show', $solicitud)->with('status', 'Aprobación registrada.');
    }

    public function rechazar(Request $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $this->authorize('resolver', $solicitud);

        $datos = $request->validate(['comentario' => ['required', 'string', 'max:2000']]);

        $servicio->rechazar($solicitud, $request->user(), $datos['comentario']);

        return redirect()->route('solicitudes.show', $solicitud)->with('status', 'Solicitud rechazada.');
    }
}