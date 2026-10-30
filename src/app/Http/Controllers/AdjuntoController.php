<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    public function descargar(Adjunto $adjunto)
    {
        $this->authorize('view', $adjunto->solicitud);

        return Storage::download($adjunto->ruta, $adjunto->nombre_original);
    }
}