<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::withCount('assignments')->get();
        return response()->json(['data' => $offices]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:offices,name',
        ]);
        $office = Office::create($validated);
        return response()->json(['message' => 'Secretaría creada', 'data' => $office], 201);
    }

    public function show(Office $office)
    {
        return response()->json(['data' => $office->load('assignments')]);
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:offices,name,' . $office->id,
        ]);
        $office->update($validated);
        return response()->json(['message' => 'Secretaría actualizada', 'data' => $office]);
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->json(['message' => 'Secretaría eliminada']);
    }
}
