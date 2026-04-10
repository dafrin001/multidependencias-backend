<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\Item;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Get inventory filtered by category and stock status.
     * stock status could be consumable (is_asset=false) or assets (is_asset=true)
     */
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'is_asset' => 'nullable|boolean',
        ]);

        $query = Item::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_asset')) {
            $query->where('is_asset', $request->boolean('is_asset'));
        }

        $items = $query->get();

        return response()->json(['data' => $items]);
    }

    /**
     * Get specific fixed asset details including current assignment
     */
    public function showFixedAsset($id)
    {
        $asset = FixedAsset::with(['item.category', 'provider', 'activeAssignment.office'])->findOrFail($id);
        
        // Convert image_url to absolute URL if present
        if ($asset->image_url) {
            $asset->image_url = url('storage/' . $asset->image_url);
        }

        return response()->json(['data' => $asset]);
    }
}
