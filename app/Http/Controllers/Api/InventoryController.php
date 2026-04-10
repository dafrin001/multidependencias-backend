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
            'is_asset'    => 'nullable|boolean',
            'low_stock'   => 'nullable|boolean',
        ]);

        $query = Item::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_asset')) {
            $query->where('is_asset', $request->boolean('is_asset'));
        }

        // Filtrar solo items con stock bajo
        if ($request->boolean('low_stock')) {
            $query->where('is_asset', false)->whereColumn('stock', '<=', 'min_stock');
        }

        // Consumibles: ordenar primero los de stock más bajo
        $items = $query->orderByRaw('CASE WHEN is_asset = 0 AND stock <= min_stock THEN 0 ELSE 1 END')
                       ->orderBy('name')
                       ->get();

        $lowStockCount = $items->where('is_asset', false)
                               ->filter(fn($i) => $i->stock <= $i->min_stock)
                               ->count();

        return response()->json([
            'data'           => $items,
            'low_stock_count'=> $lowStockCount,
        ]);
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
