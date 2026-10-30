<?php
// app/Enums/EstadoAprobacion.php
namespace App\Enums;

enum EstadoAprobacion: string
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';
}