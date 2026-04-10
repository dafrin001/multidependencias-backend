<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\FixedAsset;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    /**
     * Assign a fixed asset to an office and custodian
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'fixed_asset_id' => 'required|exists:fixed_assets,id',
            'office_id' => 'required|exists:offices,id',
            'custodian_name' => 'required|string|max:255',
            'assignment_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Unassign previous if exists
            Assignment::where('fixed_asset_id', $validated['fixed_asset_id'])
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new assignment
            $assignment = Assignment::create([
                'fixed_asset_id' => $validated['fixed_asset_id'],
                'office_id' => $validated['office_id'],
                'custodian_name' => $validated['custodian_name'],
                'assignment_date' => $validated['assignment_date'],
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json(['message' => 'Asset assigned successfully', 'data' => $assignment], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to assign asset', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get history of assignments for a fixed asset
     */
    public function history($fixed_asset_id)
    {
        $assignments = Assignment::with('office')
            ->where('fixed_asset_id', $fixed_asset_id)
            ->orderBy('assignment_date', 'desc')
            ->get();

        return response()->json(['data' => $assignments]);
    }
}
