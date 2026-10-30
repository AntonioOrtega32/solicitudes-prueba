<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSolicitud;
use App\Enums\Prioridad;
use App\Enums\Rol;
use App\Http\Requests\EnviarSolicitudRequest;
use App\Http\Requests\GuardarSolicitudRequest;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Services\SolicitudService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Solicitud::class);

        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoSolicitud::class)],
            'prioridad' => ['nullable', Rule::enum(Prioridad::class)],
            'tipo' => ['nullable', 'integer', 'exists:tipos_solicitud,id'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'q' => ['nullable', 'string', 'max:100'],
            'por_resolver' => ['nullable', 'boolean'],
        ]);

        $usuario = $request->user();

        $solicitudes = Solicitud::visiblesPara($usuario)
            ->with(['tipo', 'solicitante'])
            ->filtrar($filtros)
            ->when($request->boolean('por_resolver'), fn ($q) => $q->pendientesDeDecision($usuario))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $conteos = Solicitud::visiblesPara($usuario)
            ->toBase()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('solicitudes.index', [
            'solicitudes' => $solicitudes,
            'tipos' => TipoSolicitud::orderBy('nombre')->get(),
            'estados' => EstadoSolicitud::cases(),
            'prioridades' => Prioridad::cases(),
            'filtros' => $filtros,
            'conteos' => $conteos,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Solicitud::class);

        $tipos = TipoSolicitud::where('activo', true)->orderBy('nombre')->get();

        return view('solicitudes.create', [
            'tipos' => $tipos,
            // El tipo elegido llega por ?tipo=ID y de él sale el formulario dinámico.
            'tipo' => $tipos->firstWhere('id', (int) $request->query('tipo')),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function store(GuardarSolicitudRequest $request, SolicitudService $servicio)
    {
        $solicitud = $servicio->crear($request->user(), $request->safe()->except('adjuntos'));

        if ($request->hasFile('adjuntos')) {
            $servicio->adjuntar($solicitud, $request->user(), $request->file('adjuntos'));
        }

        return redirect()->route('solicitudes.show', $solicitud)
            ->with('status', 'Solicitud creada como borrador. Revísala y envíala a aprobación.');
    }

    public function show(Request $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $this->authorize('view', $solicitud);

        $solicitud->load(['tipo', 'solicitante', 'adjuntos', 'aprobaciones.aprobador', 'historial.usuario']);

        $puedeEnviar = $request->user()->can('enviar', $solicitud);

        return view('solicitudes.show', [
            'solicitud' => $solicitud,
            'puedeEnviar' => $puedeEnviar,
            'nivelesRequeridos' => $puedeEnviar ? $servicio->nivelesRequeridos($solicitud) : null,
            'aprobadoresDisponibles' => $puedeEnviar
                ? User::where('rol', Rol::Aprobador->value)
                    ->where('id', '!=', $solicitud->solicitante_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
        ]);
    }

    public function edit(Solicitud $solicitud)
    {
        $this->authorize('update', $solicitud);

        return view('solicitudes.edit', [
            'solicitud' => $solicitud->load(['tipo', 'adjuntos']),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function update(GuardarSolicitudRequest $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $servicio->actualizar($solicitud, $request->user(), $request->safe()->except('adjuntos'));

        if ($request->hasFile('adjuntos')) {
            $servicio->adjuntar($solicitud, $request->user(), $request->file('adjuntos'));
        }

        return redirect()->route('solicitudes.show', $solicitud)->with('status', 'Solicitud actualizada.');
    }

    public function enviar(EnviarSolicitudRequest $request, Solicitud $solicitud, SolicitudService $servicio)
    {
        $servicio->enviar($solicitud, $request->user(), $request->validated('aprobadores'));

        return redirect()->route('solicitudes.show', $solicitud)->with('status', 'Solicitud enviada a revisión.');
    }
}