<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->get();
        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:items,name',
            'category_id' => 'required|exists:categories,id',
            'is_asset'    => 'required|boolean',
            'stock'       => 'required_if:is_asset,false|integer|min:0',
            'min_stock'   => 'required_if:is_asset,false|integer|min:1',
        ]);

        if ($validated['is_asset']) {
            $validated['stock']     = 0;
            $validated['min_stock'] = 0;
        }

        $item = Item::create($validated);
        $item->load('category');
        return response()->json(['message' => 'Artículo creado', 'data' => $item], 201);
    }

    public function show(Item $item)
    {
        return response()->json(['data' => $item->load('category')]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('items')->ignore($item->id)],
            'category_id' => 'required|exists:categories,id',
            'is_asset'    => 'required|boolean',
            'stock'       => 'required_if:is_asset,false|integer|min:0',
            'min_stock'   => 'required_if:is_asset,false|integer|min:1',
        ]);

        $item->update($validated);
        $item->load('category');
        return response()->json(['message' => 'Artículo actualizado', 'data' => $item]);
    }

    public function destroy(Item $item)
    {
        if ($item->fixedAssets()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar: tiene activos fijos asociados'], 422);
        }
        $item->delete();
        return response()->json(['message' => 'Artículo eliminado']);
    }
}
