<?php
// app/Enums/AccionHistorial.php
namespace App\Enums;

enum AccionHistorial: string
{
    case Creacion = 'creacion';
    case Edicion = 'edicion';
    case Envio = 'envio';
    case Reenvio = 'reenvio';
    case Aprobacion = 'aprobacion';
    case Rechazo = 'rechazo';
    case Comentario = 'comentario';
}