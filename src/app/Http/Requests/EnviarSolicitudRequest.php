<?php

namespace App\Http\Requests;

use App\Enums\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnviarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enviar', $this->route('solicitud'));
    }

    public function rules(): array
    {
        return [
            'aprobadores' => ['required', 'array', 'min:1', 'max:2'],
            'aprobadores.*' => [
                'integer',
                Rule::exists('users', 'id')->where('rol', Rol::Aprobador->value),
            ],
        ];
    }
}