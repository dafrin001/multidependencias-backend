<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RufeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_ES');
        
        $events = ['Inundación', 'Vendaval', 'Incendio Forestal', 'Deslizamiento', 'Sismo', 'Sequía', 'Granizada'];
        $departments = [
            'MAGDALENA' => ['SANTA MARTA', 'CIÉNAGA', 'FUNDACIÓN', 'EL BANCO', 'PLATO'],
            'LA GUAJIRA' => ['RIOHACHA', 'MAICAO', 'MANAURE', 'URIBIA', 'URUMITA'],
            'ANTIOQUIA' => ['MEDELLÍN', 'BELLO', 'ITAGÜÍ', 'ENVIGADO', 'APARTADÓ'],
            'CHOCÓ' => ['QUIBDÓ', 'ISTMINA', 'CONDOTO', 'NUEVO BELÉN DE BAJIRÁ', 'RÍOSUCIO'],
            'VALLE DEL CAUCA' => ['CALI', 'BUENAVENTURA', 'PALMIRA', 'TULUÁ', 'CARTAGO'],
            'CUNDINAMARCA' => ['BOGOTÁ', 'SOACHA', 'FACATATIVÁ', 'CHÍA', 'ZIPAQUIRÁ']
        ];
        
        $tenience = ['PROPIETARIO', 'ARRENDATARIO', 'POSEEDOR', 'OCUPANTE'];
        $states = ['DESTRUIDO', 'NO HABITABLE', 'HABITABLE', 'AVERIADO'];
        $assetTypes = ['VIVIENDA', 'FINCA', 'LOCAL COMERCIAL', 'BODEGA'];
        
        $ethnicities = ['Indígena', 'Rom', 'Raizal', 'Palenquero', 'No aplica'];
        $relationships = ['Jefe de Hogar', 'Pareja', 'Hijo(a)', 'Abuelo(a)', 'Nieto(a)', 'Hermano(a)', 'Sobrino(a)', 'Otro'];
        $docTypes = ['CC', 'TI', 'RC', 'CE', 'PPT'];

        $deptNames = array_keys($departments);

        for ($i = 0; $i < 30; $i++) {
            $dept = $deptNames[array_rand($deptNames)];
            $municipio = $departments[$dept][array_rand($departments[$dept])];
            $fechaEvento = date('Y-m-d', strtotime("-" . rand(1, 150) . " days"));
            
            $record = \App\Models\RufeRecord::create([
                'departamento' => $dept,
                'municipio' => $municipio,
                'evento' => $events[array_rand($events)],
                'fecha_evento' => $fechaEvento,
                'fecha_rufe' => date('Y-m-d', strtotime($fechaEvento . " + " . rand(1, 5) . " days")),
                'ubicacion_tipo' => rand(0, 1) ? 'URBANO' : 'RURAL',
                'corregimiento' => rand(0, 1) ? 'Corregimiento ' . $faker->city : null,
                'vereda_sector_barrio' => $faker->streetName,
                'direccion' => $faker->address,
                'forma_tenencia' => $tenience[array_rand($tenience)],
                'estado_bien' => $states[array_rand($states)],
                'tipo_bien' => $assetTypes[array_rand($assetTypes)],
                'alojamiento_actual_tipo' => rand(0, 1) ? 'HABITUAL' : 'EVACUADO',
                'observaciones' => $faker->paragraph,
                'vo_bo' => $faker->name
            ]);

            // Demographics
            $numPeople = rand(1, 5);
            for ($j = 0; $j < $numPeople; $j++) {
                $record->demographics()->create([
                    'nombres' => $faker->firstName,
                    'apellidos' => $faker->lastName,
                    'tipo_documento' => $docTypes[array_rand($docTypes)],
                    'numero_documento' => rand(10000000, 1100000000),
                    'parentesco' => ($j == 0) ? 'Jefe de Hogar' : $relationships[array_rand($relationships)],
                    'genero' => rand(0, 1) ? (rand(0, 1) ? 'FEMENINO' : 'MASCULINO') : 'TRANSGENERO',
                    'fecha_nacimiento' => $faker->date('Y-m-d', '2015-01-01'),
                    'pertenencia_etnica' => $ethnicities[array_rand($ethnicities)],
                    'telefono' => '300' . rand(1000000, 9999999)
                ]);
            }

            // Agro
            if (rand(0, 1)) {
                $record->agros()->create([
                    'tipo_cultivo' => ['MAIZ', 'YUCA', 'PASTO', 'AHUYAMA'][rand(0, 3)],
                    'unidad_medida' => 'HECTAREAS',
                    'area_cantidad' => rand(1, 10),
                    'sector_pecuario_especie' => rand(0, 1) ? 'CHIVOS' : 'VACAS',
                    'cantidad_unidades' => rand(5, 50)
                ]);
            }
        }
    }
}
