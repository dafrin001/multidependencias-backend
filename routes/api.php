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
use App\Http\Controllers\Api\InventoryEntryController;
use App\Http\Controllers\Api\AssetDisposalController;
use App\Http\Controllers\Api\AssetTransferController;
use App\Http\Controllers\Api\AssetMaintenanceController;
use App\Http\Controllers\Api\SupplyRequestController;
use App\Http\Controllers\Api\HrController;

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

// ── Entradas de Inventario ─────────────────────────────────────────
Route::get('inventory-entries',             [InventoryEntryController::class, 'index']);
Route::post('inventory-entries',            [InventoryEntryController::class, 'store']);
Route::get('inventory-entries/{id}',        [InventoryEntryController::class, 'show']);
Route::delete('inventory-entries/{id}',     [InventoryEntryController::class, 'destroy']);

// ── Bajas de Activos ──────────────────────────────────────────────
Route::get('asset-disposals',               [AssetDisposalController::class, 'index']);
Route::post('asset-disposals',              [AssetDisposalController::class, 'store']);
Route::get('asset-disposals/{id}',          [AssetDisposalController::class, 'show']);

// ── Traslados entre Dependencias ──────────────────────────────────
Route::get('asset-transfers',               [AssetTransferController::class, 'index']);
Route::post('asset-transfers',              [AssetTransferController::class, 'store']);
Route::get('asset-transfers/{id}',          [AssetTransferController::class, 'show']);

// ── Mantenimiento de Activos ──────────────────────────────────────
Route::get('asset-maintenances',            [AssetMaintenanceController::class, 'index']);
Route::post('asset-maintenances',           [AssetMaintenanceController::class, 'store']);
Route::patch('asset-maintenances/{id}',     [AssetMaintenanceController::class, 'update']);
Route::delete('asset-maintenances/{id}',    [AssetMaintenanceController::class, 'destroy']);

// ── Solicitudes de Suministros ────────────────────────────────────
Route::get('supply-requests',               [SupplyRequestController::class, 'index']);
Route::post('supply-requests',              [SupplyRequestController::class, 'store']);
Route::get('supply-requests/{id}',          [SupplyRequestController::class, 'show']);
Route::patch('supply-requests/{id}',        [SupplyRequestController::class, 'update']);

// ── Kardex (movimientos por artículo) ────────────────────────────
Route::get('kardex/{item_id}', function ($itemId) {
    $item = \App\Models\Item::with('category')->findOrFail($itemId);

    $entries = \App\Models\InventoryEntryItem::with('entry.supplier')
        ->where('item_id', $itemId)->get()->map(fn($e) => [
            'date'        => $e->entry->entry_date,
            'type'        => 'entrada',
            'reference'   => $e->entry->entry_number,
            'description' => 'Entrada: ' . ($e->entry->supplier->company_name ?? 'Sin proveedor'),
            'entrada'     => $e->quantity,
            'salida'      => 0,
            'unit_price'  => $e->unit_price,
        ]);

    $deliverySalidas = \App\Models\DeliveryItem::with(['deliveryRecord.official'])
        ->where('item_id', $itemId)->where('type', 'consumable')->get()
        ->map(fn($d) => [
            'date'        => $d->deliveryRecord->delivery_date,
            'type'        => 'salida',
            'reference'   => $d->deliveryRecord->acta_number,
            'description' => 'Entrega: ' . ($d->deliveryRecord->official->full_name ?? ''),
            'entrada'     => 0,
            'salida'      => $d->quantity,
            'unit_price'  => null,
        ]);

    $returns = \App\Models\DeliveryItem::with(['deliveryRecord'])
        ->where('item_id', $itemId)->where('type', 'consumable')
        ->whereHas('deliveryRecord', fn($q) => $q->where('is_returned', true))
        ->get()->map(fn($d) => [
            'date'        => $d->deliveryRecord->returned_date,
            'type'        => 'devolucion',
            'reference'   => $d->deliveryRecord->acta_number,
            'description' => 'Devolución de entrega',
            'entrada'     => $d->quantity,
            'salida'      => 0,
            'unit_price'  => null,
        ]);

    $supplyExits = \App\Models\SupplyRequestItem::with(['request.office'])
        ->where('item_id', $itemId)->whereNotNull('dispatched_quantity')
        ->where('dispatched_quantity', '>', 0)->get()
        ->map(fn($s) => [
            'date'        => $s->request->dispatch_date,
            'type'        => 'salida',
            'reference'   => $s->request->request_number,
            'description' => 'Despacho: ' . ($s->request->office->name ?? ''),
            'entrada'     => 0,
            'salida'      => $s->dispatched_quantity,
            'unit_price'  => null,
        ]);

    $balance  = 0;
    $movements = $entries->concat($deliverySalidas)->concat($returns)->concat($supplyExits)
        ->sortBy('date')->values()->map(function ($mov) use (&$balance) {
            $mov = (array) $mov;
            $balance += ($mov['entrada'] - $mov['salida']);
            return array_merge($mov, ['saldo' => $balance]);
        });

    return response()->json([
        'item'          => $item,
        'movements'     => $movements,
        'current_stock' => $item->stock,
    ]);
});
// ── Reportes ───────────────────────────────────────────────────────
use App\Http\Controllers\Api\ReportController;
Route::prefix('reports')->group(function () {
    Route::get('inventory-stock',  [ReportController::class, 'inventoryStock']);
    Route::get('assets-by-office', [ReportController::class, 'assetsByOffice']);
    Route::get('movements',        [ReportController::class, 'movements']);
});

