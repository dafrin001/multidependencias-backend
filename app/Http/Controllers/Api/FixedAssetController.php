<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::with(['item.category', 'provider', 'activeAssignment.office'])->get();
        return response()->json(['data' => $assets]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'provider_id' => 'required|exists:providers,id',
            'inventory_code' => 'required|string|unique:fixed_assets,inventory_code',
            'serial_number' => 'nullable|string',
            'purchase_price' => 'nullable|numeric',
            'status' => 'required|in:nuevo,bueno,regular,malo,baja',
        ]);

        $asset = FixedAsset::create($validated);
        $asset->load(['item.category', 'provider']);

        return response()->json(['message' => 'Asset created successfully', 'data' => $asset], 201);
    }

    public function show($id)
    {
        $asset = FixedAsset::with(['item.category', 'provider', 'activeAssignment.office'])->findOrFail($id);
        
        if ($asset->image_url) {
            $asset->image_url = url('storage/' . $asset->image_url);
        }

        return response()->json(['data' => $asset]);
    }

    public function update(Request $request, $id)
    {
        $asset = FixedAsset::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'provider_id' => 'required|exists:providers,id',
            'inventory_code' => [
                'required',
                'string',
                Rule::unique('fixed_assets')->ignore($asset->id),
            ],
            'serial_number' => 'nullable|string',
            'purchase_price' => 'nullable|numeric',
            'status' => 'required|in:nuevo,bueno,regular,malo,baja',
        ]);

        $asset->update($validated);
        $asset->load(['item.category', 'provider', 'activeAssignment.office']);

        return response()->json(['message' => 'Asset updated successfully', 'data' => $asset]);
    }

    public function destroy($id)
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->delete();
        return response()->json(['message' => 'Asset deleted successfully']);
    }
}
