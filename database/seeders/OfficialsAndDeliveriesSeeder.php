<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Official;
use App\Models\Office;
use App\Models\Item;
use App\Models\FixedAsset;
use App\Models\DeliveryRecord;

class OfficialsAndDeliveriesSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. ACTUALIZAR STOCK DE CONSUMIBLES ────────────────────────
        $stockData = [
            'Tóner HP CF258A'            => ['stock' => 3,  'min_stock' => 5],   // Alerta: bajo
            'Mouse Inalámbrico Logitech' => ['stock' => 12, 'min_stock' => 10],
            'Resma de Papel Carta'        => ['stock' => 2,  'min_stock' => 10],  // Alerta: crítico
        ];
        foreach ($stockData as $name => $data) {
            Item::where('name', $name)->update($data);
        }

        // ─── 2. FUNCIONARIOS (OFICIALES) ───────────────────────────────
        $hacienda   = Office::where('name', 'Secretaría de Hacienda')->first();
        $planeacion = Office::where('name', 'Secretaría de Planeación')->first();
        $gobierno   = Office::where('name', 'Secretaría de Gobierno')->first();
        $salud      = Office::where('name', 'Secretaría de Salud')->first();
        $educacion  = Office::where('name', 'Secretaría de Educación')->first();
        $obras      = Office::where('name', 'Secretaría de Obras Públicas')->first();
        $despacho   = Office::where('name', 'Despacho del Alcalde')->first();

        $officials = [
            ['full_name' => 'María Fernanda González Ríos',   'document_number' => '52301142', 'document_type' => 'CC', 'position' => 'Secretaria de Hacienda',        'office' => $hacienda,   'email' => 'mgonzalez@alcaldia.gov',   'phone' => '3112223344'],
            ['full_name' => 'Felipe Andrés Torres Muñoz',     'document_number' => '79834521', 'document_type' => 'CC', 'position' => 'Arquitecto de Datos',            'office' => $planeacion, 'email' => 'ftorres@alcaldia.gov',     'phone' => '3124456789'],
            ['full_name' => 'Luis Eduardo Quintero Parra',    'document_number' => '12456789', 'document_type' => 'CC', 'position' => 'Auxiliar Administrativo',        'office' => $gobierno,   'email' => 'lquintero@alcaldia.gov',   'phone' => '3133214567'],
            ['full_name' => 'Ana María Moreno Castro',        'document_number' => '39112034', 'document_type' => 'CC', 'position' => 'Coordinadora de Salud Pública',  'office' => $salud,      'email' => 'amoreno@alcaldia.gov',     'phone' => '3155679012'],
            ['full_name' => 'Jorge William Castillo Díaz',    'document_number' => '88230145', 'document_type' => 'CC', 'position' => 'Inspector de Educación',         'office' => $educacion,  'email' => 'jcastillo@alcaldia.gov',   'phone' => '3166678901'],
            ['full_name' => 'Claudia Marcela Rinaldi Pinto',  'document_number' => '41229980', 'document_type' => 'CC', 'position' => 'Jefe de Obras Públicas',         'office' => $obras,      'email' => 'crinaldi@alcaldia.gov',    'phone' => '3144512345'],
            ['full_name' => 'Patricia Lucía Herrera Soto',    'document_number' => '60231008', 'document_type' => 'CC', 'position' => 'Secretaria del Despacho',        'office' => $despacho,   'email' => 'pherrera@alcaldia.gov',    'phone' => '3108890011'],
            ['full_name' => 'Ricardo Emilio Salcedo Vargas',  'document_number' => '71345623', 'document_type' => 'CC', 'position' => 'Contador Municipal',             'office' => $hacienda,   'email' => 'rsalcedo@alcaldia.gov',    'phone' => '3118890022'],
            ['full_name' => 'Sandra Patricia Ospina López',   'document_number' => '30445012', 'document_type' => 'CC', 'position' => 'Bióloga Ambiental',              'office' => null,        'email' => 'sospina@alcaldia.gov',     'phone' => '3127780033'],
            ['full_name' => 'Mariana Sofía López Agudelo',    'document_number' => '53230091', 'document_type' => 'CC', 'position' => 'Coordinadora TIC',               'office' => $educacion,  'email' => 'mlopez@alcaldia.gov',      'phone' => '3136670044'],
        ];

        $officialModels = [];
        foreach ($officials as $o) {
            if (!$o['office']) continue; // Saltar si la office no existe
            $officialModels[] = Official::firstOrCreate(
                ['document_number' => $o['document_number']],
                [
                    'full_name'     => $o['full_name'],
                    'document_type' => $o['document_type'],
                    'position'      => $o['position'],
                    'office_id'     => $o['office']->id,
                    'email'         => $o['email'],
                    'phone'         => $o['phone'],
                    'is_active'     => true,
                ]
            );
        }

        // ─── 3. ACTAS DE ENTREGA DE PRUEBA ─────────────────────────────
        if (count($officialModels) >= 3) {
            $assets = FixedAsset::take(5)->get();
            $consumable = Item::where('is_asset', false)->first();

            $deliveries = [
                [
                    'type'           => 'asset',
                    'fixed_asset_id' => $assets->get(0)?->id,
                    'item_id'        => null,
                    'quantity'       => 1,
                    'official_id'    => $officialModels[0]->id,
                    'delivered_by'   => 'Ricardo Emilio Salcedo Vargas',
                    'delivery_date'  => '2024-01-15',
                    'notes'          => 'Equipo asignado para uso en Secretaría de Hacienda.',
                ],
                [
                    'type'           => 'asset',
                    'fixed_asset_id' => $assets->get(1)?->id,
                    'item_id'        => null,
                    'quantity'       => 1,
                    'official_id'    => $officialModels[1]->id,
                    'delivered_by'   => 'Patricia Lucía Herrera Soto',
                    'delivery_date'  => '2024-01-20',
                    'notes'          => 'Equipo asignado Planeación - Proyecto SIG.',
                ],
                [
                    'type'           => 'consumable',
                    'fixed_asset_id' => null,
                    'item_id'        => $consumable?->id,
                    'quantity'       => 5,
                    'official_id'    => $officialModels[2]->id,
                    'delivered_by'   => 'María Fernanda González Ríos',
                    'delivery_date'  => '2024-03-01',
                    'notes'          => 'Entrega de suministros mes de marzo.',
                ],
            ];

            $actaNum = DeliveryRecord::withTrashed()->max('id') ?? 0;
            foreach ($deliveries as $d) {
                if (!$d['fixed_asset_id'] && !$d['item_id']) continue;
                $actaNum++;
                DeliveryRecord::firstOrCreate(
                    ['acta_number' => 'ACTA-' . date('Y') . '-' . str_pad($actaNum, 4, '0', STR_PAD_LEFT)],
                    $d + ['acta_number' => 'ACTA-' . date('Y') . '-' . str_pad($actaNum, 4, '0', STR_PAD_LEFT)]
                );
            }
        }

        $this->command->info('✅ Funcionarios y actas de entrega creados');
        $this->command->info('   → ' . count($officialModels) . ' funcionarios registrados');
        $this->command->line('   → Stock bajo configurado en consumibles (Tóner y Resmas de Papel)');
    }
}
