<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Almacén',
                'slug' => 'almacen',
                'description' => 'Gestión de inventarios, suministros y activos fijos.',
                'icon' => '📦'
            ],
            [
                'name' => 'Hacienda',
                'slug' => 'hacienda',
                'description' => 'Gestión financiera, impuestos y presupuestos.',
                'icon' => '💰'
            ],
            [
                'name' => 'Secretaría General',
                'slug' => 'secretaria-general',
                'description' => 'Administración central y trámites institucionales.',
                'icon' => '🏛️'
            ],
            [
                'name' => 'Talento Humano',
                'slug' => 'talento-humano',
                'description' => 'Gestión de empleados, nómina y bienestar.',
                'icon' => '👥'
            ],
            [
                'name' => 'Obras Públicas',
                'slug' => 'obras-publicas',
                'description' => 'Gestión de infraestructura y proyectos municipales.',
                'icon' => '🏗️'
            ]
        ];

        foreach ($areas as $area) {
            \App\Models\Area::create($area);
        }
    }
}
