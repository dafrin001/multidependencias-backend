<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetDisposal;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetDisposalController extends Controller
{
    public function index()
    {
        $disposals = AssetDisposal::with(['fixedAsset.item.category', 'fixedAsset.provider'])
            ->orderBy('disposal_date', 'desc')->get();
        return response()->json(['data' => $disposals]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fixed_asset_id'    => 'required|exists:fixed_assets,id',
            'reason'            => 'required|in:damage,loss,obsolescence,theft,transfer,other',
            'disposal_date'     => 'required|date',
            'authorized_by'     => 'required|string|max:255',
            'processed_by'      => 'required|string|max:255',
            'description'       => 'nullable|string',
            'resolution_number' => 'nullable|string|max:100',
        ]);

        $asset = FixedAsset::findOrFail($validated['fixed_asset_id']);

        if ($asset->is_disposed) {
            return response()->json(['message' => 'El activo ya fue dado de baja anteriormente'], 422);
        }

        DB::beginTransaction();
        try {
            $lastId = AssetDisposal::max('id') ?? 0;
            $number = 'BAJA-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $disposal = AssetDisposal::create(array_merge($validated, [
                'disposal_number' => $number,
            ]));

            // Marcar activo como dado de baja y desactivar ubicación activa
            $asset->update(['is_disposed' => true, 'status' => 'baja']);
            
            \App\Models\Assignment::where('fixed_asset_id', $asset->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $disposal->load(['fixedAsset.item.category']);

            DB::commit();
            return response()->json([
                'message' => 'Baja de activo registrada correctamente',
                'data'    => $disposal,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar baja', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $disposal = AssetDisposal::with(['fixedAsset.item.category', 'fixedAsset.provider'])->findOrFail($id);
        return response()->json(['data' => $disposal]);
    }
}
