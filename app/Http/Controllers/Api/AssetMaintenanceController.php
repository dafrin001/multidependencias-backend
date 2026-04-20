<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetMaintenance;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetMaintenance::with(['fixedAsset.item'])
            ->orderBy('maintenance_date', 'desc');

        if ($request->query('fixed_asset_id')) {
            $query->where('fixed_asset_id', $request->query('fixed_asset_id'));
        }
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fixed_asset_id'        => 'required|exists:fixed_assets,id',
            'type'                  => 'required|in:preventive,corrective,upgrade',
            'maintenance_date'      => 'required|date',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'technician'            => 'nullable|string|max:255',
            'cost'                  => 'nullable|numeric|min:0',
            'description'           => 'required|string',
            'status'                => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $maintenance = AssetMaintenance::create($validated);
        $maintenance->load(['fixedAsset.item']);

        return response()->json([
            'message' => 'Mantenimiento registrado',
            'data'    => $maintenance,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $maintenance = AssetMaintenance::findOrFail($id);
        $validated   = $request->validate([
            'status'                => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'next_maintenance_date' => 'nullable|date',
            'cost'                  => 'nullable|numeric|min:0',
            'description'           => 'sometimes|string',
        ]);
        $maintenance->update($validated);
        return response()->json(['message' => 'Actualizado', 'data' => $maintenance]);
    }

    public function destroy($id)
    {
        AssetMaintenance::findOrFail($id)->delete();
        return response()->json(['message' => 'Registro de mantenimiento eliminado']);
    }
}
