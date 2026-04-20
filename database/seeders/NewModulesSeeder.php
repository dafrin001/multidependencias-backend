<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryEntry;
use App\Models\InventoryEntryItem;
use App\Models\AssetDisposal;
use App\Models\AssetTransfer;
use App\Models\AssetMaintenance;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Models\User;
use App\Models\Item;
use App\Models\FixedAsset;
use App\Models\Office;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class NewModulesSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // Get necessary data
            $items = Item::all();
            $assets = FixedAsset::all();
            $offices = Office::all();
            $providers = Provider::all();

            $consumableItems = $items->where('is_asset', false);

            if ($consumableItems->count() > 0 && $providers->count() > 0) {
                // 1. Entradas (InventoryEntry)
                $entry = InventoryEntry::create([
                    'entry_number' => 'ENT-2026-0001',
                    'supplier_id' => $providers->first()->id,
                    'invoice_number' => 'FAC-9923',
                    'entry_date' => now()->subDays(5)->toDateString(),
                    'received_by' => 'Almacenista Central',
                    'notes' => 'Entrada inicial de suministros de prueba.',
                    'status' => 'completed',
                    'total_amount' => 150000.00
                ]);

                foreach ($consumableItems->take(3) as $cItem) {
                    InventoryEntryItem::create([
                        'inventory_entry_id' => $entry->id,
                        'item_id' => $cItem->id,
                        'quantity' => 10,
                        'unit_price' => 5000.00
                    ]);
                    $cItem->increment('stock', 10);
                }
            }

            if ($assets->count() > 0) {
                // 2. Bajas (AssetDisposal)
                $assetToDispose = $assets->where('status', '!=', 'baja')->first();
                if ($assetToDispose) {
                    AssetDisposal::create([
                        'disposal_number' => 'BAJ-2026-0001',
                        'fixed_asset_id' => $assetToDispose->id,
                        'reason' => 'obsolescence',
                        'description' => 'Equipo obsoleto, dado de baja.',
                        'disposal_date' => now()->subDays(2)->toDateString(),
                        'authorized_by' => 'Director Administrativo',
                        'processed_by' => 'Almacenista Central',
                        'resolution_number' => 'RES-2026-045'
                    ]);
                    $assetToDispose->update([
                        'status' => 'baja',
                        'is_disposed' => true
                    ]);
                }

                // 3. Traslados (AssetTransfer)
                $assetToTransfer = $assets->where('status', '!=', 'baja')->where('id', '!=', $assetToDispose->id ?? null)->first();
                if ($assetToTransfer && $offices->count() >= 2) {
                    $fromOffice = $offices->first();
                    $toOffice = $offices->last();
                    
                    AssetTransfer::create([
                        'transfer_number' => 'TRA-2026-0001',
                        'fixed_asset_id' => $assetToTransfer->id,
                        'from_office_id' => $fromOffice->id,
                        'to_office_id' => $toOffice->id,
                        'transferred_by' => 'Almacenista Central',
                        'received_by' => 'Funcionario de Destino',
                        'transfer_date' => now()->subDays(1)->toDateString(),
                        'notes' => 'Traslado por reubicación de personal.'
                    ]);
                }

                // 4. Mantenimientos (AssetMaintenance)
                $assetToMaintain = $assets->where('status', '!=', 'baja')->last();
                if ($assetToMaintain) {
                    AssetMaintenance::create([
                        'fixed_asset_id' => $assetToMaintain->id,
                        'type' => 'preventive',
                        'description' => 'Mantenimiento preventivo anual.',
                        'maintenance_date' => now()->subDays(10)->toDateString(),
                        'next_maintenance_date' => now()->addMonths(6)->toDateString(),
                        'technician' => 'Soporte TI Externo',
                        'cost' => '120000.00',
                        'status' => 'completed'
                    ]);
                }
            }

            if ($consumableItems->count() > 0 && $offices->count() > 0) {
                // 5. Solicitudes de suministro (SupplyRequest)
                $req1 = SupplyRequest::create([
                    'request_number' => 'SOL-2026-0001',
                    'office_id' => $offices->first()->id,
                    'requested_by' => 'Secretario General',
                    'request_date' => now()->subDays(3)->toDateString(),
                    'needed_by' => now()->addDays(2)->toDateString(),
                    'notes' => 'Suministros para evento institucional.',
                    'status' => 'pending'
                ]);

                foreach ($consumableItems->take(2) as $cItem) {
                    SupplyRequestItem::create([
                        'supply_request_id' => $req1->id,
                        'item_id' => $cItem->id,
                        'requested_quantity' => 5
                    ]);
                }

                $req2 = SupplyRequest::create([
                    'request_number' => 'SOL-2026-0002',
                    'office_id' => $offices->last()->id,
                    'requested_by' => 'Director de Planeación',
                    'request_date' => now()->subDays(4)->toDateString(),
                    'status' => 'dispatched',
                    'dispatch_date' => now()->subDays(2)->toDateString(),
                    'dispatched_by' => 'Almacenista Central'
                ]);

                foreach ($consumableItems->skip(1)->take(2) as $cItem) {
                    SupplyRequestItem::create([
                        'supply_request_id' => $req2->id,
                        'item_id' => $cItem->id,
                        'requested_quantity' => 3,
                        'approved_quantity' => 3,
                        'dispatched_quantity' => 3
                    ]);
                    // Restar stock
                    if ($cItem->stock >= 3) {
                        $cItem->decrement('stock', 3);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
