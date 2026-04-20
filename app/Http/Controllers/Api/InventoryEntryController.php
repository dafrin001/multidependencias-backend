<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryEntry;
use App\Models\InventoryEntryItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryEntryController extends Controller
{
    public function index()
    {
        $entries = InventoryEntry::with(['supplier', 'items.item.category'])
            ->orderBy('entry_date', 'desc')
            ->get();
        return response()->json(['data' => $entries]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'      => 'nullable|string|max:100',
            'supplier_id'         => 'nullable|exists:providers,id',
            'entry_date'          => 'required|date',
            'received_by'         => 'required|string|max:255',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.item_id'     => 'required|exists:items,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $lastId = InventoryEntry::max('id') ?? 0;
            $entryNumber = 'ENT-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            $total = 0;

            $entry = InventoryEntry::create([
                'entry_number'   => $entryNumber,
                'supplier_id'    => $validated['supplier_id'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'entry_date'     => $validated['entry_date'],
                'received_by'    => $validated['received_by'],
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'completed',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty  = $itemData['quantity'];
                $price = $itemData['unit_price'] ?? null;

                // Incrementar stock en catálogo
                $item->increment('stock', $qty);
                if ($price) $total += $qty * $price;

                InventoryEntryItem::create([
                    'inventory_entry_id' => $entry->id,
                    'item_id'            => $itemData['item_id'],
                    'quantity'           => $qty,
                    'unit_price'         => $price,
                ]);

                // ── SINERGIA: Auto-generar registros de activos fijos ──
                if ($item->is_asset) {
                    for ($i = 0; $i < $qty; $i++) {
                        $lastAssetId = \App\Models\FixedAsset::max('id') ?? 0;
                        \App\Models\FixedAsset::create([
                            'item_id'        => $item->id,
                            'provider_id'    => $validated['supplier_id'] ?? null,
                            'inventory_code' => 'INV-' . date('Y') . '-' . str_pad($lastAssetId + 1 + $i, 6, '0', STR_PAD_LEFT),
                            'serial_number'  => 'PENDIENTE',
                            'purchase_price' => $price,
                            'status'         => 'active',
                        ]);
                    }
                }
            }

            $entry->update(['total_amount' => $total ?: null]);
            $entry->load(['supplier', 'items.item.category']);

            DB::commit();
            return response()->json([
                'message' => 'Entrada de inventario registrada',
                'data'    => $entry,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar entrada', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $entry = InventoryEntry::with(['supplier', 'items.item.category'])->findOrFail($id);
        return response()->json(['data' => $entry]);
    }

    public function destroy($id)
    {
        // Solo cancelar, no eliminar físicamente
        $entry = InventoryEntry::findOrFail($id);
        if ($entry->status === 'cancelled') {
            return response()->json(['message' => 'La entrada ya está cancelada'], 422);
        }
        // Revertir stock
        foreach ($entry->items as $ei) {
            Item::where('id', $ei->item_id)->decrement('stock', $ei->quantity);
        }
        $entry->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Entrada cancelada y stock revertido']);
    }
}
