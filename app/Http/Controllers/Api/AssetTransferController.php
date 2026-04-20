<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetTransfer;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetTransferController extends Controller
{
    public function index()
    {
        $transfers = AssetTransfer::with([
            'fixedAsset.item', 'fixedAsset.provider',
            'fromOffice', 'toOffice',
        ])->orderBy('transfer_date', 'desc')->get();
        return response()->json(['data' => $transfers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fixed_asset_id' => 'required|exists:fixed_assets,id',
            'from_office_id' => 'nullable|exists:offices,id',
            'to_office_id'   => 'required|exists:offices,id',
            'transferred_by' => 'required|string|max:255',
            'received_by'    => 'nullable|string|max:255',
            'transfer_date'  => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $asset = FixedAsset::findOrFail($validated['fixed_asset_id']);

        if ($asset->is_disposed) {
            return response()->json(['message' => 'El activo fue dado de baja y no puede transferirse'], 422);
        }

        DB::beginTransaction();
        try {
            $lastId = AssetTransfer::max('id') ?? 0;
            $number = 'TRASL-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $transfer = AssetTransfer::create(array_merge($validated, [
                'transfer_number' => $number,
                'status'          => 'completed',
            ]));

            // ── SINERGIA: Actualizar la ubicación (asignación) del activo ──
            // Desactivar asignación anterior si existe
            \App\Models\Assignment::where('fixed_asset_id', $asset->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Crear nueva asignación activa para la dependencia de destino
            \App\Models\Assignment::create([
                'fixed_asset_id' => $asset->id,
                'office_id'      => $validated['to_office_id'],
                'custodian_name' => $validated['received_by'] ?? 'Almacén/Por determinar',
                'assignment_date' => $validated['transfer_date'],
                'is_active'      => true,
            ]);

            $transfer->load(['fixedAsset.item', 'fromOffice', 'toOffice']);
            DB::commit();

            return response()->json([
                'message' => 'Traslado registrado exitosamente',
                'data'    => $transfer,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar traslado', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $t = AssetTransfer::with(['fixedAsset.item.category', 'fromOffice', 'toOffice'])->findOrFail($id);
        return response()->json(['data' => $t]);
    }
}
