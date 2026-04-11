<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AssetAssignmentController;
use App\Http\Controllers\Api\FixedAssetController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OfficialController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\ItemController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── Recursos principales ───────────────────────────────────────────
Route::apiResource('providers',   ProviderController::class);
Route::apiResource('fixed-assets', FixedAssetController::class);
Route::apiResource('offices',     OfficeController::class);
Route::apiResource('categories',  CategoryController::class);
Route::apiResource('officials',   OfficialController::class);
Route::apiResource('items',       ItemController::class);

// ── Catálogo e inventario ──────────────────────────────────────────
Route::get('inventory', [InventoryController::class, 'index']);

// ── Asignaciones de activos ────────────────────────────────────────
Route::post('assignments/assign',            [AssetAssignmentController::class, 'assign']);
Route::get('assignments',                    [AssetAssignmentController::class, 'index']);
Route::get('fixed-assets/{id}/assignments',  [AssetAssignmentController::class, 'history']);
Route::delete('assignments/{id}',            [AssetAssignmentController::class, 'destroy']);

// ── Actas de entrega (Deliveries) ─────────────────────────────────
Route::get('deliveries',                    [DeliveryController::class, 'index']);
Route::post('deliveries',                   [DeliveryController::class, 'store']);
Route::get('deliveries/{id}',               [DeliveryController::class, 'show']);
Route::patch('deliveries/{id}/return',      [DeliveryController::class, 'registerReturn']);
Route::get('alerts/low-stock',              [DeliveryController::class, 'lowStockAlerts']);
