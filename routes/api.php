<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AssetAssignmentController;
use App\Http\Controllers\Api\FixedAssetController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Recursos principales (abiertos para integración con el panel web y Flutter)
Route::apiResource('providers',    ProviderController::class);
Route::apiResource('fixed-assets', FixedAssetController::class);
Route::apiResource('offices',      OfficeController::class);
Route::apiResource('categories',   CategoryController::class);

// Catálogo de artículos filtrado
Route::get('inventory', [InventoryController::class, 'index']);

// Asignaciones
Route::post('assignments/assign',            [AssetAssignmentController::class, 'assign']);
Route::get('assignments',                    [AssetAssignmentController::class, 'index']);
Route::get('fixed-assets/{id}/assignments',  [AssetAssignmentController::class, 'history']);
