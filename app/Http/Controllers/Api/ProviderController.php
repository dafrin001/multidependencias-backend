<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = Provider::all();
        return response()->json(['data' => $providers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nit' => 'required|string|unique:providers,nit',
            'company_name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
        ]);

        $provider = Provider::create($validated);

        return response()->json(['message' => 'Provider created successfully', 'data' => $provider], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        return response()->json(['data' => $provider]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'nit' => [
                'required',
                'string',
                Rule::unique('providers')->ignore($provider->id),
            ],
            'company_name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
        ]);

        $provider->update($validated);

        return response()->json(['message' => 'Provider updated successfully', 'data' => $provider]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Provider $provider)
    {
        $provider->delete();
        return response()->json(['message' => 'Provider deleted successfully']);
    }
}
