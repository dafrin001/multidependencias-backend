<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRecord;
use App\Models\Item;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * List all delivery records
     */
    public function index(Request $request)
    {
        $query = DeliveryRecord::with([
            'official.office',
            'fixedAsset.item.category',
            'item.category',
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
     * Create a new delivery record (Acta de Entrega)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:asset,consumable',
            'fixed_asset_id'  => 'required_if:type,asset|nullable|exists:fixed_assets,id',
            'item_id'         => 'required_if:type,consumable|nullable|exists:items,id',
            'quantity'        => 'required_if:type,consumable|integer|min:1',
            'official_id'     => 'required|exists:officials,id',
            'delivered_by'    => 'required|string|max:255',
            'delivery_date'   => 'required|date',
            'notes'           => 'nullable|string',
            'signature_data'  => 'nullable|string', // Base64 de la firma
        ]);

        DB::beginTransaction();
        try {
            // Reducir stock si es consumible
            if ($validated['type'] === 'consumable') {
                $item = Item::findOrFail($validated['item_id']);
                if ($item->is_asset) {
                    return response()->json(['message' => 'El artículo seleccionado es un activo fijo, no un consumible'], 422);
                }
                if ($item->stock < $validated['quantity']) {
                    return response()->json(['message' => "Stock insuficiente. Disponible: {$item->stock}"], 422);
                }
                $item->decrement('stock', $validated['quantity']);
            }

            // Generar número de acta automático
            $lastActa = DeliveryRecord::withTrashed()->max('id') ?? 0;
            $validated['acta_number'] = 'ACTA-' . date('Y') . '-' . str_pad($lastActa + 1, 4, '0', STR_PAD_LEFT);

            $record = DeliveryRecord::create($validated);
            $record->load(['official.office', 'fixedAsset.item', 'item.category']);

            DB::commit();
            return response()->json([
                'message' => 'Acta de entrega registrada exitosamente',
                'data'    => $record,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar la entrega', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get full delivery record (with signature for printing)
     */
    public function show($id)
    {
        $record = DeliveryRecord::withoutHidden()
            ->with(['official.office', 'fixedAsset.item.category', 'item.category'])
            ->findOrFail($id);

        return response()->json(['data' => $record]);
    }

    /**
     * Register a return
     */
    public function registerReturn(Request $request, $id)
    {
        $validated = $request->validate([
            'returned_date' => 'required|date',
            'return_notes'  => 'nullable|string',
        ]);

        $record = DeliveryRecord::findOrFail($id);

        if ($record->is_returned) {
            return response()->json(['message' => 'Este ítem ya fue devuelto anteriormente'], 422);
        }

        DB::beginTransaction();
        try {
            // Retornar stock si era consumible (opcional según política)
            if ($record->type === 'consumable' && $record->item_id) {
                Item::where('id', $record->item_id)->increment('stock', $record->quantity);
            }

            $record->update([
                'is_returned'  => true,
                'returned_date'=> $validated['returned_date'],
                'return_notes' => $validated['return_notes'] ?? null,
            ]);

            DB::commit();
            return response()->json(['message' => 'Devolución registrada', 'data' => $record]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar devolución', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Items with low stock
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
