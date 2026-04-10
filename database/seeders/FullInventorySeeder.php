<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;
use App\Models\Category;
use App\Models\Item;
use App\Models\FixedAsset;
use App\Models\Office;
use App\Models\Assignment;
use Carbon\Carbon;

class FullInventorySeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. SECRETARÍAS ────────────────────────────────────────────
        $offices = [
            'Secretaría de Hacienda',
            'Secretaría de Planeación',
            'Secretaría de Gobierno',
            'Secretaría de Salud',
            'Secretaría de Educación',
            'Secretaría de Obras Públicas',
            'Despacho del Alcalde',
            'Secretaría de Ambiente',
        ];
        $officeModels = [];
        foreach ($offices as $name) {
            $officeModels[] = Office::firstOrCreate(['name' => $name]);
        }

        // ─── 2. PROVEEDORES ────────────────────────────────────────────
        $providers = [
            ['nit' => '900.111.222-3', 'company_name' => 'Tecnología & Sistemas SAS',   'contact' => 'Andrés Reyes - 3100001111'],
            ['nit' => '800.333.444-5', 'company_name' => 'Muebles Oficina Colombia',    'contact' => 'Laura Gómez - 3122223333'],
            ['nit' => '900.555.666-7', 'company_name' => 'Dell Technologies Colombia',  'contact' => 'Camila Torres - 3011112222'],
            ['nit' => '700.777.888-9', 'company_name' => 'HP Inc Colombia',             'contact' => 'Jhon Pérez - 3044445555'],
            ['nit' => '800.999.000-1', 'company_name' => 'Distribuidora El Progreso',   'contact' => 'Natalia Ruiz - 3166667777'],
        ];
        $providerModels = [];
        foreach ($providers as $p) {
            $providerModels[] = Provider::firstOrCreate(['nit' => $p['nit']], $p);
        }

        // ─── 3. CATEGORÍAS ─────────────────────────────────────────────
        $categories = [
            ['code' => 'COMP-001', 'name' => 'Equipos de Cómputo'],
            ['code' => 'MOBI-002', 'name' => 'Mobiliario de Oficina'],
            ['code' => 'ELEC-003', 'name' => 'Equipos Eléctricos'],
            ['code' => 'VEHC-004', 'name' => 'Vehículos y Transporte'],
            ['code' => 'CONS-005', 'name' => 'Consumibles y Papelería'],
        ];
        $catModels = [];
        foreach ($categories as $c) {
            $catModels[$c['code']] = Category::firstOrCreate(['code' => $c['code']], $c);
        }

        // ─── 4. ARTÍCULOS (ITEMS) ──────────────────────────────────────
        $items = [
            // Cómputo - Activos
            ['category_code' => 'COMP-001', 'name' => 'Computador Portátil Dell Latitude 3420', 'is_asset' => true],
            ['category_code' => 'COMP-001', 'name' => 'Computador de Escritorio HP ProDesk 400', 'is_asset' => true],
            ['category_code' => 'COMP-001', 'name' => 'Monitor LG 24" Full HD', 'is_asset' => true],
            ['category_code' => 'COMP-001', 'name' => 'Impresora HP LaserJet Pro M404n', 'is_asset' => true],
            ['category_code' => 'COMP-001', 'name' => 'Servidor HP ProLiant ML110', 'is_asset' => true],
            // Cómputo - Consumibles
            ['category_code' => 'COMP-001', 'name' => 'Tóner HP CF258A', 'is_asset' => false],
            ['category_code' => 'COMP-001', 'name' => 'Mouse Inalámbrico Logitech', 'is_asset' => false],
            // Mobiliario - Activos
            ['category_code' => 'MOBI-002', 'name' => 'Escritorio Ejecutivo en Madera', 'is_asset' => true],
            ['category_code' => 'MOBI-002', 'name' => 'Silla Ergonómica Gerencial', 'is_asset' => true],
            ['category_code' => 'MOBI-002', 'name' => 'Archivador Metálico 4 Gavetas', 'is_asset' => true],
            // Eléctricos
            ['category_code' => 'ELEC-003', 'name' => 'UPS 1000VA CyberPower', 'is_asset' => true],
            ['category_code' => 'ELEC-003', 'name' => 'Televisor Samsung 55" Smart', 'is_asset' => true],
            ['category_code' => 'ELEC-003', 'name' => 'Proyector Epson PowerLite', 'is_asset' => true],
            // Vehículos
            ['category_code' => 'VEHC-004', 'name' => 'Motocicleta Honda CB190', 'is_asset' => true],
            // Consumibles
            ['category_code' => 'CONS-005', 'name' => 'Resma de Papel Carta', 'is_asset' => false],
        ];
        $itemModels = [];
        foreach ($items as $i) {
            $itemModels[] = Item::firstOrCreate(
                ['name' => $i['name']],
                ['category_id' => $catModels[$i['category_code']]->id, 'is_asset' => $i['is_asset']]
            );
        }

        // ─── 5. ACTIVOS FIJOS ──────────────────────────────────────────
        $assets = [
            // Laptop
            ['item' => 0, 'provider' => 2, 'code' => 'INV-2024-0001', 'serial' => 'DELL-LA-00091', 'price' => 3800000, 'status' => 'nuevo'],
            ['item' => 0, 'provider' => 2, 'code' => 'INV-2024-0002', 'serial' => 'DELL-LA-00092', 'price' => 3800000, 'status' => 'bueno'],
            ['item' => 0, 'provider' => 2, 'code' => 'INV-2023-0010', 'serial' => 'DELL-LA-00071', 'price' => 3500000, 'status' => 'regular'],
            // Desktop HP
            ['item' => 1, 'provider' => 3, 'code' => 'INV-2024-0003', 'serial' => 'HP-PD-44001',   'price' => 2900000, 'status' => 'nuevo'],
            ['item' => 1, 'provider' => 3, 'code' => 'INV-2023-0011', 'serial' => 'HP-PD-44002',   'price' => 2800000, 'status' => 'bueno'],
            // Monitor
            ['item' => 2, 'provider' => 3, 'code' => 'INV-2024-0004', 'serial' => 'LG-MN-2401',    'price' => 680000,  'status' => 'nuevo'],
            ['item' => 2, 'provider' => 3, 'code' => 'INV-2024-0005', 'serial' => 'LG-MN-2402',    'price' => 680000,  'status' => 'bueno'],
            // Impresora
            ['item' => 3, 'provider' => 0, 'code' => 'INV-2023-0012', 'serial' => 'HP-LJ-99112',   'price' => 1200000, 'status' => 'bueno'],
            ['item' => 3, 'provider' => 0, 'code' => 'INV-2022-0007', 'serial' => 'HP-LJ-88201',   'price' => 1150000, 'status' => 'malo'],
            // Servidor
            ['item' => 4, 'provider' => 0, 'code' => 'INV-2023-0013', 'serial' => 'HP-SRV-10011',  'price' => 9800000, 'status' => 'bueno'],
            // Escritorio
            ['item' => 7, 'provider' => 1, 'code' => 'INV-2022-0015', 'serial' => null,             'price' => 850000,  'status' => 'regular'],
            ['item' => 7, 'provider' => 1, 'code' => 'INV-2022-0016', 'serial' => null,             'price' => 850000,  'status' => 'bueno'],
            // Silla
            ['item' => 8, 'provider' => 1, 'code' => 'INV-2022-0017', 'serial' => null,             'price' => 420000,  'status' => 'bueno'],
            ['item' => 8, 'provider' => 1, 'code' => 'INV-2021-0009', 'serial' => null,             'price' => 380000,  'status' => 'malo'],
            // Archivador
            ['item' => 9, 'provider' => 1, 'code' => 'INV-2023-0020', 'serial' => null,             'price' => 560000,  'status' => 'bueno'],
            // UPS
            ['item' => 10, 'provider' => 4, 'code' => 'INV-2024-0006', 'serial' => 'UPS-CP-30011',  'price' => 380000,  'status' => 'nuevo'],
            // TV
            ['item' => 11, 'provider' => 4, 'code' => 'INV-2023-0021', 'serial' => 'SAM-TV-55001',  'price' => 2100000, 'status' => 'bueno'],
            // Proyector
            ['item' => 12, 'provider' => 4, 'code' => 'INV-2022-0022', 'serial' => 'EPS-PJ-00112',  'price' => 1850000, 'status' => 'regular'],
            // Moto
            ['item' => 13, 'provider' => 4, 'code' => 'INV-2023-0030', 'serial' => 'HON-CB-23001',  'price' => 8900000, 'status' => 'bueno'],
            // Dado de baja
            ['item' => 0, 'provider' => 2, 'code' => 'INV-2019-0001', 'serial' => 'DELL-OLD-0001',  'price' => 2200000, 'status' => 'baja'],
        ];

        $assetModels = [];
        foreach ($assets as $a) {
            $assetModels[] = FixedAsset::firstOrCreate(
                ['inventory_code' => $a['code']],
                [
                    'item_id'        => $itemModels[$a['item']]->id,
                    'provider_id'    => $providerModels[$a['provider']]->id,
                    'serial_number'  => $a['serial'],
                    'purchase_price' => $a['price'],
                    'status'         => $a['status'],
                ]
            );
        }

        // ─── 6. ASIGNACIONES ───────────────────────────────────────────
        $assignments = [
            // [asset_index, office_index, custodian, date]
            [0,  0, 'Dra. María González - Secretaria de Hacienda',       '2024-01-15'],
            [1,  1, 'Ing. Felipe Torres - Arquitecto de Datos',            '2024-01-20'],
            [2,  2, 'Sr. Luis Quintero - Aux. Gobierno',                   '2023-09-10'],
            [3,  3, 'Lic. Ana Moreno - Coordinadora Salud',                '2024-02-01'],
            [4,  4, 'Prof. Jorge Castillo - Rector Encargado',             '2024-03-05'],
            [5,  0, 'Contador Rodrigo Sánchez - Hacienda',                 '2024-01-15'],
            [6,  5, 'Arq. Claudia Rinaldi - Jefe Obras',                   '2023-11-20'],
            [7,  6, 'Sec. Patricia Herrera - Despacho Alcalde',            '2022-06-01'],
            [8,  0, 'Aux. Diego Vargas - Hacienda',                        '2022-06-01'],
            [9,  1, 'Estadístico Carlos Mora - Planeación',                '2023-02-14'],
            [10, 7, 'Biol. Sandra Ospina - Ambiente',                      '2022-05-18'],
            [11, 6, 'Sec. Patricia Herrera - Despacho Alcalde',            '2023-08-01'],
            [12, 4, 'Coordinadora TIC Mariana López - Educación',          '2022-09-12'],
            [13, 2, 'Ins. Fiscal Juan Pablo Ríos - Gobierno',             '2023-07-01'],
            [14, 5, 'Inspector Obra Roberto Cano - Obras Públicas',        '2023-10-25'],
            [15, 0, 'Sist. William Pardo - Hacienda',                      '2024-01-15'],
            [16, 6, 'Coord. Comunicaciones Luisa Martínez - Despacho',    '2023-05-22'],
            [17, 1, 'Ing. Daniel Castro - Sistemas de Información',        '2022-11-30'],
            [18, 3, 'Enf. Adriana Pinto - Coordinación Salud Pública',    '2023-04-10'],
        ];

        foreach ($assignments as [$ai, $oi, $custodian, $date]) {
            if (isset($assetModels[$ai]) && isset($officeModels[$oi])) {
                Assignment::firstOrCreate(
                    ['fixed_asset_id' => $assetModels[$ai]->id, 'is_active' => true],
                    [
                        'office_id'       => $officeModels[$oi]->id,
                        'custodian_name'  => $custodian,
                        'assignment_date' => $date,
                    ]
                );
            }
        }

        $this->command->info('✅ Datos completos de inventario municipal generados exitosamente.');
        $this->command->info('   → ' . count($officeModels) . ' secretarías');
        $this->command->info('   → ' . count($providerModels) . ' proveedores');
        $this->command->info('   → ' . count($catModels) . ' categorías');
        $this->command->info('   → ' . count($itemModels) . ' artículos');
        $this->command->info('   → ' . count($assetModels) . ' activos fijos');
        $this->command->info('   → ' . count($assignments) . ' asignaciones');
    }
}
