<?php
// app/Enums/Prioridad.php
namespace App\Enums;

enum Prioridad: string
{
    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';
    case Urgente = 'urgente';

    public function etiqueta(): string
    {
        return ucfirst($this->value);
    }
}