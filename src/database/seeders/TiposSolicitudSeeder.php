<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoSolicitud;

class TiposSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $tipos = [
        [
            'codigo' => 'COMPRA',
            'nombre' => 'Compra de equipo o servicio',
            'requiere_monto' => true,
            'esquema_campos' => [
                ['nombre' => 'proveedor', 'etiqueta' => 'Proveedor', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'justificacion', 'etiqueta' => 'Justificación', 'tipo' => 'textarea', 'requerido' => true],
            ],
        ],
        [
            'codigo' => 'VIATICOS',
            'nombre' => 'Viáticos',
            'requiere_monto' => true,
            'esquema_campos' => [
                ['nombre' => 'destino', 'etiqueta' => 'Destino', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'fecha_inicio', 'etiqueta' => 'Fecha de inicio', 'tipo' => 'date', 'requerido' => true],
                ['nombre' => 'fecha_fin', 'etiqueta' => 'Fecha de fin', 'tipo' => 'date', 'requerido' => true],
            ],
        ],
        [
            'codigo' => 'VACACIONES',
            'nombre' => 'Vacaciones',
            'requiere_monto' => false,
            'esquema_campos' => [
                ['nombre' => 'fecha_inicio', 'etiqueta' => 'Fecha de inicio', 'tipo' => 'date', 'requerido' => true],
                ['nombre' => 'fecha_fin', 'etiqueta' => 'Fecha de fin', 'tipo' => 'date', 'requerido' => true],
            ],
        ],
        [
            'codigo' => 'ACCESO',
            'nombre' => 'Acceso a sistemas',
            'requiere_monto' => false,
            'esquema_campos' => [
                ['nombre' => 'sistema', 'etiqueta' => 'Sistema', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'nivel_acceso', 'etiqueta' => 'Nivel de acceso', 'tipo' => 'select',
                 'opciones' => ['Lectura', 'Escritura', 'Administrador'], 'requerido' => true],
            ],
        ],
    ];

    foreach ($tipos as $tipo) {
        TipoSolicitud::updateOrCreate(['codigo' => $tipo['codigo']], $tipo);
    }
    }
}
