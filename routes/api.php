<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AssetAssignmentController;

use App\Http\Controllers\Api\FixedAssetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('providers', ProviderController::class);
Route::apiResource('fixed-assets', FixedAssetController::class);

Route::get('inventory', [InventoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // Asset Assignment (requiere protección real)
    Route::post('assignments/assign', [AssetAssignmentController::class, 'assign']);
    Route::get('fixed-assets/{id}/assignments', [AssetAssignmentController::class, 'history']);
});
