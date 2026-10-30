<?php
// app/Enums/Rol.php
namespace App\Enums;

enum Rol: string
{
    case Colaborador = 'colaborador';
    case Aprobador = 'aprobador';
    case Administrador = 'administrador';

    public function etiqueta(): string
        {
            return ucfirst($this->value);
        }
}

