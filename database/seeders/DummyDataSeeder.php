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

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear un Proveedor
        $provider = Provider::create([
            'nit' => '900123456-1',
            'company_name' => 'Proveedora Tecnológica M&M SAS',
            'contact' => 'Carlos Martínez - 3112223344'
        ]);

        // 2. Obtener la Categoría que creamos antes
        $category = Category::where('code', 'COMP-001')->first();

        // 3. Crear Artículos (Uno como Activo Fijo y otro como consumible)
        $itemLaptop = Item::create([
            'category_id' => $category->id,
            'name' => 'Computador Portátil Dell Latitude 3420',
            'is_asset' => true, 
        ]);

        $itemMouse = Item::create([
            'category_id' => $category->id,
            'name' => 'Mouse Inalámbrico Logitech',
            'is_asset' => false, // Consumible
        ]);

        // 4. Registrar los Activos Fijos
        $asset1 = FixedAsset::create([
            'item_id' => $itemLaptop->id,
            'provider_id' => $provider->id,
            'inventory_code' => 'INV-2026-0001',
            'serial_number' => 'DELL-LX-9912',
            'purchase_price' => 3500000.00,
            'status' => 'nuevo',
        ]);

        $asset2 = FixedAsset::create([
            'item_id' => $itemLaptop->id,
            'provider_id' => $provider->id,
            'inventory_code' => 'INV-2026-0002',
            'serial_number' => 'DELL-LX-9913',
            'purchase_price' => 3500000.00,
            'status' => 'bueno',
        ]);

        // 5. Asignar los activos a las dependencias municipales (Oficinas)
        $hacienda = Office::where('name', 'Hacienda')->first();
        $planeacion = Office::where('name', 'Planeación')->first();

        Assignment::create([
            'fixed_asset_id' => $asset1->id,
            'office_id' => $hacienda->id,
            'custodian_name' => 'Dra. María González (Secretaria)',
            'assignment_date' => Carbon::now(),
            'is_active' => true,
        ]);

        Assignment::create([
            'fixed_asset_id' => $asset2->id,
            'office_id' => $planeacion->id,
            'custodian_name' => 'Ing. Felipe Torres (Arquitecto de Datos)',
            'assignment_date' => Carbon::now(),
            'is_active' => true,
        ]);
        
        $this->command->info('¡Datos de prueba generados exitosamente!');
    }
}
