<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Rol;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $usuarios = [
        ['name' => 'Admin Sistema',   'email' => 'admin@example.com',       'rol' => Rol::Administrador],
        ['name' => 'Ana Aprobadora',  'email' => 'aprobador1@example.com',  'rol' => Rol::Aprobador],
        ['name' => 'Luis Aprobador',  'email' => 'aprobador2@example.com',  'rol' => Rol::Aprobador],
        ['name' => 'Carla Colaboradora', 'email' => 'colaborador1@example.com', 'rol' => Rol::Colaborador],
        ['name' => 'Mario Colaborador',  'email' => 'colaborador2@example.com', 'rol' => Rol::Colaborador],
    ];

    foreach ($usuarios as $datos) {
        User::updateOrCreate(
            ['email' => $datos['email']],
            ['name' => $datos['name'], 'rol' => $datos['rol'], 'password' => 'password'],
        );
    }
    }
}
