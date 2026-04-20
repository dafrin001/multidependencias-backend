<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\FixedAsset;
use App\Models\AssetTransfer;
use App\Models\AssetDisposal;
use App\Models\InventoryEntry;
use App\Models\SupplyRequest;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * General Inventory Stock Report
     */
    public function inventoryStock()
    {
        $items = Item::with('category')->get();
        return response()->json(['data' => $items, 'title' => 'Inventario General de Stock']);
    }

    /**
     * Assets by Office/Location
     */
    public function assetsByOffice()
    {
        $assets = FixedAsset::with(['item.category', 'activeAssignment.office'])
            ->where('status', '!=', 'baja')
            ->get()
            ->groupBy(fn($a) => $a->activeAssignment->office->name ?? 'Sin Asignar');

        return response()->json(['data' => $assets, 'title' => 'Activos Fijos por Dependencia']);
    }

    /**
     * Recent Movements (Entries/Exits)
     */
    public function movements(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $entries = InventoryEntry::with('supplier')
            ->when($start, fn($q) => $q->whereDate('entry_date', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('entry_date', '<=', $end))
            ->get()->map(fn($e) => [
                'date' => $e->entry_date,
                'type' => 'ENTRADA',
                'ref'  => $e->entry_number,
                'desc' => $e->supplier->company_name ?? 'Sin proveedor',
                'items' => $e->items_count
            ]);

        // Add other movement types...
        return response()->json(['data' => $entries, 'title' => 'Reporte de Movimientos']);
    }
}
