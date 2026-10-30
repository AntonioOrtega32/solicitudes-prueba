<?php

namespace App\Http\Requests;

use App\Enums\Prioridad;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');

        return $solicitud
            ? $this->user()->can('update', $solicitud)
            : $this->user()->can('create', Solicitud::class);
    }

    public function rules(): array
    {
        $tipo = $this->tipo();
        $editando = $this->route('solicitud') !== null;

        $reglas = [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'prioridad' => ['required', Rule::enum(Prioridad::class)],
            'monto' => $tipo?->requiere_monto
                ? ['required', 'numeric', 'min:0.01', 'max:999999999999.99']
                : ['prohibited'],
            'datos' => ['nullable', 'array'],
            'adjuntos' => ['nullable', 'array', 'max:5'],
            'adjuntos.*' => ['file', 'max:5120', 'mimes:pdf,png,jpg,jpeg,docx,xlsx'],
        ];

        // El tipo se elige al crear y ya no cambia (el esquema de campos depende de él).
        if (! $editando) {
            $reglas['tipo_solicitud_id'] = [
                'required',
                Rule::exists('tipos_solicitud', 'id')->where('activo', true),
            ];
        }

        foreach ($tipo?->esquema_campos ?? [] as $campo) {
            $reglas["datos.{$campo['nombre']}"] = $this->reglasDeCampo($campo);
        }

        return $reglas;
    }

    public function attributes(): array
    {
        $nombres = [];

        foreach ($this->tipo()?->esquema_campos ?? [] as $campo) {
            $nombres["datos.{$campo['nombre']}"] = mb_strtolower($campo['etiqueta']);
        }

        return $nombres;
    }

    private function tipo(): ?TipoSolicitud
    {
        $id = $this->route('solicitud')?->tipo_solicitud_id ?? $this->input('tipo_solicitud_id');

        return $id ? TipoSolicitud::find($id) : null;
    }

    private function reglasDeCampo(array $campo): array
    {
        $reglas = match ($campo['tipo']) {
            'text' => ['string', 'max:255'],
            'textarea' => ['string', 'max:2000'],
            'number' => ['numeric'],
            'date' => ['date'],
            'select' => [Rule::in($campo['opciones'] ?? [])],
            default => ['string'],
        };

        return [($campo['requerido'] ?? false) ? 'required' : 'nullable', ...$reglas];
    }
}