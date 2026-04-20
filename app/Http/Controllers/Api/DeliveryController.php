<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRecord;
use App\Models\DeliveryItem;
use App\Models\Item;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * List all delivery records with their items.
     */
    public function index(Request $request)
    {
        $query = DeliveryRecord::with([
            'official.office',
            'deliveryItems.fixedAsset.item.category',
            'deliveryItems.item.category',
        ])->orderBy('delivery_date', 'desc');

        if ($request->query('is_returned') !== null) {
            $query->where('is_returned', $request->boolean('is_returned'));
        }
        if ($request->query('official_id')) {
            $query->where('official_id', $request->query('official_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Create a new delivery record with multiple items (Acta de Entrega).
     *
     * Expected payload:
     * {
     *   "official_id": 1,
     *   "delivered_by": "María González",
     *   "delivery_date": "2026-04-11",
     *   "notes": "...",
     *   "signature_data": "data:image/png;base64,...",
     *   "items": [
     *     { "type": "asset",      "fixed_asset_id": 3 },
     *     { "type": "consumable", "item_id": 7, "quantity": 5 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'official_id'       => 'required|exists:officials,id',
            'delivered_by'      => 'required|string|max:255',
            'delivery_date'     => 'required|date',
            'notes'             => 'nullable|string',
            'signature_data'    => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.type'      => 'required|in:asset,consumable',
            'items.*.fixed_asset_id' => 'required_if:items.*.type,asset|nullable|exists:fixed_assets,id',
            'items.*.item_id'   => 'required_if:items.*.type,consumable|nullable|exists:items,id',
            'items.*.quantity'  => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Generar número de acta automático
            $lastId = DeliveryRecord::withTrashed()->max('id') ?? 0;
            $actaNumber = 'ACTA-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // Crear el acta principal
            $record = DeliveryRecord::create([
                'acta_number'    => $actaNumber,
                'official_id'    => $validated['official_id'],
                'delivered_by'   => $validated['delivered_by'],
                'delivery_date'  => $validated['delivery_date'],
                'notes'          => $validated['notes'] ?? null,
                'signature_data' => $validated['signature_data'] ?? null,
                'is_returned'    => false,
            ]);

            // Procesar cada ítem del acta
            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'] ?? 1;

                if ($itemData['type'] === 'consumable') {
                    $consumable = Item::findOrFail($itemData['item_id']);
                    if ($consumable->is_asset) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "El artículo '{$consumable->name}' es un activo fijo, no un consumible."
                        ], 422);
                    }
                    if ($consumable->stock < $qty) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stock insuficiente para '{$consumable->name}'. Disponible: {$consumable->stock}"
                        ], 422);
                    }
                    $consumable->decrement('stock', $qty);
                }

                if ($itemData['type'] === 'asset' && !empty($itemData['fixed_asset_id'])) {
                    $asset = FixedAsset::findOrFail($itemData['fixed_asset_id']);
                    
                    // Desactivar asignación previa
                    \App\Models\Assignment::where('fixed_asset_id', $asset->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);

                    // Crear nueva asignación activa vinculada al funcionario y su oficina
                    \App\Models\Assignment::create([
                        'fixed_asset_id' => $asset->id,
                        'office_id'      => $record->official->office_id ?? null,
                        'custodian_name' => $record->official->full_name ?? 'Desconocido',
                        'assignment_date' => $validated['delivery_date'],
                        'is_active'      => true,
                    ]);
                }

                DeliveryItem::create([
                    'delivery_record_id' => $record->id,
                    'type'               => $itemData['type'],
                    'fixed_asset_id'     => $itemData['fixed_asset_id'] ?? null,
                    'item_id'            => $itemData['item_id'] ?? null,
                    'quantity'           => $qty,
                ]);
            }

            $record->load(['official.office', 'deliveryItems.fixedAsset.item.category', 'deliveryItems.item.category']);

            DB::commit();
            return response()->json([
                'message' => 'Acta de entrega registrada exitosamente',
                'data'    => $record,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la entrega',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get full delivery record (including signature and all items) for printing.
     */
    public function show($id)
    {
        $record = DeliveryRecord::with([
            'official.office',
            'deliveryItems.fixedAsset.item.category',
            'deliveryItems.item.category',
        ])->findOrFail($id);

        // Incluir signature_data (oculto en listados pero necesario para imprimir)
        $record->makeVisible('signature_data');

        return response()->json(['data' => $record]);
    }

    /**
     * Register a return for the whole acta.
     */
    public function registerReturn(Request $request, $id)
    {
        $validated = $request->validate([
            'returned_date' => 'required|date',
            'return_notes'  => 'nullable|string',
        ]);

        $record = DeliveryRecord::findOrFail($id);

        if ($record->is_returned) {
            return response()->json(['message' => 'Este acta ya fue devuelta anteriormente'], 422);
        }

        DB::beginTransaction();
        try {
            // Devolver stock de consumibles
            foreach ($record->deliveryItems as $di) {
                if ($di->type === 'consumable' && $di->item_id) {
                    Item::where('id', $di->item_id)->increment('stock', $di->quantity);
                }
            }

            $record->update([
                'is_returned'   => true,
                'returned_date' => $validated['returned_date'],
                'return_notes'  => $validated['return_notes'] ?? null,
            ]);

            DB::commit();
            return response()->json(['message' => 'Devolución registrada', 'data' => $record]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar devolución',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Items with low stock.
     */
    public function lowStockAlerts()
    {
        $items = Item::where('is_asset', false)
            ->whereColumn('stock', '<=', 'min_stock')
            ->with('category')
            ->orderBy('stock')
            ->get();

        return response()->json(['data' => $items, 'count' => $items->count()]);
    }
}
