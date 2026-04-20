<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyRequestController extends Controller
{
    public function index()
    {
        $requests = SupplyRequest::with(['office', 'items.item.category'])
            ->orderBy('request_date', 'desc')->get();
        return response()->json(['data' => $requests]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id'       => 'required|exists:offices,id',
            'requested_by'    => 'required|string|max:255',
            'request_date'    => 'required|date',
            'needed_by'       => 'nullable|date',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.requested_quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $lastId = SupplyRequest::max('id') ?? 0;
            $number = 'SOL-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $req = SupplyRequest::create([
                'request_number' => $number,
                'office_id'      => $validated['office_id'],
                'requested_by'   => $validated['requested_by'],
                'request_date'   => $validated['request_date'],
                'needed_by'      => $validated['needed_by'] ?? null,
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                SupplyRequestItem::create([
                    'supply_request_id'  => $req->id,
                    'item_id'            => $item['item_id'],
                    'requested_quantity' => $item['requested_quantity'],
                ]);
            }

            $req->load(['office', 'items.item.category']);
            DB::commit();
            return response()->json(['message' => 'Solicitud registrada', 'data' => $req], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /** Approve / dispatch / reject a request */
    public function update(Request $request, $id)
    {
        $req       = SupplyRequest::with('items.item')->findOrFail($id);
        $validated = $request->validate([
            'action'           => 'required|in:approve,dispatch,reject',
            'dispatched_by'    => 'nullable|string|max:255',
            'rejection_reason' => 'nullable|string',
            'items'            => 'nullable|array',
            'items.*.supply_request_item_id' => 'nullable|exists:supply_request_items,id',
            'items.*.approved_quantity'       => 'nullable|integer|min:0',
            'items.*.dispatched_quantity'     => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $req->update(['status' => 'approved']);

                // Guardar cantidades aprobadas
                foreach (($validated['items'] ?? []) as $itemData) {
                    SupplyRequestItem::where('id', $itemData['supply_request_item_id'])
                        ->update(['approved_quantity' => $itemData['approved_quantity']]);
                }

            } elseif ($validated['action'] === 'dispatch') {
                foreach (($validated['items'] ?? []) as $itemData) {
                    $ri = SupplyRequestItem::findOrFail($itemData['supply_request_item_id']);
                    $dispatchQty = $itemData['dispatched_quantity'];

                    // Verificar y descontar stock
                    $item = Item::findOrFail($ri->item_id);
                    if ($item->stock < $dispatchQty) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stock insuficiente para '{$item->name}'. Disponible: {$item->stock}"
                        ], 422);
                    }
                    $item->decrement('stock', $dispatchQty);
                    $ri->update(['dispatched_quantity' => $dispatchQty]);
                }

                $req->update([
                    'status'          => 'dispatched',
                    'dispatch_date'   => now()->toDateString(),
                    'dispatched_by'   => $validated['dispatched_by'] ?? null,
                ]);

            } elseif ($validated['action'] === 'reject') {
                $req->update([
                    'status'           => 'rejected',
                    'rejection_reason' => $validated['rejection_reason'] ?? null,
                ]);
            }

            $req->load(['office', 'items.item.category']);
            DB::commit();
            return response()->json(['message' => 'Solicitud actualizada', 'data' => $req]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $req = SupplyRequest::with(['office', 'items.item.category'])->findOrFail($id);
        return response()->json(['data' => $req]);
    }
}
