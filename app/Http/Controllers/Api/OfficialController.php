<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficialController extends Controller
{
    public function index()
    {
        $officials = Official::with('office')
            ->withCount(['deliveries', 'activeDeliveries'])
            ->get();
        return response()->json(['data' => $officials]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'document_number' => 'required|string|max:50|unique:officials,document_number',
            'document_type'   => 'required|in:CC,TI,CE,PS',
            'position'        => 'required|string|max:255',
            'office_id'       => 'required|exists:offices,id',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'is_active'       => 'boolean',
        ]);

        $official = Official::create($validated);
        $official->load('office');

        return response()->json(['message' => 'Funcionario registrado', 'data' => $official], 201);
    }

    public function show(Official $official)
    {
        $official->load(['office', 'deliveries.fixedAsset.item', 'deliveries.item']);
        return response()->json(['data' => $official]);
    }

    public function update(Request $request, Official $official)
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'document_number' => ['required', 'string', 'max:50', Rule::unique('officials')->ignore($official->id)],
            'document_type'   => 'required|in:CC,TI,CE,PS',
            'position'        => 'required|string|max:255',
            'office_id'       => 'required|exists:offices,id',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'is_active'       => 'boolean',
        ]);

        $official->update($validated);
        $official->load('office');

        return response()->json(['message' => 'Funcionario actualizado', 'data' => $official]);
    }

    public function destroy(Official $official)
    {
        $official->delete();
        return response()->json(['message' => 'Funcionario eliminado']);
    }
}