// ── Talento Humano ────────────────────────────────────────────────
Route::prefix('hr')->group(function () {
    Route::get('stats',             [HrController::class, 'stats']);
    Route::get('positions',         [HrController::class, 'getPositions']);
    Route::post('positions',        [HrController::class, 'storePosition']);
    // Nómina
    Route::get('payroll',           [HrController::class, 'getPayrollPeriods']);
    Route::get('payroll/{id}',      [HrController::class, 'getPayrollPeriod']);
    Route::post('payroll',          [HrController::class, 'processPayroll']);
    Route::post('payroll/contractors', [HrController::class, 'processContractorPayroll']);
    Route::patch('payroll/{id}/pay', [HrController::class, 'markPayrollPaid']);
    Route::patch('payroll/items/{id}', [HrController::class, 'updatePayrollItem']);
    
    // Contratos y Estampillas
    Route::get('contracts',         [HrController::class, 'getContracts']);
    Route::post('contracts',        [HrController::class, 'storeContract']);
    Route::post('contracts/deductions', [HrController::class, 'storeDeductionRule']);

    // Otros
    Route::get('training',          [HrController::class, 'getTrainingPrograms']);
    Route::post('training',         [HrController::class, 'storeTraining']);
    Route::get('sst',               [HrController::class, 'getSstRecords']);
    Route::get('situations',        [HrController::class, 'getSituations']);
    Route::post('situations',       [HrController::class, 'storeSituation']);
    Route::put('situations/{id}',   [HrController::class, 'updateSituation']);
    
    // Comités y reuniones
    Route::get('committees',        [HrController::class, 'getCommittees']);
    Route::post('committees',       [HrController::class, 'storeCommittee']);
    Route::post('committees/members', [HrController::class, 'storeMember']);
    Route::get('meetings',          [HrController::class, 'getMeetings']);
    Route::post('meetings',         [HrController::class, 'storeMeeting']);

    // Directorio
    Route::get('officials',         [HrController::class, 'getOfficials']);
    Route::post('officials',        [HrController::class, 'storeOfficial']);

    // EDL
    Route::get('edl',               [HrController::class, 'getEdlRecords']);
    Route::post('edl',              [HrController::class, 'storeEdlRecord']);
    Route::patch('edl/{id}/score',  [HrController::class, 'updateEdlScore']);

    // Configuraciones
    Route::get('settings',          [HrController::class, 'getSettings']);
    Route::patch('settings/{id}',   [HrController::class, 'updateSetting']);
    Route::get('stamps',            [HrController::class, 'getStampCatalog']);
    Route::post('stamps',           [HrController::class, 'storeStampCatalog']);
    Route::put('stamps/{id}',       [HrController::class, 'updateStampCatalog']);
    Route::delete('stamps/{id}',    [HrController::class, 'deleteStampCatalog']);

    // Reportes y Eliminaciones
    Route::get('reports',           [HrController::class, 'exportReport']);
    Route::delete('positions/{id}', [HrController::class, 'deletePosition']);
    Route::delete('officials/{id}', [HrController::class, 'deleteOfficial']);
});

// ── Gestión del Riesgo (RUFE) ─────────────────────────────────────
use App\Http\Controllers\Api\RiskManagementController;
Route::get('risk-management/export', [RiskManagementController::class, 'export']);
Route::get('risk-management/stats', [RiskManagementController::class, 'stats']);
Route::apiResource('risk-management', RiskManagementController::class);
