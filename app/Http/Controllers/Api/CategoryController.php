<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('items')->get();
        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code',
        ]);
        $category = Category::create($validated);
        return response()->json(['message' => 'Categoría creada', 'data' => $category], 201);
    }

    public function show(Category $category)
    {
        return response()->json(['data' => $category->load('items')]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('categories')->ignore($category->id)],
        ]);
        $category->update($validated);
        return response()->json(['message' => 'Categoría actualizada', 'data' => $category]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
}
