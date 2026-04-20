<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanningController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/almacen', [DashboardController::class, 'almacen'])->name('almacen.index');
Route::get('/talento-humano', [DashboardController::class, 'hr'])->name('hr.index');
Route::get('/gestion-riesgo', [DashboardController::class, 'risk'])->name('risk.index');
Route::get('/planeacion-obras', [PlanningController::class, 'index'])->name('planning.index');

// API-like routes for Geo-Portal
Route::get('/api/planning/works', [PlanningController::class, 'getWorks']);
Route::post('/api/planning/works', [PlanningController::class, 'storeWork']);
Route::put('/api/planning/works/{id}', [PlanningController::class, 'updateWork']);
Route::delete('/api/planning/works/{id}', [PlanningController::class, 'deleteWork']);
Route::post('/api/planning/works/{id}/image', [PlanningController::class, 'uploadImage']);
Route::get('/api/planning/sectors', [PlanningController::class, 'getSectors']);
