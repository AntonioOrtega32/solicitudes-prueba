<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\SolicitudService;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $this->authorize('comentar', $solicitud);

        $datos = $request->validate(['comentario' => ['required', 'string', 'max:2000']]);

        $servicio->comentar($solicitud, $request->user(), $datos['comentario']);

        return back()->with('status', 'Comentario agregado.');
    }
}