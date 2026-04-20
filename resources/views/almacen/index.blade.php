@extends('layouts.admin')

@section('title', 'Almacén - Inventario')
@section('page_title', 'Almacén Municipal')
@section('page_subtitle', 'Gestión de inventarios, suministros y activos fijos')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* =============================================
       DESIGN TOKENS (complementan al layout)
    ============================================= */
    :root {
        --bg-card: rgba(22, 30, 50, 0.75);
        --bg-card-hover: rgba(30, 41, 70, 0.85);
        --bg-input: rgba(8, 12, 30, 0.6);
        --border-active: rgba(99,102,241,0.5);
        --text-primary: #f0f4ff;
        --text-secondary: #8492a6;
        --text-muted: #4a5568;
        --radius-card: 16px;
        --radius-btn: 10px;
        --shadow-card: 0 8px 32px rgba(0,0,0,0.4);
        --shadow-glow: 0 0 30px rgba(99,102,241,0.15);
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
    }

    /* =============================================
       INNER NAV (tabs de secciones) — cuadrícula 2 filas
    ============================================= */
    .inv-tabs {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 6px;
        margin-bottom: 20px;
    }
    .inv-tab {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 8px 4px;
        border-radius: 12px;
        cursor: pointer;
        color: var(--text-dim);
        font-size: 0.72rem;
        font-weight: 500;
        transition: all 0.2s;
        white-space: nowrap;
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.03);
        position: relative;
        user-select: none;
        text-align: center;
        line-height: 1.2;
    }
    .inv-tab .tab-icon {
        font-size: 1rem;
        line-height: 1;
        display: block;
    }
    .inv-tab .tab-label {
        display: block;
        font-size: 0.62rem;
        letter-spacing: 0.01em;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .inv-tab .tab-badge {
        position: absolute;
        top: 5px; right: 7px;
        background: var(--primary);
        color: white;
        padding: 0px 5px;
        border-radius: 8px;
        font-size: 0.62rem;
        font-weight: 700;
        line-height: 1.5;
    }
    .inv-tab .tab-badge-alert {
        background: #ef4444;
    }
    .inv-tab:hover {
        color: white;
        background: rgba(99,102,241,0.1);
        border-color: rgba(99,102,241,0.3);
        transform: translateY(-1px);
    }
    .inv-tab.active {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        border-color: rgba(99,102,241,0.5);
        box-shadow: 0 4px 14px rgba(99,102,241,0.3);
    }
    .inv-tab.active .tab-label { color: rgba(255,255,255,0.85); }

    /* =============================================
       PAGES
    ============================================= */
    .inv-page { display: none; }
    .inv-page.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    /* =============================================
       BUTTONS
    ============================================= */
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 15px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        box-shadow: 0 4px 15px rgba(99,102,241,0.3);
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
    .btn-secondary { background: rgba(255,255,255,0.06); color: var(--text-primary); border: 1px solid var(--border); }
    .btn-secondary:hover { background: rgba(255,255,255,0.1); }
    .btn-danger { background: rgba(239,68,68,0.12); color: #fca5a5; border: 1px solid rgba(239,68,68,0.2); }
    .btn-danger:hover { background: rgba(239,68,68,0.2); }
    .btn-success { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.2); }
    .btn-sm { padding: 5px 10px; font-size: 0.75rem; }

    /* =============================================
       CARDS
    ============================================= */
    .inv-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        backdrop-filter: blur(10px);
    }
    .inv-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .inv-card-header h3 { font-size: 0.95rem; font-weight: 600; }
    .inv-card-body { padding: 22px; }

    /* =============================================
       STATS GRID
    ============================================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-glow); border-color: var(--border-active); }
    .stat-card::before {
        content: ''; position: absolute; top: 0; right: 0;
        width: 80px; height: 80px; border-radius: 50%;
        opacity: 0.08; transform: translate(20px, -20px);
    }
    .stat-card.purple::before { background: var(--primary); }
    .stat-card.green::before { background: var(--success); }
    .stat-card.orange::before { background: var(--warning); }
    .stat-card.red::before { background: var(--danger); }
    .stat-card.blue::before { background: var(--info); }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 14px;
    }
    .stat-card.purple .stat-icon { background: rgba(99,102,241,0.12); color: #818cf8; }
    .stat-card.green .stat-icon  { background: rgba(16,185,129,0.12); color: #34d399; }
    .stat-card.orange .stat-icon { background: rgba(245,158,11,0.12); color: #fbbf24; }
    .stat-card.red .stat-icon    { background: rgba(239,68,68,0.12); color: #f87171; }
    .stat-card.blue .stat-icon   { background: rgba(59,130,246,0.12); color: #60a5fa; }
    .stat-value { font-size: 2rem; font-weight: 800; margin-bottom: 4px; }
    .stat-label { font-size: 0.82rem; color: var(--text-dim); }

    /* =============================================
       GRID LAYOUTS
    ============================================= */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 1100px) { .grid-2 { grid-template-columns: 1fr; } }

    /* =============================================
       TABLE
    ============================================= */
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    thead th {
        padding: 8px 12px;
        text-align: left;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-dim);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid rgba(255,255,255,0.03); transition: background 0.15s ease; }
    tbody tr:hover { background: rgba(99,102,241,0.04); }
    tbody td { padding: 8px 12px; color: var(--text-primary); vertical-align: middle; line-height: 1.35; }
    tbody td small { color: var(--text-dim); font-size: 0.73rem; }

    /* =============================================
       BADGES
    ============================================= */
    .badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 20px;
        font-size: 0.72rem; font-weight: 600;
    }
    .badge-nuevo      { background: rgba(16,185,129,0.12); color: #34d399; }
    .badge-bueno      { background: rgba(99,102,241,0.12); color: #a5b4fc; }
    .badge-regular    { background: rgba(245,158,11,0.12); color: #fcd34d; }
    .badge-malo       { background: rgba(239,68,68,0.12); color: #fca5a5; }
    .badge-baja       { background: rgba(100,116,139,0.15); color: #94a3b8; }
    .badge-asset      { background: rgba(99,102,241,0.12); color: #a5b4fc; }
    .badge-consumable { background: rgba(16,185,129,0.12); color: #6ee7b7; }
    .badge-returned   { background: rgba(100,116,139,0.15); color: #94a3b8; }
    .badge-active-delivery { background: rgba(16,185,129,0.12); color: #34d399; }

    /* =============================================
       SEARCH / CONTROLS
    ============================================= */
    .search-wrap { position: relative; }
    .search-wrap input {
        width: 100%; padding: 10px 14px 10px 40px;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-primary);
        font-size: 0.875rem; font-family: 'Inter', sans-serif;
        outline: none; transition: border-color 0.2s ease;
    }
    .search-wrap input:focus { border-color: var(--border-active); }
    .search-wrap input::placeholder { color: var(--text-dim); }
    .search-icon {
        position: absolute; left: 13px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-dim); font-size: 15px;
    }
    .controls-row {
        display: flex; gap: 12px; align-items: center;
        margin-bottom: 18px; flex-wrap: wrap;
    }
    .controls-row .search-wrap { flex: 1; min-width: 200px; }
    .form-select {
        padding: 10px 14px;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 9px;
        color: var(--text-primary);
        font-size: 0.875rem; font-family: 'Inter', sans-serif;
        outline: none; transition: border-color 0.2s;
    }
    .form-select:focus { border-color: var(--border-active); }
    .form-select option { background: #1e293b; }

    /* =============================================
       MODAL
    ============================================= */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.65);
        backdrop-filter: blur(6px);
        z-index: 2000;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .inv-modal {
        background: #111827;
        border: 1px solid var(--border);
        border-radius: 18px;
        width: 100%; max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px rgba(0,0,0,0.6);
        animation: modalIn 0.25s ease;
    }
    @keyframes modalIn { from { opacity:0; transform:scale(0.95) translateY(10px); } to { opacity:1; transform:scale(1); } }
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-header h3 { font-size: 1.05rem; font-weight: 600; }
    .modal-close {
        background: transparent; border: none; color: var(--text-dim);
        font-size: 22px; cursor: pointer; line-height: 1; padding: 4px;
    }
    .modal-body { padding: 24px; }
    .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }

    /* =============================================
       FORM
    ============================================= */
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--text-dim); margin-bottom: 6px; }
    .form-control {
        width: 100%; padding: 10px 14px;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 9px;
        color: var(--text-primary);
        font-size: 0.875rem; font-family: 'Inter', sans-serif;
        outline: none; transition: border-color 0.2s;
    }
    .form-control:focus { border-color: var(--border-active); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* =============================================
       TOAST
    ============================================= */
    #toast-container { position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
    .toast {
        padding: 12px 18px; border-radius: 10px;
        font-size: 0.82rem; font-weight: 500; color: white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        animation: slideUp 0.3s ease;
        display: flex; align-items: center; gap: 10px;
    }
    @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .toast.success { background: linear-gradient(135deg, #059669, #10b981); }
    .toast.error   { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .toast.info    { background: linear-gradient(135deg, #2563eb, #3b82f6); }

    /* =============================================
       LOADER / EMPTY STATE
    ============================================= */
    .loader-wrap { display: flex; justify-content: center; padding: 60px 0; }
    .spinner { width: 36px; height: 36px; border: 3px solid rgba(255,255,255,0.1); border-left-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state-icon { font-size: 52px; margin-bottom: 16px; opacity: 0.4; }
    .empty-state p { color: var(--text-dim); font-size: 0.9rem; }

    /* =============================================
       TABLE EXTRAS
    ============================================= */
    .action-group { display: flex; gap: 6px; }
    .code-pill {
        background: rgba(99,102,241,0.1);
        color: #818cf8;
        padding: 2px 8px; border-radius: 6px;
        font-family: monospace; font-size: 0.82rem;
    }
    .low-stock-row { background: rgba(239,68,68,0.05) !important; }
    .stock-bar-wrap { width: 80px; height: 6px; background: rgba(255,255,255,0.08); border-radius: 4px; display:inline-block; vertical-align:middle; }
    .stock-bar { height: 100%; border-radius: 4px; transition: width 0.4s; }
    .stock-ok { background: var(--success); }
    .stock-warn { background: var(--warning); }
    .stock-crit { background: var(--danger); }

    /* Alert banner */
    .alert-banner {
        display: flex; align-items: center; gap: 12px;
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.25);
        border-radius: 12px; padding: 14px 18px;
        margin-bottom: 20px;
        animation: pulse-border 2s infinite;
    }
    @keyframes pulse-border { 0%,100%{border-color:rgba(239,68,68,0.25)} 50%{border-color:rgba(239,68,68,0.6)} }
    .alert-banner .alert-icon { font-size: 22px; flex-shrink:0; }
    .alert-banner .alert-text strong { display:block; color:#fca5a5; font-size:0.9rem; }
    .alert-banner .alert-text span { color:var(--text-dim); font-size:0.8rem; }

    /* Chart container */
    .chart-container { position: relative; height: 250px; }

    /* Signature canvas */
    .signature-wrap {
        border: 1px dashed rgba(255,255,255,0.2);
        border-radius: 10px;
        background: rgba(255,255,255,0.03);
        overflow: hidden; position: relative;
    }
    #sig-canvas { display:block; cursor:crosshair; touch-action:none; }
    .sig-clear { position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.5); border:none; color:white; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer; }

    /* Print */
    @media print {
        .sidebar, .top-bar, .controls-row, .modal-overlay, #toast-container, .no-print, .inv-tabs { display:none !important; }
        .main-wrapper { margin-left:0; }
        body { background: white; color: black; }
        .acta-print { padding: 40px; }
    }

    /* =============================================
       ACTA PRINT — forzar colores oscuros
       (el modal usa fondo blanco pero hereda vars del tema oscuro)
    ============================================= */
    #acta-print-modal .inv-modal {
        background: #ffffff !important;
        color: #111111 !important;
    }
    #acta-print-body {
        color: #111111 !important;
        background: #ffffff !important;
    }
    /* Neutralizar las variables CSS del tema oscuro dentro del acta */
    #acta-print-body table td,
    #acta-print-body table th,
    #acta-print-body p,
    #acta-print-body span:not([style*="background"]),
    #acta-print-body div:not([style*="background"]) {
        color: inherit;
    }
    /* Asegurar que celdas sin color explícito sean visibles */
    #acta-print-body table td {
        color: #1f2937;
    }

    /* =============================================
       DELIVERY ITEM ROWS (multi-ítem modal)
    ============================================= */
    .delivery-item-row {
        display: grid;
        grid-template-columns: 130px 1fr auto auto;
        gap: 8px;
        align-items: center;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 8px 10px;
    }
    .delivery-item-row select,
    .delivery-item-row input[type="number"] {
        background: var(--bg-input);
        border: 1px solid var(--border);
        color: var(--text-primary);
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.8rem;
        width: 100%;
    }
    .delivery-item-row select:focus,
    .delivery-item-row input:focus { outline: none; border-color: var(--primary); }
    .delivery-item-qty { width: 72px !important; }
    .delivery-item-remove {
        width: 28px; height: 28px; border-radius: 7px;
        background: rgba(239,68,68,0.12); border: none;
        color: #f87171; cursor: pointer; font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: background 0.15s;
    }
    .delivery-item-remove:hover { background: rgba(239,68,68,0.25); }
    /* Estilos para impresión de reportes */
    @media print {
        body * { visibility: hidden !important; background: white !important; color: black !important; }
        #report-print-modal, #report-print-modal * { visibility: visible !important; }
        #report-print-modal { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; margin: 0 !important; padding: 0 !important; background: white !important; z-index: 9999; }
        .inv-modal { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; position: static !important; }
        .modal-header, .btn, .modal-footer, .sidebar, .header-top { display: none !important; }
        #report-print-body { padding: 0 !important; display: block !important; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000 !important; padding: 8px; text-align: left; font-size: 10pt; background: #fff !important; }
        th { background: #f0f0f0 !important; font-weight: bold; }
        .print-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .print-footer { margin-top: 30px; display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 10px; font-size: 8pt; }
    }
</style>
@endsection

@section('content')

{{-- TABS DE NAVEGACIÓN INTERNA — cuadrícula 2×5 sin scroll --}}
<div class="inv-tabs" id="almacenTabs">
    <!-- Fila 1: Gestión de Stock y Catálogo -->
    <div class="inv-tab active" onclick="showPage('dashboard',this)" id="tab-dashboard">
        <span class="tab-icon">📊</span>
        <span class="tab-label">Resumen</span>
    </div>
    <div class="inv-tab" onclick="showPage('items',this)" id="tab-items">
        <span class="tab-icon">🗂️</span>
        <span class="tab-label">Catálogo</span>
    </div>
    <div class="inv-tab" onclick="showPage('categories',this)" id="tab-categories">
        <span class="tab-icon">📚</span>
        <span class="tab-label">Categorías</span>
    </div>
    <div class="inv-tab" onclick="showPage('providers',this)" id="tab-providers">
        <span class="tab-icon">🏭</span>
        <span class="tab-label">Proveedores</span>
    </div>
    <div class="inv-tab" onclick="showPage('entries',this)" id="tab-entries">
        <span class="tab-icon">📥</span>
        <span class="tab-label">Entradas</span>
    </div>
    <div class="inv-tab" onclick="showPage('alerts',this)" id="tab-alerts">
        <span class="tab-badge tab-badge-alert" id="badge-alerts" style="display:none">0</span>
        <span class="tab-icon">🚨</span>
        <span class="tab-label">Alertas</span>
    </div>
    <div class="inv-tab" onclick="showPage('supply-requests',this)" id="tab-supply-requests">
        <span class="tab-badge" id="badge-supply" style="display:none">0</span>
        <span class="tab-icon">📋</span>
        <span class="tab-label">Solicitudes</span>
    </div>
    <div class="inv-tab" onclick="showPage('reports',this)" id="tab-reports">
        <span class="tab-icon">💹</span>
        <span class="tab-label">Reportes</span>
    </div>

    <!-- Fila 2: Ciclo de Vida de Activos Fijos -->
    <div class="inv-tab" onclick="showPage('assets',this)" id="tab-assets">
        <span class="tab-badge" id="badge-assets" style="display:none">0</span>
        <span class="tab-icon">📦</span>
        <span class="tab-label">Activos Fijos</span>
    </div>
    <div class="inv-tab" onclick="showPage('assignments',this)" id="tab-assignments">
        <span class="tab-icon">👤</span>
        <span class="tab-label">Asignaciones</span>
    </div>
    <div class="inv-tab" onclick="showPage('offices',this)" id="tab-offices">
        <span class="tab-icon">🏢</span>
        <span class="tab-label">Secretarías</span>
    </div>
    <div class="inv-tab" onclick="showPage('officials',this)" id="tab-officials">
        <span class="tab-icon">👨‍💼</span>
        <span class="tab-label">Funcionarios</span>
    </div>
    <div class="inv-tab" onclick="showPage('deliveries',this)" id="tab-deliveries">
        <span class="tab-icon">📄</span>
        <span class="tab-label">Actas Entrega</span>
    </div>
    <div class="inv-tab" onclick="showPage('transfers',this)" id="tab-transfers">
        <span class="tab-icon">🔄</span>
        <span class="tab-label">Traslados</span>
    </div>
    <div class="inv-tab" onclick="showPage('maintenances',this)" id="tab-maintenances">
        <span class="tab-icon">🔧</span>
        <span class="tab-label">Mantenimiento</span>
    </div>
    <div class="inv-tab" onclick="showPage('disposals',this)" id="tab-disposals">
        <span class="tab-icon">🗑️</span>
        <span class="tab-label">Bajas</span>
    </div>
</div>

{{-- ============================================================
     DASHBOARD PAGE
============================================================ --}}
<section class="inv-page active" id="page-dashboard">
    <div class="stats-grid" id="stats-grid">
        <div class="stat-card blue" onclick="showPage('items',document.getElementById('tab-items'))" style="cursor:pointer">
            <div class="stat-icon">📚</div>
            <div class="stat-value" id="s-items">—</div>
            <div class="stat-label">Artículos en Catálogo</div>
        </div>
        <div class="stat-card purple" onclick="showPage('assets',document.getElementById('tab-assets'))" style="cursor:pointer">
            <div class="stat-icon">📦</div>
            <div class="stat-value" id="s-total">—</div>
            <div class="stat-label">Activos Fijos Activos</div>
        </div>
        <div class="stat-card orange" onclick="showPage('alerts',document.getElementById('tab-alerts'))" style="cursor:pointer">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value" id="s-low-stock">—</div>
            <div class="stat-label">Stock Crítico</div>
        </div>
        <div class="stat-card green" onclick="showPage('supply-requests',document.getElementById('tab-supply-requests'))" style="cursor:pointer">
            <div class="stat-icon">📋</div>
            <div class="stat-value" id="s-pending-req">—</div>
            <div class="stat-label">Solicitudes Pendientes</div>
        </div>
        <div class="stat-card red" onclick="showPage('maintenances',document.getElementById('tab-maintenances'))" style="cursor:pointer">
            <div class="stat-icon">🔧</div>
            <div class="stat-value" id="s-pending-maint">—</div>
            <div class="stat-label">Mantenimientos Pendientes</div>
        </div>
    </div>
    <div class="grid-2">
        <div class="inv-card">
            <div class="inv-card-header"><h3>📊 Distribución por Estado</h3></div>
            <div class="inv-card-body"><div class="chart-container"><canvas id="chartStatus"></canvas></div></div>
        </div>
        <div class="inv-card">
            <div class="inv-card-header"><h3>📋 Activos por Categoría</h3></div>
            <div class="inv-card-body"><div class="chart-container"><canvas id="chartCategory"></canvas></div></div>
        </div>
    </div>
    <div style="margin-top:20px" class="inv-card">
        <div class="inv-card-header">
            <h3>🕐 Últimos Activos Registrados</h3>
            <button class="btn btn-secondary btn-sm" onclick="showPage('assets',document.getElementById('tab-assets'))">Ver todos</button>
        </div>
        <div class="inv-card-body" style="padding:0">
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Código</th><th>Artículo</th><th>Proveedor</th><th>Estado</th></tr></thead>
                    <tbody id="dashboard-recent"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ASSETS PAGE
============================================================ --}}
<section class="inv-page" id="page-assets">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por código, artículo o serie..." id="assets-search" oninput="filterAssets(this.value)">
        </div>
        <select class="form-select" id="assets-status-filter" onchange="filterAssets(document.getElementById('assets-search').value)">
            <option value="">Todos los estados</option>
            <option value="nuevo">Nuevo</option>
            <option value="bueno">Bueno</option>
            <option value="regular">Regular</option>
            <option value="malo">Malo</option>
            <option value="baja">De Baja</option>
        </select>
        <button class="btn btn-primary" onclick="openAssetModal()">+ Nuevo Activo</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Código</th><th>Artículo / Categoría</th><th>Serie</th><th>Proveedor</th><th>Precio</th><th>Estado</th><th>Custodio</th><th>Acciones</th></tr>
                </thead>
                <tbody id="assets-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="assets-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="assets-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No se encontraron activos fijos</p></div>
    </div>
</section>

{{-- ============================================================
     ITEMS PAGE
============================================================ --}}
<section class="inv-page" id="page-items">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar artículo..." oninput="filterItems(this.value)">
        </div>
        <select class="form-select" onchange="filterItemsByType(this.value)">
            <option value="">Todos los tipos</option>
            <option value="1">Activos Fijos</option>
            <option value="0">Consumibles</option>
        </select>
        <button class="btn btn-primary" onclick="openItemModal()">+ Nuevo Artículo</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Nombre del Artículo</th><th>Categoría</th><th>Tipo</th><th>Stock</th><th>Stock Mín.</th><th>Acciones</th></tr></thead>
                <tbody id="items-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="items-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="items-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No se encontraron artículos</p></div>
    </div>
</section>

{{-- ============================================================
     CATEGORIES PAGE
============================================================ --}}
<section class="inv-page" id="page-categories">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar categoría..." oninput="filterCategories(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openCategoryModal()">+ Nueva Categoría</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Código</th><th>Nombre</th><th>N° Artículos</th><th>Acciones</th></tr></thead>
                <tbody id="categories-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="categories-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="categories-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No se encontraron categorías</p></div>
    </div>
</section>

{{-- ============================================================
     PROVIDERS PAGE
============================================================ --}}
<section class="inv-page" id="page-providers">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por NIT o razón social..." oninput="filterProviders(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openProviderModal()">+ Nuevo Proveedor</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>NIT</th><th>Razón Social</th><th>Contacto</th><th>Acciones</th></tr></thead>
                <tbody id="providers-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="providers-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="providers-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No se encontraron proveedores</p></div>
    </div>
</section>

{{-- ============================================================
     ASSIGNMENTS PAGE
============================================================ --}}
<section class="inv-page" id="page-assignments">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por custodio o código..." oninput="filterAssignments(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openAssignModal()">+ Nueva Asignación</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Activo</th><th>Custodio</th><th>Secretaría</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="assignments-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="assignments-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="assignments-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No hay asignaciones registradas</p></div>
    </div>
</section>

{{-- ============================================================
     OFFICES PAGE
============================================================ --}}
<section class="inv-page" id="page-offices">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar secretaría..." oninput="filterOfficesTable(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openOfficeModal()">+ Nueva Secretaría</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Nombre de la Secretaría</th><th>Asignaciones</th><th>Acciones</th></tr></thead>
                <tbody id="offices-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="offices-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="offices-empty" style="display:none"><div class="empty-state-icon">📭</div><p>No hay secretarías registradas</p></div>
    </div>
</section>

{{-- ============================================================
     OFFICIALS PAGE
============================================================ --}}
<section class="inv-page" id="page-officials">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por nombre, cédula o cargo..." oninput="filterOfficials(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openOfficialModal()">+ Nuevo Funcionario</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Documento</th><th>Nombre Completo</th><th>Cargo</th><th>Secretaría</th><th>Contacto</th><th>Entregas</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="officials-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="officials-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="officials-empty" style="display:none"><div class="empty-state-icon">👤</div><p>No hay funcionarios registrados</p></div>
    </div>
</section>

{{-- ============================================================
     DELIVERIES PAGE
============================================================ --}}
<section class="inv-page" id="page-deliveries">
    <div id="deliveries-alert-banner"></div>
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por acta, funcionario o activo..." oninput="filterDeliveries(this.value)">
        </div>
        <select class="form-select" onchange="filterDeliveriesByStatus(this.value)">
            <option value="">Todas las actas</option>
            <option value="active">Vigentes</option>
            <option value="returned">Devueltas</option>
        </select>
        <button class="btn btn-primary" onclick="openDeliveryModal()">+ Nueva Acta de Entrega</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>N° Acta</th><th>Tipo</th><th>Ítem Entregado</th><th>Funcionario Receptor</th><th>Secretaría</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="deliveries-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="deliveries-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="deliveries-empty" style="display:none"><div class="empty-state-icon">📋</div><p>No hay actas de entrega registradas</p></div>
    </div>
</section>

{{-- ============================================================
     ALERTS PAGE
============================================================ --}}
<section class="inv-page" id="page-alerts">
    <div class="inv-card">
        <div class="inv-card-header"><h3>🚨 Suministros con Stock Bajo o Agotado</h3><button class="btn btn-secondary btn-sm" onclick="loadAlerts()">↻ Actualizar</button></div>
        <div class="inv-card-body" style="padding:0">
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Artículo</th><th>Categoría</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Nivel</th><th>Estado</th></tr></thead>
                    <tbody id="alerts-table-body"></tbody>
                </table>
            </div>
            <div class="loader-wrap" id="alerts-loader"><div class="spinner"></div></div>
            <div class="empty-state" id="alerts-empty" style="display:none"><div class="empty-state-icon">✅</div><p>¡Todo en orden! No hay suministros en nivel crítico.</p></div>
        </div>
    </div>
</section>

{{-- ============================================================
     ENTRADAS DE INVENTARIO
============================================================ --}}
<section class="inv-page" id="page-entries">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por N° entrada, proveedor..." oninput="filterEntries(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openEntryModal()">+ Nueva Entrada</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>N° Entrada</th><th>Fecha</th><th>Proveedor</th><th>Factura</th><th>Recibido Por</th><th>Ítems</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="entries-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="entries-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="entries-empty" style="display:none"><div class="empty-state-icon">📥</div><p>No hay entradas de inventario registradas</p></div>
    </div>
</section>

{{-- ============================================================
     BAJAS DE ACTIVOS
============================================================ --}}
<section class="inv-page" id="page-disposals">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por N° baja o activo..." oninput="filterDisposals(this.value)">
        </div>
        <button class="btn btn-danger" onclick="openDisposalModal()">+ Registrar Baja</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>N° Baja</th><th>Activo</th><th>Código</th><th>Motivo</th><th>Fecha</th><th>Autorizado Por</th><th>Resolución</th><th>Acciones</th></tr></thead>
                <tbody id="disposals-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="disposals-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="disposals-empty" style="display:none"><div class="empty-state-icon">🗑️</div><p>No hay bajas de activos registradas</p></div>
    </div>
</section>

{{-- ============================================================
     TRASLADOS ENTRE DEPENDENCIAS
============================================================ --}}
<section class="inv-page" id="page-transfers">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar por N° traslado o activo..." oninput="filterTransfers(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openTransferModal()">+ Nuevo Traslado</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>N° Traslado</th><th>Activo</th><th>De Secretaría</th><th>A Secretaría</th><th>Transferido Por</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="transfers-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="transfers-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="transfers-empty" style="display:none"><div class="empty-state-icon">🔄</div><p>No hay traslados registrados</p></div>
    </div>
</section>

{{-- ============================================================
     MANTENIMIENTO DE ACTIVOS
============================================================ --}}
<section class="inv-page" id="page-maintenances">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar activo o técnico..." oninput="filterMaintenances(this.value)">
        </div>
        <select class="form-select" onchange="filterMaintenancesByStatus(this.value)">
            <option value="">Todos los estados</option>
            <option value="scheduled">Programado</option>
            <option value="in_progress">En Proceso</option>
            <option value="completed">Completado</option>
            <option value="cancelled">Cancelado</option>
        </select>
        <button class="btn btn-primary" onclick="openMaintenanceModal()">+ Registrar Mantenimiento</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Activo</th><th>Tipo</th><th>Fecha</th><th>Próximo</th><th>Técnico</th><th>Costo</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="maintenances-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="maintenances-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="maintenances-empty" style="display:none"><div class="empty-state-icon">🔧</div><p>No hay registros de mantenimiento</p></div>
    </div>
</section>

{{-- ============================================================
     SOLICITUDES DE SUMINISTROS
============================================================ --}}
<section class="inv-page" id="page-supply-requests">
    <div class="controls-row">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Buscar solicitud o dependencia..." oninput="filterSupplyRequests(this.value)">
        </div>
        <select class="form-select" onchange="filterSupplyByStatus(this.value)">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="approved">Aprobada</option>
            <option value="dispatched">Despachada</option>
            <option value="rejected">Rechazada</option>
        </select>
        <button class="btn btn-primary" onclick="openSupplyRequestModal()">+ Nueva Solicitud</button>
    </div>
    <div class="inv-card">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>N° Solicitud</th><th>Dependencia</th><th>Solicitado Por</th><th>Fecha</th><th>Requerido Para</th><th>Ítems</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="supply-requests-table-body"></tbody>
            </table>
        </div>
        <div class="loader-wrap" id="supply-requests-loader"><div class="spinner"></div></div>
        <div class="empty-state" id="supply-requests-empty" style="display:none"><div class="empty-state-icon">📋</div><p>No hay solicitudes de suministros</p></div>
    </div>
</section>

{{-- ============================================================
     REPORTES Y ANÁLISIS
============================================================ --}}
<section class="inv-page" id="page-reports">
    <div class="stats-grid">
        <div class="stat-card blue" style="cursor:pointer" onclick="generateReport('inventory-stock')">
            <div class="stat-icon">📋</div>
            <div class="stat-label">Stock de Inventario</div>
            <p style="font-size:0.75rem; color:var(--text-dim)">Catalogo completo con saldos</p>
        </div>
        <div class="stat-card purple" style="cursor:pointer" onclick="generateReport('assets-by-office')">
            <div class="stat-icon">🏢</div>
            <div class="stat-label">Activos por Dependencia</div>
            <p style="font-size:0.75rem; color:var(--text-dim)">Ubicación y custodios actuales</p>
        </div>
        <div class="stat-card orange" style="cursor:pointer" onclick="generateReport('movements')">
            <div class="stat-icon">🔄</div>
            <div class="stat-label">Historial de Movimientos</div>
            <p style="font-size:0.75rem; color:var(--text-dim)">Entradas, salidas y traslados</p>
        </div>
    </div>

    <div class="inv-card" style="margin-top:20px">
        <div class="report-filters" style="display:flex;gap:15px;padding:15px;align-items:center;border-bottom:1px solid var(--border)">
            <div class="form-group" style="margin:0">
                <label style="font-size:0.75rem">Fecha Inicial</label>
                <input type="date" id="report-start-date" class="form-control" style="padding:6px 12px">
            </div>
            <div class="form-group" style="margin:0">
                <label style="font-size:0.75rem">Fecha Final</label>
                <input type="date" id="report-end-date" class="form-control" style="padding:6px 12px">
            </div>
            <p style="color:var(--text-dim);font-size:0.75rem;flex:1">Filtre por fecha para el reporte de movimientos</p>
        </div>
        
        <div id="report-preview-container" style="min-height:400px; display:flex; align-items:center; justify-content:center; flex-direction:column; color:var(--text-dim)">
            <div style="font-size:3rem">📑</div>
            <p>Seleccione un tipo de reporte para previsualizar</p>
        </div>
    </div>
</section>

<div class="modal-overlay" id="report-print-modal">
    <div class="inv-modal" style="max-width:1100px; padding:0; background:#fff; color:#000">
        <div style="background:#f8fafc; padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
            <h3 style="margin:0; color:#1e293b; font-size:1.1rem">Vista Previa de Impresión</h3>
            <div style="display:flex; gap:10px">
                <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimir PDF / Papel</button>
                <button class="btn btn-secondary btn-sm" onclick="closeModal('report-print-modal')">Cerrar</button>
            </div>
        </div>
        <div id="report-print-body" style="padding:40px; overflow-y:auto; max-height:80vh">
        </div>
    </div>
</div>


{{-- ============================================================
     MÓDULOS MODALS
============================================================ --}}

<!-- Entry Modal -->
<div class="modal-overlay" id="entry-modal">
    <div class="inv-modal" style="max-width:680px">
        <div class="modal-header"><h3>📥 Nueva Entrada de Inventario</h3><button class="modal-close" onclick="closeModal('entry-modal')">×</button></div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label>Proveedor</label><select id="entry-supplier" class="form-control"><option value="">Sin proveedor</option></select></div>
                <div class="form-group"><label>N° Factura / Remisión</label><input type="text" id="entry-invoice" class="form-control" placeholder="FAC-001 / REM-2026-001"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Fecha de Entrada *</label><input type="date" id="entry-date" class="form-control"></div>
                <div class="form-group"><label>Recibido Por *</label><input type="text" id="entry-received-by" class="form-control" placeholder="Nombre de quien recibe"></div>
            </div>
            <div class="form-group"><label>Observaciones</label><input type="text" id="entry-notes" class="form-control" placeholder="Condiciones de llegada, estado, etc."></div>

            <div style="margin-top:12px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <label style="margin:0;font-weight:600;font-size:0.85rem">📦 Artículos Recibidos *</label>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addEntryItemRow()">+ Agregar artículo</button>
                </div>
                <div id="entry-items-list" style="display:flex;flex-direction:column;gap:8px"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('entry-modal')">Cancelar</button>
            <button class="btn btn-primary" id="entry-save-btn" onclick="saveEntry()">📥 Registrar Entrada</button>
        </div>
    </div>
</div>

<!-- Disposal Modal -->
<div class="modal-overlay" id="disposal-modal">
    <div class="inv-modal" style="max-width:560px">
        <div class="modal-header"><h3>🗑️ Acta de Baja de Activo</h3><button class="modal-close" onclick="closeModal('disposal-modal')">×</button></div>
        <div class="modal-body">
            <div class="form-group"><label>Activo a dar de Baja *</label><select id="disposal-asset" class="form-control"><option value="">Seleccionar activo...</option></select></div>
            <div class="form-row">
                <div class="form-group"><label>Motivo de Baja *</label>
                    <select id="disposal-reason" class="form-control">
                        <option value="damage">Daño irreparable</option>
                        <option value="loss">Pérdida</option>
                        <option value="obsolescence">Obsolescencia</option>
                        <option value="theft">Hurto/Robo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div class="form-group"><label>Fecha de Baja *</label><input type="date" id="disposal-date" class="form-control"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Autorizado Por *</label><input type="text" id="disposal-authorized" class="form-control" placeholder="Nombre del autorizador"></div>
                <div class="form-group"><label>Tramitado Por *</label><input type="text" id="disposal-processed" class="form-control" placeholder="Nombre del funcionario que tramita"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>N° Resolución</label><input type="text" id="disposal-resolution" class="form-control" placeholder="RES-2026-001 (opcional)"></div>
            </div>
            <div class="form-group"><label>Descripción / Justificación</label><textarea id="disposal-description" class="form-control" rows="3" placeholder="Detalle el estado del activo y motivo de la baja..."></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('disposal-modal')">Cancelar</button>
            <button class="btn btn-danger" id="disposal-save-btn" onclick="saveDisposal()">🗑️ Registrar Baja</button>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal-overlay" id="transfer-modal">
    <div class="inv-modal" style="max-width:560px">
        <div class="modal-header"><h3>🔄 Nuevo Traslado de Activo</h3><button class="modal-close" onclick="closeModal('transfer-modal')">×</button></div>
        <div class="modal-body">
            <div class="form-group"><label>Activo a Trasladar *</label><select id="transfer-asset" class="form-control"><option value="">Seleccionar activo...</option></select></div>
            <div class="form-row">
                <div class="form-group"><label>De Secretaría</label><select id="transfer-from" class="form-control"><option value="">Origen (actual)...</option></select></div>
                <div class="form-group"><label>A Secretaría *</label><select id="transfer-to" class="form-control"><option value="">Destino...</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Transferido Por *</label><input type="text" id="transfer-by" class="form-control" placeholder="Nombre de quien traslada"></div>
                <div class="form-group"><label>Recibido Por</label><input type="text" id="transfer-received" class="form-control" placeholder="Nombre de quien recibe"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Fecha *</label><input type="date" id="transfer-date" class="form-control"></div>
            </div>
            <div class="form-group"><label>Observaciones</label><input type="text" id="transfer-notes" class="form-control" placeholder="Estado del activo, motivo, etc."></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('transfer-modal')">Cancelar</button>
            <button class="btn btn-primary" id="transfer-save-btn" onclick="saveTransfer()">🔄 Registrar Traslado</button>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal-overlay" id="maintenance-modal">
    <div class="inv-modal" style="max-width:560px">
        <div class="modal-header"><h3>🔧 Registro de Mantenimiento</h3><button class="modal-close" onclick="closeModal('maintenance-modal')">×</button></div>
        <div class="modal-body">
            <div class="form-group"><label>Activo *</label><select id="maintenance-asset" class="form-control"><option value="">Seleccionar activo...</option></select></div>
            <div class="form-row">
                <div class="form-group"><label>Tipo *</label>
                    <select id="maintenance-type" class="form-control">
                        <option value="preventive">Preventivo</option>
                        <option value="corrective">Correctivo</option>
                        <option value="upgrade">Mejora / Actualización</option>
                    </select>
                </div>
                <div class="form-group"><label>Estado *</label>
                    <select id="maintenance-status" class="form-control">
                        <option value="completed">Completado</option>
                        <option value="scheduled">Programado</option>
                        <option value="in_progress">En Proceso</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Fecha de Mantenimiento *</label><input type="date" id="maintenance-date" class="form-control"></div>
                <div class="form-group"><label>Próximo Mantenimiento</label><input type="date" id="maintenance-next" class="form-control"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Técnico / Empresa</label><input type="text" id="maintenance-technician" class="form-control" placeholder="Nombre técnico o empresa"></div>
                <div class="form-group"><label>Costo ($)</label><input type="number" id="maintenance-cost" class="form-control" placeholder="0.00" min="0" step="0.01"></div>
            </div>
            <div class="form-group"><label>Descripción / Trabajos Realizados *</label><textarea id="maintenance-description" class="form-control" rows="3" placeholder="Detalle los trabajos realizados o programados..."></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('maintenance-modal')">Cancelar</button>
            <button class="btn btn-primary" id="maintenance-save-btn" onclick="saveMaintenance()">🔧 Guardar</button>
        </div>
    </div>
</div>

<!-- Supply Request Modal -->
<div class="modal-overlay" id="supply-request-modal">
    <div class="inv-modal" style="max-width:640px">
        <div class="modal-header"><h3>📋 Nueva Solicitud de Suministros</h3><button class="modal-close" onclick="closeModal('supply-request-modal')">×</button></div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label>Dependencia Solicitante *</label><select id="sr-office" class="form-control"><option value="">Seleccionar...</option></select></div>
                <div class="form-group"><label>Solicitado Por *</label><input type="text" id="sr-requested-by" class="form-control" placeholder="Nombre del solicitante"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Fecha de Solicitud *</label><input type="date" id="sr-date" class="form-control"></div>
                <div class="form-group"><label>Requerido Para</label><input type="date" id="sr-needed-by" class="form-control"></div>
            </div>
            <div class="form-group"><label>Observaciones</label><input type="text" id="sr-notes" class="form-control" placeholder="Justificación o información adicional"></div>

            <div style="margin-top:12px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <label style="margin:0;font-weight:600;font-size:0.85rem">📦 Artículos Solicitados *</label>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addSRItemRow()">+ Agregar artículo</button>
                </div>
                <div id="sr-items-list" style="display:flex;flex-direction:column;gap:8px"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('supply-request-modal')">Cancelar</button>
            <button class="btn btn-primary" id="sr-save-btn" onclick="saveSupplyRequest()">📋 Enviar Solicitud</button>
        </div>
    </div>
</div>

<!-- Dispatch Modal (aprobar/despachar solicitud) -->
<div class="modal-overlay" id="dispatch-modal">
    <div class="inv-modal" style="max-width:600px">
        <div class="modal-header"><h3 id="dispatch-modal-title">📦 Gestionar Solicitud</h3><button class="modal-close" onclick="closeModal('dispatch-modal')">×</button></div>
        <div class="modal-body">
            <p style="font-size:0.82rem;color:var(--text-dim);margin-bottom:12px">Solicitud: <strong id="dispatch-sr-ref"></strong></p>
            <div id="dispatch-items-area"></div>
            <div class="form-group" id="dispatch-by-wrap" style="display:none">
                <label>Despachado Por</label>
                <input type="text" id="dispatch-by" class="form-control" placeholder="Nombre del funcionario que despacha">
            </div>
            <div class="form-group" id="reject-reason-wrap" style="display:none">
                <label>Motivo de Rechazo</label>
                <textarea id="reject-reason" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('dispatch-modal')">Cancelar</button>
            <button class="btn btn-success" id="dispatch-approve-btn" onclick="processSupplyAction('approve')">✅ Aprobar</button>
            <button class="btn btn-primary" id="dispatch-dispatch-btn" onclick="processSupplyAction('dispatch')">📦 Despachar</button>
            <button class="btn btn-danger" id="dispatch-reject-btn" onclick="processSupplyAction('reject')">❌ Rechazar</button>
        </div>
    </div>
</div>

<!-- Kardex Modal -->
<div class="modal-overlay" id="kardex-modal">
    <div class="inv-modal" style="max-width:750px;background:#ffffff;color:#111111">
        <div class="modal-header" style="background:#ffffff;border-color:#e2e8f0;color:#111111">
            <h3 style="color:#111111">📊 Kardex — <span id="kardex-item-name"></span></h3>
            <button class="modal-close" onclick="closeModal('kardex-modal')" style="color:#888">×</button>
        </div>
        <div id="kardex-body" style="padding:20px;overflow-y:auto;max-height:75vh;background:#fff;color:#111"></div>
    </div>
</div>

{{-- ============================================================
     MODALS (existing)
============================================================ --}}

<!-- Asset Modal -->
<div class="modal-overlay" id="asset-modal">
    <div class="inv-modal">
        <div class="modal-header">
            <h3 id="asset-modal-title">Nuevo Activo Fijo</h3>
            <button class="modal-close" onclick="closeModal('asset-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="asset-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Código de Inventario *</label>
                    <input type="text" id="asset-code" class="form-control" placeholder="INV-2026-XXXX">
                </div>
                <div class="form-group">
                    <label>Número de Serie</label>
                    <input type="text" id="asset-serial" class="form-control" placeholder="SN-XXXXXXXX">
                </div>
            </div>
            <div class="form-group">
                <label>Artículo Base *</label>
                <select id="asset-item" class="form-control"><option value="">Seleccionar artículo...</option></select>
            </div>
            <div class="form-group">
                <label>Proveedor *</label>
                <select id="asset-provider" class="form-control"><option value="">Seleccionar proveedor...</option></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Precio de Compra</label>
                    <input type="number" id="asset-price" class="form-control" placeholder="0.00" step="0.01">
                </div>
                <div class="form-group">
                    <label>Estado *</label>
                    <select id="asset-status" class="form-control">
                        <option value="nuevo">Nuevo</option>
                        <option value="bueno">Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="baja">De Baja</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('asset-modal')">Cancelar</button>
            <button class="btn btn-primary" id="asset-save-btn" onclick="saveAsset()">Guardar Activo</button>
        </div>
    </div>
</div>

<!-- Provider Modal -->
<div class="modal-overlay" id="provider-modal">
    <div class="inv-modal">
        <div class="modal-header">
            <h3 id="provider-modal-title">Nuevo Proveedor</h3>
            <button class="modal-close" onclick="closeModal('provider-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="provider-id">
            <div class="form-group"><label>NIT *</label><input type="text" id="provider-nit" class="form-control" placeholder="900.123.456-7"></div>
            <div class="form-group"><label>Razón Social *</label><input type="text" id="provider-name" class="form-control" placeholder="Nombre de la empresa"></div>
            <div class="form-group"><label>Contacto</label><input type="text" id="provider-contact" class="form-control" placeholder="Nombre o teléfono"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('provider-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveProvider()">Guardar Proveedor</button>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal-overlay" id="assign-modal">
    <div class="inv-modal">
        <div class="modal-header">
            <h3>Nueva Asignación</h3>
            <button class="modal-close" onclick="closeModal('assign-modal')">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group"><label>Activo Fijo *</label><select id="assign-asset" class="form-control"><option value="">Seleccionar activo...</option></select></div>
            <div class="form-group"><label>Secretaría *</label><select id="assign-office" class="form-control"><option value="">Seleccionar secretaría...</option></select></div>
            <div class="form-group"><label>Nombre del Custodio *</label><input type="text" id="assign-custodian" class="form-control" placeholder="Nombre y apellido del funcionario"></div>
            <div class="form-group"><label>Fecha de Asignación *</label><input type="date" id="assign-date" class="form-control"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('assign-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveAssignment()">Registrar Asignación</button>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal-overlay" id="item-modal">
    <div class="inv-modal">
        <div class="modal-header">
            <h3 id="item-modal-title">Nuevo Artículo</h3>
            <button class="modal-close" onclick="closeModal('item-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="item-id">
            <div class="form-group"><label>Nombre del Artículo *</label><input type="text" id="item-name" class="form-control" placeholder="Ej: Computador Portátil Dell"></div>
            <div class="form-group"><label>Categoría *</label><select id="item-category" class="form-control"><option value="">Seleccionar...</option></select></div>
            <div class="form-group">
                <label>Tipo *</label>
                <select id="item-type" class="form-control" onchange="toggleItemStock(this.value)">
                    <option value="1">Activo Fijo (Equipo inventariable)</option>
                    <option value="0">Consumible / Suministro</option>
                </select>
            </div>
            <div id="item-stock-wrap" style="display:none">
                <div class="form-row">
                    <div class="form-group"><label>Stock Actual *</label><input type="number" id="item-stock" class="form-control" value="0" min="0"></div>
                    <div class="form-group"><label>Stock Mínimo *</label><input type="number" id="item-min-stock" class="form-control" value="5" min="1"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('item-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveItem()">Guardar Artículo</button>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="category-modal">
    <div class="inv-modal" style="max-width:420px">
        <div class="modal-header">
            <h3 id="category-modal-title">Nueva Categoría</h3>
            <button class="modal-close" onclick="closeModal('category-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="category-id">
            <div class="form-group"><label>Código Único *</label><input type="text" id="category-code" class="form-control" placeholder="Ej: COMP-001"></div>
            <div class="form-group"><label>Nombre de la Categoría *</label><input type="text" id="category-name" class="form-control" placeholder="Ej: Equipos de Cómputo"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('category-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveCategory()">Guardar Categoría</button>
        </div>
    </div>
</div>

<!-- Office Modal -->
<div class="modal-overlay" id="office-modal">
    <div class="inv-modal" style="max-width:420px">
        <div class="modal-header">
            <h3 id="office-modal-title">Nueva Secretaría</h3>
            <button class="modal-close" onclick="closeModal('office-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="office-id">
            <div class="form-group"><label>Nombre de la Secretaría / Dependencia *</label><input type="text" id="office-name" class="form-control" placeholder="Ej: Secretaría de Hacienda"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('office-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveOffice()">Guardar Secretaría</button>
        </div>
    </div>
</div>

<!-- Official Modal -->
<div class="modal-overlay" id="official-modal">
    <div class="inv-modal">
        <div class="modal-header">
            <h3 id="official-modal-title">Nuevo Funcionario</h3>
            <button class="modal-close" onclick="closeModal('official-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="official-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Doc.</label>
                    <select id="official-doctype" class="form-control">
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="TI">Tarjeta de Identidad</option>
                        <option value="CE">Cédula Extranjería</option>
                        <option value="PS">Pasaporte</option>
                    </select>
                </div>
                <div class="form-group"><label>Número de Documento *</label><input type="text" id="official-doc" class="form-control" placeholder="12345678"></div>
            </div>
            <div class="form-group"><label>Nombre Completo *</label><input type="text" id="official-name" class="form-control" placeholder="Nombre y apellidos"></div>
            <div class="form-group"><label>Cargo *</label><input type="text" id="official-position" class="form-control" placeholder="Ej: Secretario de Hacienda"></div>
            <div class="form-group"><label>Secretaría / Dependencia *</label><select id="official-office" class="form-control"><option value="">Seleccionar...</option></select></div>
            <div class="form-row">
                <div class="form-group"><label>Email</label><input type="email" id="official-email" class="form-control" placeholder="nombre@alcaldia.gov"></div>
                <div class="form-group"><label>Teléfono</label><input type="text" id="official-phone" class="form-control" placeholder="3100000000"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('official-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveOfficial()">Guardar Funcionario</button>
        </div>
    </div>
</div>

<!-- Delivery Modal — Multi-ítem -->
<div class="modal-overlay" id="delivery-modal">
    <div class="inv-modal" style="max-width:680px">
        <div class="modal-header">
            <h3>📋 Nueva Acta de Entrega</h3>
            <button class="modal-close" onclick="closeModal('delivery-modal')">×</button>
        </div>
        <div class="modal-body">

            {{-- Funcionario y generales --}}
            <div class="form-row">
                <div class="form-group">
                    <label>Funcionario Receptor *</label>
                    <select id="delivery-official" class="form-control">
                        <option value="">Seleccionar funcionario...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Entregado Por *</label>
                    <input type="text" id="delivery-by" class="form-control" placeholder="Nombre de quien entrega">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha de Entrega *</label>
                    <input type="date" id="delivery-date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Observaciones generales</label>
                    <input type="text" id="delivery-notes" class="form-control" placeholder="Condiciones, ubicación, etc.">
                </div>
            </div>

            {{-- Lista de ítems --}}
            <div style="margin-bottom:12px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <label style="margin:0;font-weight:600;font-size:0.85rem">📦 Bienes a Entregar *</label>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addDeliveryItemRow()">+ Agregar ítem</button>
                </div>
                <div id="delivery-items-list" style="display:flex;flex-direction:column;gap:8px">
                    {{-- filas se agregan dinámicamente --}}
                </div>
            </div>

            <div style="margin-top:8px;padding:10px 14px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);border-radius:10px;font-size:0.8rem;color:var(--text-dim)">
                ✍️ El acta impresa incluirá un espacio en blanco para que el funcionario receptor firme físicamente.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('delivery-modal')">Cancelar</button>
            <button class="btn btn-primary" id="delivery-save-btn" onclick="saveDelivery()">📋 Registrar Acta</button>
        </div>
    </div>
</div>

<!-- Acta Print Modal -->
<div class="modal-overlay" id="acta-print-modal">
    <div class="inv-modal" style="max-width:720px;background:#ffffff;color:#111111">
        <div class="modal-header" style="background:#ffffff;border-color:#e2e8f0;color:#111111">
            <h3 style="color:#111111;font-weight:700">📋 Acta de Entrega</h3>
            <div style="display:flex;gap:8px">
                <button class="btn btn-primary btn-sm no-print" onclick="window.print()">🖨️ Imprimir</button>
                <button class="modal-close no-print" onclick="closeModal('acta-print-modal')" style="color:#888">×</button>
            </div>
        </div>
        {{-- NO usar clase modal-body: hereda color:var(--text-primary) del tema oscuro --}}
        <div id="acta-print-body" style="
            padding: 24px;
            overflow-y: auto;
            max-height: 78vh;
            background: #ffffff;
            color: #111111;
            font-family: 'Segoe UI', Arial, sans-serif;
        "></div>
    </div>
</div>


<!-- Return Modal -->
<div class="modal-overlay" id="return-modal">
    <div class="inv-modal" style="max-width:440px">
        <div class="modal-header">
            <h3>↩️ Registrar Devolución</h3>
            <button class="modal-close" onclick="closeModal('return-modal')">×</button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.85rem;color:var(--text-dim);margin-bottom:16px">
                Acta: <strong id="return-acta-ref" style="color:var(--text-primary)"></strong>
            </p>
            <div class="form-group">
                <label>Fecha de Devolución *</label>
                <input type="date" id="return-date" class="form-control">
            </div>
            <div class="form-group">
                <label>Observaciones / Motivo</label>
                <textarea id="return-notes" class="form-control" rows="3" placeholder="Estado del bien al ser devuelto, causa de la devolución, etc."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('return-modal')">Cancelar</button>
            <button class="btn btn-success" id="return-save-btn" onclick="saveReturn()">↩️ Registrar Devolución</button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="toast-container"></div>

@endsection

@section('scripts')
<script>
const API = '{{ url('/api') }}';
let allAssets = [], allItems = [], allProviders = [], allAssignments = [], allOffices = [], allCategories = [];
let allOfficials = [], allDeliveries = [], allDeliveriesRaw = [];
let chartStatus = null, chartCategory = null;
let sigCanvas, sigCtx, sigDrawing = false;

/* ============================================================
   PAGE NAVIGATION
============================================================ */
function showPage(name, el) {
    document.querySelectorAll('.inv-page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.inv-tab').forEach(n => n.classList.remove('active'));
    document.getElementById('page-' + name).classList.add('active');
    if(el) el.classList.add('active');
    loadPage(name);
}

async function loadPage(name) {
    if(name === 'dashboard')       await loadDashboard();
    if(name === 'assets')          await loadAssets();
    if(name === 'items')           await loadItems();
    if(name === 'categories')      await loadCategories();
    if(name === 'providers')       await loadProviders();
    if(name === 'assignments')     await loadAssignments();
    if(name === 'offices')         await loadOffices();
    if(name === 'officials')       await loadOfficials();
    if(name === 'deliveries')      await loadDeliveries();
    if(name === 'alerts')          await loadAlerts();
    if(name === 'entries')         await loadEntries();
    if(name === 'disposals')       await loadDisposals();
    if(name === 'transfers')       await loadTransfers();
    if(name === 'maintenances')    await loadMaintenances();
    if(name === 'supply-requests') await loadSupplyRequests();
}

/* ============================================================
   FETCH HELPER
============================================================ */
async function apiFetch(path, opts={}) {
    const res = await fetch(API + path, {
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        ...opts
    });
    return res.json();
}

/* ============================================================
   DASHBOARD
============================================================ */
async function loadDashboard() {
    try {
        const [assetsRes, itemsRes, maintRes, reqRes] = await Promise.all([
            apiFetch('/fixed-assets'),
            apiFetch('/items'),
            apiFetch('/asset-maintenances'),
            apiFetch('/supply-requests'),
        ]);
        
        allAssets = assetsRes.data || [];
        const allItems = itemsRes.data || [];
        const allMaint = maintRes.data || [];
        const allReq = reqRes.data || [];

        // --- Synergy Tracking: Update Summary Cards ---
        document.getElementById('s-items').textContent   = allItems.length;
        document.getElementById('s-total').textContent   = allAssets.filter(a => !a.is_disposed).length;
        document.getElementById('s-low-stock').textContent = allItems.filter(i => i.is_low_stock).length;
        document.getElementById('s-pending-req').textContent = allReq.filter(r => r.status === 'pending').length;
        document.getElementById('s-pending-maint').textContent = allMaint.filter(m => m.status === 'pending').length;

        // Badge counter for main tab
        const badgeEl = document.getElementById('badge-assets');
        badgeEl.textContent = allAssets.length;
        badgeEl.style.display = allAssets.length > 0 ? '' : 'none';

        renderDashboardCharts(allAssets);
        renderRecentAssets(allAssets.slice(0, 8));
    } catch(e) { console.error(e); showToast('Error cargando resumen consolidado', 'error'); }
}

function renderDashboardCharts(assets) {
    const statusCount = {};
    assets.forEach(a => { statusCount[a.status] = (statusCount[a.status] || 0) + 1; });

    const colors = { nuevo:'#10b981', bueno:'#6366f1', regular:'#f59e0b', malo:'#ef4444', baja:'#64748b' };
    const labels = { nuevo:'Nuevo', bueno:'Bueno', regular:'Regular', malo:'Malo', baja:'Baja' };

    if(chartStatus) chartStatus.destroy();
    const ctx1 = document.getElementById('chartStatus').getContext('2d');
    chartStatus = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusCount).map(k => labels[k] || k),
            datasets: [{ data: Object.values(statusCount), backgroundColor: Object.keys(statusCount).map(k => colors[k] || '#6366f1'), borderWidth: 2, borderColor: '#111827' }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Inter' } } } } }
    });

    const catCount = {};
    assets.forEach(a => { const cat = a.item?.category?.name || 'Sin categoría'; catCount[cat] = (catCount[cat] || 0) + 1; });

    if(chartCategory) chartCategory.destroy();
    const ctx2 = document.getElementById('chartCategory').getContext('2d');
    chartCategory = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: Object.keys(catCount),
            datasets: [{ label: 'Activos', data: Object.values(catCount), backgroundColor: 'rgba(99,102,241,0.6)', borderColor: '#6366f1', borderWidth: 1, borderRadius: 6 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.04)' } }, y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.04)' } } }
        }
    });
}

function renderRecentAssets(assets) {
    const tbody = document.getElementById('dashboard-recent');
    tbody.innerHTML = assets.map(a => `
        <tr>
            <td><span class="code-pill">${a.inventory_code}</span></td>
            <td>${a.item?.name || '—'}<br><small>${a.item?.category?.name || ''}</small></td>
            <td><small>${a.provider?.company_name || '—'}</small></td>
            <td>${badgeStatus(a.status)}</td>
        </tr>
    `).join('');
}

/* ============================================================
   FIXED ASSETS
============================================================ */
async function loadAssets() {
    showLoader('assets');
    try {
        const res = await apiFetch('/fixed-assets');
        allAssets = res.data || [];
        const b = document.getElementById('badge-assets');
        b.textContent = allAssets.length;
        b.style.display = allAssets.length > 0 ? '' : 'none';
        renderAssetsTable(allAssets);
    } catch(e) { showToast('Error cargando activos', 'error'); } finally { hideLoader('assets'); }
}

function renderAssetsTable(assets) {
    const tbody = document.getElementById('assets-table-body');
    if(!assets.length) { tbody.innerHTML = ''; document.getElementById('assets-empty').style.display = ''; return; }
    document.getElementById('assets-empty').style.display = 'none';
    tbody.innerHTML = assets.map(a => `
        <tr>
            <td><span class="code-pill">${a.inventory_code}</span></td>
            <td><strong>${a.item?.name || '—'}</strong><br><small>${a.item?.category?.name || 'Sin categoría'}</small></td>
            <td><small>${a.serial_number || 'N/A'}</small></td>
            <td><small>${a.provider?.company_name || '—'}</small></td>
            <td><small>${a.purchase_price ? '$' + Number(a.purchase_price).toLocaleString() : 'N/A'}</small></td>
            <td>${badgeStatus(a.status)}</td>
            <td><small>${a.active_assignment ? a.active_assignment.custodian_name : '<span style="color:var(--text-muted)">Sin asignar</span>'}</small></td>
            <td>
                <div class="action-group">
                    <button class="btn btn-secondary btn-sm" onclick='editAsset(${JSON.stringify(a)})'>✏️</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteAsset(${a.id})">🗑️</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filterAssets(term) {
    const status = document.getElementById('assets-status-filter').value;
    let filtered = allAssets;
    if(term) {
        const t = term.toLowerCase();
        filtered = filtered.filter(a =>
            a.inventory_code.toLowerCase().includes(t) ||
            (a.item?.name || '').toLowerCase().includes(t) ||
            (a.serial_number || '').toLowerCase().includes(t)
        );
    }
    if(status) filtered = filtered.filter(a => a.status === status);
    renderAssetsTable(filtered);
}

async function openAssetModal() {
    document.getElementById('asset-modal-title').textContent = 'Nuevo Activo Fijo';
    document.getElementById('asset-id').value = '';
    document.getElementById('asset-code').value = '';
    document.getElementById('asset-serial').value = '';
    document.getElementById('asset-price').value = '';
    document.getElementById('asset-status').value = 'nuevo';
    await populateAssetSelects();
    openModal('asset-modal');
}

async function editAsset(asset) {
    document.getElementById('asset-modal-title').textContent = 'Editar Activo Fijo';
    document.getElementById('asset-id').value = asset.id;
    document.getElementById('asset-code').value = asset.inventory_code;
    document.getElementById('asset-serial').value = asset.serial_number || '';
    document.getElementById('asset-price').value = asset.purchase_price || '';
    document.getElementById('asset-status').value = asset.status;
    await populateAssetSelects(asset.item_id, asset.provider_id);
    openModal('asset-modal');
}

async function populateAssetSelects(selItem, selProv) {
    if(!allItems.length) { const r = await apiFetch('/inventory'); allItems = r.data || []; }
    if(!allProviders.length) { const r = await apiFetch('/providers'); allProviders = r.data || []; }
    document.getElementById('asset-item').innerHTML = '<option value="">Seleccionar artículo...</option>' + allItems.map(i => `<option value="${i.id}" ${i.id == selItem ? 'selected':''}>${i.name}</option>`).join('');
    document.getElementById('asset-provider').innerHTML = '<option value="">Seleccionar proveedor...</option>' + allProviders.map(p => `<option value="${p.id}" ${p.id == selProv ? 'selected':''}>${p.company_name}</option>`).join('');
}

async function saveAsset() {
    const id = document.getElementById('asset-id').value;
    const payload = {
        inventory_code: document.getElementById('asset-code').value,
        item_id:        document.getElementById('asset-item').value,
        provider_id:    document.getElementById('asset-provider').value,
        serial_number:  document.getElementById('asset-serial').value,
        purchase_price: document.getElementById('asset-price').value || null,
        status:         document.getElementById('asset-status').value,
    };
    if(!payload.inventory_code || !payload.item_id || !payload.provider_id) { showToast('Completa los campos obligatorios', 'error'); return; }
    const btn = document.getElementById('asset-save-btn');
    btn.textContent = 'Guardando...'; btn.disabled = true;
    try {
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/fixed-assets/${id}` : '/fixed-assets';
        const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
        if(res.data) { closeModal('asset-modal'); showToast(id ? 'Activo actualizado' : 'Activo creado exitosamente', 'success'); loadAssets(); }
        else { showToast(res.message || 'Error al guardar', 'error'); }
    } catch(e) { showToast('Error de red', 'error'); }
    finally { btn.textContent = 'Guardar Activo'; btn.disabled = false; }
}

async function deleteAsset(id) {
    if(!confirm('¿Estás seguro de eliminar este activo? Esta acción es irreversible.')) return;
    await apiFetch(`/fixed-assets/${id}`, { method: 'DELETE' });
    showToast('Activo eliminado', 'info'); loadAssets();
}

/* ============================================================
   ITEMS
============================================================ */
let allItemsRaw = [];
async function loadItems() {
    showLoader('items');
    try {
        const res = await apiFetch('/inventory');
        allItemsRaw = res.data || [];
        renderItemsTable(allItemsRaw);
    } catch(e) {} finally { hideLoader('items'); }
}
function renderItemsTable(items) {
    const tbody = document.getElementById('items-table-body');
    if(!items.length) { tbody.innerHTML=''; document.getElementById('items-empty').style.display=''; return; }
    document.getElementById('items-empty').style.display = 'none';
    tbody.innerHTML = items.map(i => `
        <tr class="${i.is_low_stock?'low-stock-row':''}">
            <td><small style="color:var(--text-muted)">#${i.id}</small></td>
            <td><strong>${i.name}</strong></td>
            <td>${i.category?.name || '—'} <small style="color:var(--text-muted)">${i.category?.code || ''}</small></td>
            <td>${i.is_asset ? '<span class="badge badge-asset">Activo Fijo</span>' : '<span class="badge badge-consumable">Consumible</span>'}</td>
            <td>${i.is_asset ? '<small style="color:var(--text-muted)">N/A</small>' : `<strong>${i.stock}</strong>`}</td>
            <td>${i.is_asset ? '<small style="color:var(--text-muted)">N/A</small>' : i.min_stock}</td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" onclick='openItemModal(${JSON.stringify(i)})'>✏️</button>
                <button class="btn btn-danger btn-sm" onclick="deleteItem(${i.id})">🗑️</button>
            </div></td>
        </tr>
    `).join('');
}
function filterItems(term) {
    const t = term.toLowerCase();
    renderItemsTable(allItemsRaw.filter(i => i.name.toLowerCase().includes(t)));
}
function filterItemsByType(val) {
    if(val === '') { renderItemsTable(allItemsRaw); return; }
    renderItemsTable(allItemsRaw.filter(i => String(i.is_asset ? 1 : 0) === val));
}
function toggleItemStock(val) {
    document.getElementById('item-stock-wrap').style.display = val === '0' ? '' : 'none';
}
async function openItemModal(item = null) {
    if(!allCategories.length) { const r = await apiFetch('/categories'); allCategories = r.data || []; }
    document.getElementById('item-modal-title').textContent = item ? 'Editar Artículo' : 'Nuevo Artículo';
    document.getElementById('item-id').value = item?.id || '';
    document.getElementById('item-name').value = item?.name || '';
    document.getElementById('item-type').value = item ? (item.is_asset ? '1' : '0') : '1';
    document.getElementById('item-stock').value = item?.stock || 0;
    document.getElementById('item-min-stock').value = item?.min_stock || 5;
    document.getElementById('item-category').innerHTML = '<option value="">Seleccionar...</option>' +
        allCategories.map(c => `<option value="${c.id}" ${c.id == item?.category_id ? 'selected' : ''}>${c.name}</option>`).join('');
    toggleItemStock(item ? (item.is_asset ? '1' : '0') : '1');
    openModal('item-modal');
}
async function saveItem() {
    const id = document.getElementById('item-id').value;
    const isAsset = document.getElementById('item-type').value === '1';
    const payload = {
        name:        document.getElementById('item-name').value,
        category_id: document.getElementById('item-category').value,
        is_asset:    isAsset,
        stock:       isAsset ? 0 : parseInt(document.getElementById('item-stock').value) || 0,
        min_stock:   isAsset ? 0 : parseInt(document.getElementById('item-min-stock').value) || 5,
    };
    if(!payload.name || !payload.category_id) { showToast('Nombre y categoría son obligatorios','error'); return; }
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/items/${id}` : '/items';
    const res = await apiFetch(url, {method, body: JSON.stringify(payload)});
    if(res.data) { closeModal('item-modal'); showToast(id ? 'Artículo actualizado' : 'Artículo creado','success'); loadItems(); }
    else showToast(res.message || 'Error al guardar','error');
}
async function deleteItem(id) {
    if(!confirm('¿Eliminar este artículo? Si tiene activos asociados no será posible.')) return;
    const res = await apiFetch(`/items/${id}`, {method:'DELETE'});
    showToast(res.message || 'Artículo eliminado', res.message?.includes('No se puede') ? 'error' : 'info');
    if(!res.message?.includes('No se puede')) loadItems();
}

/* ============================================================
   CATEGORIES
============================================================ */
async function loadCategories() {
    showLoader('categories');
    try {
        const res = await apiFetch('/categories');
        allCategories = (res.data || []).map(c => ({ ...c, count: c.items_count ?? 0 }));
        renderCategories(allCategories);
    } catch(e) {} finally { hideLoader('categories'); }
}
function renderCategories(cats) {
    const tbody = document.getElementById('categories-table-body');
    if(!cats.length) { tbody.innerHTML=''; document.getElementById('categories-empty').style.display=''; return; }
    document.getElementById('categories-empty').style.display = 'none';
    tbody.innerHTML = cats.map(c => `
        <tr>
            <td><span class="code-pill">${c.code}</span></td>
            <td><strong>${c.name}</strong></td>
            <td><span class="badge badge-bueno">${c.count} artículo(s)</span></td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" onclick='openCategoryModal(${JSON.stringify(c)})'>✏️</button>
                <button class="btn btn-danger btn-sm" onclick="deleteCategory(${c.id})">🗑️</button>
            </div></td>
        </tr>
    `).join('');
}
function filterCategories(term) {
    const t = term.toLowerCase();
    renderCategories(allCategories.filter(c => c.name.toLowerCase().includes(t) || c.code.toLowerCase().includes(t)));
}
function openCategoryModal(cat = null) {
    document.getElementById('category-modal-title').textContent = cat ? 'Editar Categoría' : 'Nueva Categoría';
    document.getElementById('category-id').value   = cat?.id || '';
    document.getElementById('category-code').value = cat?.code || '';
    document.getElementById('category-name').value = cat?.name || '';
    openModal('category-modal');
}
async function saveCategory() {
    const id = document.getElementById('category-id').value;
    const payload = { code: document.getElementById('category-code').value, name: document.getElementById('category-name').value };
    if(!payload.code || !payload.name) { showToast('Código y nombre son obligatorios','error'); return; }
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/categories/${id}` : '/categories';
    const res = await apiFetch(url, {method, body: JSON.stringify(payload)});
    if(res.data) { closeModal('category-modal'); showToast(id ? 'Categoría actualizada' : 'Categoría creada','success'); loadCategories(); }
    else showToast(res.message || 'Error al guardar','error');
}
async function deleteCategory(id) {
    if(!confirm('¿Eliminar esta categoría?')) return;
    const res = await apiFetch(`/categories/${id}`, {method:'DELETE'});
    showToast(res.message || 'Categoría eliminada','info'); loadCategories();
}

/* ============================================================
   PROVIDERS
============================================================ */
async function loadProviders() {
    showLoader('providers');
    try {
        const res = await apiFetch('/providers');
        allProviders = res.data || [];
        renderProvidersTable(allProviders);
    } catch(e) {} finally { hideLoader('providers'); }
}
function renderProvidersTable(providers) {
    const tbody = document.getElementById('providers-table-body');
    if(!providers.length) { tbody.innerHTML=''; document.getElementById('providers-empty').style.display=''; return; }
    document.getElementById('providers-empty').style.display = 'none';
    tbody.innerHTML = providers.map(p => `
        <tr>
            <td><span class="code-pill">${p.nit}</span></td>
            <td><strong>${p.company_name}</strong></td>
            <td><small>${p.contact || '—'}</small></td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" onclick='editProvider(${JSON.stringify(p)})'>✏️</button>
                <button class="btn btn-danger btn-sm" onclick="deleteProvider(${p.id})">🗑️</button>
            </div></td>
        </tr>
    `).join('');
}
function filterProviders(term) {
    const t = term.toLowerCase();
    renderProvidersTable(allProviders.filter(p => p.nit.toLowerCase().includes(t) || p.company_name.toLowerCase().includes(t)));
}
function openProviderModal() {
    document.getElementById('provider-modal-title').textContent = 'Nuevo Proveedor';
    document.getElementById('provider-id').value = '';
    document.getElementById('provider-nit').value = '';
    document.getElementById('provider-name').value = '';
    document.getElementById('provider-contact').value = '';
    openModal('provider-modal');
}
function editProvider(p) {
    document.getElementById('provider-modal-title').textContent = 'Editar Proveedor';
    document.getElementById('provider-id').value = p.id;
    document.getElementById('provider-nit').value = p.nit;
    document.getElementById('provider-name').value = p.company_name;
    document.getElementById('provider-contact').value = p.contact || '';
    openModal('provider-modal');
}
async function saveProvider() {
    const id = document.getElementById('provider-id').value;
    const payload = { nit: document.getElementById('provider-nit').value, company_name: document.getElementById('provider-name').value, contact: document.getElementById('provider-contact').value };
    if(!payload.nit || !payload.company_name) { showToast('NIT y Razón Social son obligatorios', 'error'); return; }
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/providers/${id}` : '/providers';
    const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
    if(res.data) { closeModal('provider-modal'); showToast(id ? 'Proveedor actualizado' : 'Proveedor creado', 'success'); loadProviders(); }
    else showToast(res.message || 'Error al guardar', 'error');
}
async function deleteProvider(id) {
    if(!confirm('¿Eliminar este proveedor?')) return;
    await apiFetch(`/providers/${id}`, { method: 'DELETE' });
    showToast('Proveedor eliminado', 'info'); loadProviders();
}

/* ============================================================
   ASSIGNMENTS
============================================================ */
async function loadAssignments() {
    showLoader('assignments');
    try {
        const res = await apiFetch('/fixed-assets');
        const assets = res.data || [];
        allAssignments = assets.filter(a => a.active_assignment).map(a => ({
            asset: a, assignment: a.active_assignment
        }));
        renderAssignmentsTable(allAssignments);
    } catch(e) {} finally { hideLoader('assignments'); }
}
function renderAssignmentsTable(list) {
    const tbody = document.getElementById('assignments-table-body');
    if(!list.length) { tbody.innerHTML=''; document.getElementById('assignments-empty').style.display=''; return; }
    document.getElementById('assignments-empty').style.display = 'none';
    tbody.innerHTML = list.map(({asset, assignment}) => `
        <tr>
            <td><span class="code-pill">${asset.inventory_code}</span><br><small>${asset.item?.name || ''}</small></td>
            <td><strong>${assignment.custodian_name}</strong></td>
            <td><small>${assignment.office?.name || '—'}</small></td>
            <td><small>${formatDate(assignment.assignment_date)}</small></td>
            <td><span class="badge badge-nuevo">Activa</span></td>
            <td><div class="action-group">
                <button class="btn btn-danger btn-sm" onclick="deleteAssignment(${assignment.id})">❌ Cancelar</button>
            </div></td>
        </tr>
    `).join('');
}
function filterAssignments(term) {
    const t = term.toLowerCase();
    renderAssignmentsTable(allAssignments.filter(({asset,assignment}) =>
        assignment.custodian_name.toLowerCase().includes(t) || asset.inventory_code.toLowerCase().includes(t)
    ));
}
async function openAssignModal() {
    if(!allAssets.length) { const r = await apiFetch('/fixed-assets'); allAssets = r.data || []; }
    if(!allOffices.length) await loadOfficesData();
    document.getElementById('assign-asset').innerHTML = '<option value="">Seleccionar activo...</option>' + allAssets.map(a => `<option value="${a.id}">${a.inventory_code} - ${a.item?.name || ''}</option>`).join('');
    document.getElementById('assign-office').innerHTML = '<option value="">Seleccionar secretaría...</option>' + allOffices.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
    document.getElementById('assign-date').value = new Date().toISOString().split('T')[0];
    openModal('assign-modal');
}
async function saveAssignment() {
    const payload = {
        fixed_asset_id: document.getElementById('assign-asset').value,
        office_id:      document.getElementById('assign-office').value,
        custodian_name: document.getElementById('assign-custodian').value,
        assignment_date:document.getElementById('assign-date').value,
    };
    if(!payload.fixed_asset_id || !payload.office_id || !payload.custodian_name || !payload.assignment_date) {
        showToast('Todos los campos son obligatorios', 'error'); return;
    }
    const res = await apiFetch('/assignments/assign', { method: 'POST', body: JSON.stringify(payload) });
    if(res.data || res.message?.includes('exitosamente')) {
        closeModal('assign-modal'); showToast('Asignación registrada exitosamente', 'success'); loadAssignments();
    } else showToast(res.message || 'Error al asignar', 'error');
}
async function deleteAssignment(id) {
    if(!confirm('¿Cancelar esta asignación? El activo quedará sin custodio.')) return;
    const res = await apiFetch(`/assignments/${id}`, {method:'DELETE'});
    showToast(res.message || 'Asignación cancelada', 'info'); loadAssignments();
}

/* ============================================================
   OFFICES
============================================================ */
async function loadOfficesData() {
    const res = await apiFetch('/offices');
    allOffices = res.data || [];
    return allOffices;
}
async function loadOffices() {
    showLoader('offices');
    try {
        await loadOfficesData();
        renderOfficesTable(allOffices);
    } catch(e) { console.error(e); } finally { hideLoader('offices'); }
}
function renderOfficesTable(list) {
    const tbody = document.getElementById('offices-table-body');
    if(!list.length) { tbody.innerHTML=''; document.getElementById('offices-empty').style.display=''; return; }
    document.getElementById('offices-empty').style.display = 'none';
    tbody.innerHTML = list.map(o => `
        <tr>
            <td><small style="color:var(--text-muted)">#${o.id}</small></td>
            <td><strong>${o.name}</strong></td>
            <td><span class="badge badge-bueno">${o.assignments_count ?? 0} asignación(es)</span></td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" onclick='openOfficeModal(${JSON.stringify(o)})'>✏️</button>
                <button class="btn btn-danger btn-sm" onclick="deleteOffice(${o.id})">🗑️</button>
            </div></td>
        </tr>
    `).join('');
}
function filterOfficesTable(term) {
    const t = term.toLowerCase();
    renderOfficesTable(allOffices.filter(o => o.name.toLowerCase().includes(t)));
}
function openOfficeModal(office = null) {
    document.getElementById('office-modal-title').textContent = office ? 'Editar Secretaría' : 'Nueva Secretaría';
    document.getElementById('office-id').value   = office?.id || '';
    document.getElementById('office-name').value = office?.name || '';
    openModal('office-modal');
}
async function saveOffice() {
    const id = document.getElementById('office-id').value;
    const payload = { name: document.getElementById('office-name').value };
    if(!payload.name) { showToast('El nombre es obligatorio','error'); return; }
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/offices/${id}` : '/offices';
    const res = await apiFetch(url, {method, body: JSON.stringify(payload)});
    if(res.data) { closeModal('office-modal'); showToast(id ? 'Secretaría actualizada' : 'Secretaría creada','success'); loadOffices(); }
    else showToast(res.message || 'Error al guardar','error');
}
async function deleteOffice(id) {
    if(!confirm('¿Eliminar esta secretaría?')) return;
    const res = await apiFetch(`/offices/${id}`, {method:'DELETE'});
    showToast(res.message || 'Secretaría eliminada','info'); loadOffices();
}

/* ============================================================
   OFFICIALS
============================================================ */
async function loadOfficials() {
    showLoader('officials');
    try {
        if(!allOffices.length) await loadOfficesData();
        const res = await apiFetch('/officials');
        allOfficials = res.data || [];
        renderOfficialsTable(allOfficials);
    } catch(e){ console.error(e); } finally { hideLoader('officials'); }
}
function renderOfficialsTable(list) {
    const tbody = document.getElementById('officials-table-body');
    if(!list.length){ tbody.innerHTML=''; document.getElementById('officials-empty').style.display=''; return; }
    document.getElementById('officials-empty').style.display='none';
    tbody.innerHTML = list.map(o => `
        <tr>
            <td><span class="code-pill">${o.document_type} ${o.document_number}</span></td>
            <td><strong>${o.full_name}</strong></td>
            <td><small>${o.position}</small></td>
            <td><small>${o.office?.name || '—'}</small></td>
            <td><small>${o.email || '—'}<br>${o.phone || ''}</small></td>
            <td><span class="badge badge-bueno">${o.active_deliveries_count ?? 0} activa(s)</span></td>
            <td>${o.is_active ? '<span class="badge badge-nuevo">Activo</span>' : '<span class="badge badge-baja">Inactivo</span>'}</td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" onclick='editOfficial(${JSON.stringify(o)})'>✏️</button>
                <button class="btn btn-danger btn-sm" onclick="deleteOfficial(${o.id})">🗑️</button>
            </div></td>
        </tr>
    `).join('');
}
function filterOfficials(term) {
    const t = term.toLowerCase();
    renderOfficialsTable(allOfficials.filter(o =>
        o.full_name.toLowerCase().includes(t) ||
        o.document_number.toLowerCase().includes(t) ||
        o.position.toLowerCase().includes(t)
    ));
}
async function openOfficialModal() {
    document.getElementById('official-modal-title').textContent = 'Nuevo Funcionario';
    document.getElementById('official-id').value = '';
    ['official-doc','official-name','official-position','official-email','official-phone'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('official-doctype').value = 'CC';
    if(!allOffices.length) await loadOfficesData();
    document.getElementById('official-office').innerHTML = '<option value="">Seleccionar...</option>' +
        allOffices.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
    openModal('official-modal');
}
function editOfficial(o) {
    document.getElementById('official-modal-title').textContent = 'Editar Funcionario';
    document.getElementById('official-id').value = o.id;
    document.getElementById('official-doctype').value = o.document_type;
    document.getElementById('official-doc').value = o.document_number;
    document.getElementById('official-name').value = o.full_name;
    document.getElementById('official-position').value = o.position;
    document.getElementById('official-email').value = o.email || '';
    document.getElementById('official-phone').value = o.phone || '';
    const buildOfficeSel = () => {
        document.getElementById('official-office').innerHTML = '<option value="">Seleccionar...</option>' +
            allOffices.map(off => `<option value="${off.id}" ${off.id==o.office_id?'selected':''}>${off.name}</option>`).join('');
    };
    if(!allOffices.length) loadOfficesData().then(buildOfficeSel); else buildOfficeSel();
    openModal('official-modal');
}
async function saveOfficial() {
    const id = document.getElementById('official-id').value;
    const payload = {
        document_type:   document.getElementById('official-doctype').value,
        document_number: document.getElementById('official-doc').value,
        full_name:       document.getElementById('official-name').value,
        position:        document.getElementById('official-position').value,
        office_id:       document.getElementById('official-office').value,
        email:           document.getElementById('official-email').value || null,
        phone:           document.getElementById('official-phone').value || null,
    };
    if(!payload.document_number||!payload.full_name||!payload.position||!payload.office_id){
        showToast('Completa todos los campos obligatorios','error'); return;
    }
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/officials/${id}` : '/officials';
    const res = await apiFetch(url, {method, body: JSON.stringify(payload)});
    if(res.data){ closeModal('official-modal'); showToast(id?'Funcionario actualizado':'Funcionario registrado','success'); loadOfficials(); }
    else showToast(res.message||'Error al guardar','error');
}
async function deleteOfficial(id){
    if(!confirm('¿Eliminar este funcionario?')) return;
    await apiFetch(`/officials/${id}`,{method:'DELETE'});
    showToast('Funcionario eliminado','info'); loadOfficials();
}

/* ============================================================
   DELIVERIES
============================================================ */
async function loadDeliveries() {
    showLoader('deliveries');
    try {
        const res = await apiFetch('/deliveries');
        allDeliveriesRaw = res.data || [];
        allDeliveries = [...allDeliveriesRaw];
        const alertRes = await apiFetch('/alerts/low-stock');
        const alertCount = alertRes.count || 0;
        const banner = document.getElementById('deliveries-alert-banner');
        if(alertCount > 0){
            banner.innerHTML = `<div class="alert-banner"><span class="alert-icon">⚠️</span><div class="alert-text"><strong>${alertCount} suministro(s) con stock bajo</strong><span>Revisa la sección "Alertas de Stock" para reponer inventario</span></div></div>`;
        } else { banner.innerHTML=''; }
        renderDeliveriesTable(allDeliveries);
    } catch(e){ console.error(e); } finally { hideLoader('deliveries'); }
}
function renderDeliveriesTable(list) {
    const tbody = document.getElementById('deliveries-table-body');
    if(!list.length){ tbody.innerHTML=''; document.getElementById('deliveries-empty').style.display=''; return; }
    document.getElementById('deliveries-empty').style.display='none';
    tbody.innerHTML = list.map(d => {
        const items = d.delivery_items || [];
        // Resumen de ítems para la fila
        const itemSummary = items.length === 0
            ? '<span style="color:var(--text-dim)">—</span>'
            : items.length === 1
                ? `<strong>${items[0].display_name || '—'}</strong>`
                : `<strong>${items[0].display_name || '—'}</strong> <small style="color:var(--text-dim)">+${items.length-1} más</small>`;
        const typeIcons = [...new Set(items.map(i => i.type))];
        const typeBadge = typeIcons.map(t =>
            `<span class="badge ${t==='asset'?'badge-asset':'badge-consumable'}">${t==='asset'?'Activo':'Consumible'}</span>`
        ).join(' ');
        const statusBadge = d.is_returned
            ? '<span class="badge badge-returned">Devuelto</span>'
            : '<span class="badge badge-active-delivery">Vigente</span>';
        return `
        <tr>
            <td><span class="code-pill" style="font-size:0.72rem">${d.acta_number}</span></td>
            <td>${typeBadge || '—'}</td>
            <td>${itemSummary}</td>
            <td><strong>${d.official?.full_name||'—'}</strong> <small>· ${d.official?.position||''}</small></td>
            <td><small>${d.official?.office?.name||'—'}</small></td>
            <td><small>${formatDate(d.delivery_date)}</small></td>
            <td>${statusBadge}</td>
            <td><div class="action-group">
                <button class="btn btn-secondary btn-sm" title="Ver / Imprimir Acta" onclick="printActa(${d.id})">🖨️</button>
                ${!d.is_returned ? `<button class="btn btn-success btn-sm" title="Registrar Devolución" onclick="openReturnModal(${d.id},'${d.acta_number}')">↩️</button>` : ''}
            </div></td>
        </tr>`;
    }).join('');
}
function filterDeliveries(term) {
    const t = term.toLowerCase();
    const filtered = allDeliveriesRaw.filter(d =>
        (d.acta_number||'').toLowerCase().includes(t) ||
        (d.official?.full_name||'').toLowerCase().includes(t) ||
        (d.delivery_items||[]).some(i => (i.display_name||'').toLowerCase().includes(t))
    );
    allDeliveries = filtered;
    renderDeliveriesTable(filtered);
}
function filterDeliveriesByStatus(val) {
    let f = allDeliveriesRaw;
    if(val==='active')   f = f.filter(d => !d.is_returned);
    if(val==='returned') f = f.filter(d => d.is_returned);
    allDeliveries = f;
    renderDeliveriesTable(f);
}

/* Signature canvas */
function initSignatureCanvas() {
    sigCanvas = document.getElementById('sig-canvas');
    if(!sigCanvas) return;
    sigCtx = sigCanvas.getContext('2d');
    sigCtx.fillStyle = '#ffffff';
    sigCtx.fillRect(0, 0, sigCanvas.width, sigCanvas.height);
    sigCtx.strokeStyle = '#1e293b'; sigCtx.lineWidth = 2.5; sigCtx.lineCap = 'round'; sigCtx.lineJoin = 'round';
    sigCanvas.addEventListener('mousedown', e => { sigDrawing=true; sigCtx.beginPath(); sigCtx.moveTo(...getXY(e,sigCanvas)); });
    sigCanvas.addEventListener('mousemove', e => { if(!sigDrawing)return; sigCtx.lineTo(...getXY(e,sigCanvas)); sigCtx.stroke(); });
    sigCanvas.addEventListener('mouseup', () => sigDrawing=false);
    sigCanvas.addEventListener('mouseleave', () => sigDrawing=false);
    sigCanvas.addEventListener('touchstart', e => { e.preventDefault(); sigDrawing=true; sigCtx.beginPath(); sigCtx.moveTo(...getXY(e.touches[0],sigCanvas)); });
    sigCanvas.addEventListener('touchmove', e => { e.preventDefault(); if(!sigDrawing)return; sigCtx.lineTo(...getXY(e.touches[0],sigCanvas)); sigCtx.stroke(); });
    sigCanvas.addEventListener('touchend', () => sigDrawing=false);
}
function getXY(e, canvas) {
    const r = canvas.getBoundingClientRect();
    return [e.clientX - r.left, e.clientY - r.top];
}
function clearSignature() {
    if(!sigCtx) return;
    sigCtx.fillStyle = '#ffffff';
    sigCtx.fillRect(0, 0, sigCanvas.width, sigCanvas.height);
}
function getSignatureData() {
    if(!sigCanvas) return null;
    const data = sigCtx.getImageData(0, 0, sigCanvas.width, sigCanvas.height).data;
    for(let i=0; i<data.length; i+=4){
        if(data[i]<250 || data[i+1]<250 || data[i+2]<250) return sigCanvas.toDataURL('image/png');
    }
    return null;
}

/* ── Multi-item delivery modal ─────────────────────────────── */
let _deliveryAssets = [], _deliveryConsumables = [];
let _deliveryItemCount = 0;

async function openDeliveryModal() {
    const btn = document.getElementById('delivery-save-btn');
    // Mostrar feedback de carga
    openModal('delivery-modal');
    document.getElementById('delivery-items-list').innerHTML =
        '<div style="text-align:center;padding:16px;color:var(--text-dim);font-size:0.82rem">⏳ Cargando datos...</div>';

    // Cargar datos en paralelo
    const [officialsRes, assetsRes, inventoryRes] = await Promise.all([
        allOfficials.length ? Promise.resolve({data: allOfficials}) : apiFetch('/officials'),
        allAssets.length    ? Promise.resolve({data: allAssets})    : apiFetch('/fixed-assets'),
        apiFetch('/inventory'),
    ]);
    allOfficials = officialsRes.data || [];
    allAssets    = assetsRes.data    || [];
    _deliveryAssets      = allAssets;
    _deliveryConsumables = (inventoryRes.data || []).filter(i => !i.is_asset);

    document.getElementById('delivery-official').innerHTML = '<option value="">Seleccionar funcionario...</option>' +
        allOfficials.map(o => `<option value="${o.id}">${o.full_name} – ${o.position} (${o.office?.name||''})</option>`).join('');
    document.getElementById('delivery-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('delivery-notes').value = '';
    document.getElementById('delivery-by').value = '';

    // Una fila inicial ya con los datos cargados
    _deliveryItemCount = 0;
    document.getElementById('delivery-items-list').innerHTML = '';
    addDeliveryItemRow();
}

function addDeliveryItemRow() {
    const idx = ++_deliveryItemCount;
    const assetsHtml = _deliveryAssets.map(a =>
        `<option value="${a.id}">${a.inventory_code} – ${a.item?.name||''}</option>`
    ).join('');
    const consumHtml = _deliveryConsumables.map(i =>
        `<option value="${i.id}">${i.name} (Stock: ${i.stock})</option>`
    ).join('');

    const row = document.createElement('div');
    row.className = 'delivery-item-row';
    row.id = `ditem-row-${idx}`;
    row.innerHTML = `
        <select class="ditem-type" onchange="toggleDItemType(${idx}, this.value)">
            <option value="asset">📦 Activo Fijo</option>
            <option value="consumable">🗂️ Consumible</option>
        </select>
        <div class="ditem-selectors">
            <select class="ditem-asset-sel" id="ditem-asset-${idx}">
                <option value="">Seleccionar activo...</option>${assetsHtml}
            </select>
            <select class="ditem-cons-sel" id="ditem-cons-${idx}" style="display:none">
                <option value="">Seleccionar artículo...</option>${consumHtml}
            </select>
        </div>
        <input type="number" class="ditem-qty delivery-item-qty" id="ditem-qty-${idx}"
               value="1" min="1" style="display:none" title="Cantidad">
        <button type="button" class="delivery-item-remove" onclick="removeDItemRow(${idx})" title="Eliminar">✕</button>`;
    document.getElementById('delivery-items-list').appendChild(row);
}

function toggleDItemType(idx, type) {
    document.getElementById(`ditem-asset-${idx}`).style.display = type==='asset' ? '' : 'none';
    document.getElementById(`ditem-cons-${idx}`).style.display  = type==='consumable' ? '' : 'none';
    document.getElementById(`ditem-qty-${idx}`).style.display   = type==='consumable' ? '' : 'none';
}

function removeDItemRow(idx) {
    const row = document.getElementById(`ditem-row-${idx}`);
    const list = document.getElementById('delivery-items-list');
    if(list.children.length <= 1){ showToast('Debe haber al menos un ítem en el acta','error'); return; }
    row?.remove();
}

async function saveDelivery() {
    const official_id  = document.getElementById('delivery-official').value;
    const delivered_by = document.getElementById('delivery-by').value.trim();
    const delivery_date= document.getElementById('delivery-date').value;
    const notes        = document.getElementById('delivery-notes').value || null;

    if(!official_id || !delivered_by || !delivery_date) {
        showToast('Funcionario, entregado por y fecha son obligatorios','error'); return;
    }

    // Recopilar ítems
    const rows = document.querySelectorAll('#delivery-items-list .delivery-item-row');
    const items = [];
    let valid = true;
    rows.forEach(row => {
        const type = row.querySelector('.ditem-type').value;
        if(type === 'asset') {
            const fa_id = row.querySelector('.ditem-asset-sel')?.value;
            if(!fa_id){ showToast('Selecciona un activo en todas las filas','error'); valid=false; return; }
            items.push({ type: 'asset', fixed_asset_id: fa_id });
        } else {
            const item_id = row.querySelector('.ditem-cons-sel')?.value;
            const qty     = parseInt(row.querySelector('.ditem-qty')?.value) || 1;
            if(!item_id){ showToast('Selecciona un artículo en todas las filas','error'); valid=false; return; }
            items.push({ type: 'consumable', item_id, quantity: qty });
        }
    });
    if(!valid || !items.length) return;

    const payload = { official_id, delivered_by, delivery_date, notes, items };

    const btn = document.getElementById('delivery-save-btn');
    btn.textContent = 'Guardando...'; btn.disabled = true;
    try {
        const res = await apiFetch('/deliveries', {method:'POST', body:JSON.stringify(payload)});
        if(res.data){
            closeModal('delivery-modal');
            showToast(`Acta ${res.data.acta_number} registrada exitosamente`,'success');
            loadDeliveries();
        } else showToast(res.message||'Error al guardar','error');
    } catch(e){ showToast('Error de red','error'); }
    finally { btn.textContent='📋 Registrar Acta'; btn.disabled=false; }
}

/* ── Return modal ─────────────────────────────────────────── */
let _returnId = null;
function openReturnModal(id, actaNumber) {
    _returnId = id;
    document.getElementById('return-acta-ref').textContent = actaNumber;
    document.getElementById('return-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('return-notes').value = '';
    openModal('return-modal');
}
async function saveReturn() {
    const date  = document.getElementById('return-date').value;
    const notes = document.getElementById('return-notes').value || '';
    if(!date){ showToast('Indica la fecha de devolución','error'); return; }
    const btn = document.getElementById('return-save-btn');
    btn.textContent = 'Guardando...'; btn.disabled = true;
    try {
        const res = await apiFetch(`/deliveries/${_returnId}/return`, {method:'PATCH', body: JSON.stringify({returned_date:date, return_notes:notes})});
        if(res.data || res.message?.includes('Devoluci')){
            closeModal('return-modal');
            showToast('Devolución registrada','success');
            loadDeliveries();
        } else showToast(res.message||'Error','error');
    } catch(e){ showToast('Error de red','error'); }
    finally { btn.textContent = 'Registrar Devolución'; btn.disabled = false; }
}

/* ── Print acta (multi-ítem) ───────────────────────────────── */
async function printActa(id) {
    try {
        const res = await apiFetch(`/deliveries/${id}`);
        const d = res.data;
        if(!d){ showToast('No se encontró el acta','error'); return; }

        const today     = new Date().toLocaleDateString('es-CO',{day:'2-digit',month:'long',year:'numeric'});
        const fEntrega  = formatDate(d.delivery_date);
        const fDevuelto = d.is_returned ? formatDate(d.returned_date) : null;
        const items     = d.delivery_items || [];

        // Tabla de bienes entregados
        const itemsRowsHtml = items.map((it, idx) => {
            const even = idx % 2 === 1;
            let desc='—', code='—', serie='—', categ='—', precio='—', tipo='—';
            if(it.type === 'asset') {
                const fa = it.fixed_asset;
                desc   = fa?.item?.name || it.description || 'Activo Fijo';
                code   = fa?.inventory_code || '—';
                serie  = fa?.serial_number  || '—';
                categ  = fa?.item?.category?.name || '—';
                precio = fa?.purchase_price ? '$' + Number(fa.purchase_price).toLocaleString('es-CO') : '—';
                tipo   = 'Activo Fijo';
            } else {
                desc   = it.item?.name || it.description || 'Consumible';
                code   = `${it.quantity} unid.`;
                categ  = it.item?.category?.name || '—';
                tipo   = 'Consumible';
            }
            return `<tr style="${even?'background:#f9fafb':''}">
                <td style="padding:6px 10px;font-weight:600;color:#1e3a5f">${idx+1}</td>
                <td style="padding:6px 10px"><span style="font-size:0.68rem;background:${it.type==='asset'?'#dbeafe':'#dcfce7'};color:${it.type==='asset'?'#1d4ed8':'#166534'};padding:1px 7px;border-radius:8px;font-weight:600">${tipo}</span></td>
                <td style="padding:6px 10px;font-weight:600">${desc}</td>
                <td style="padding:6px 10px;color:#6b7280">${categ}</td>
                <td style="padding:6px 10px;font-family:monospace">${code}</td>
                <td style="padding:6px 10px;font-family:monospace;font-size:0.75rem">${serie}</td>
                <td style="padding:6px 10px;color:#059669;font-weight:600">${precio}</td>
            </tr>`;
        }).join('');

        document.getElementById('acta-print-body').innerHTML = `
        <div style="font-family:'Segoe UI',Arial,sans-serif;color:#111;padding:8px">

          <!-- ENCABEZADO -->
          <div style="display:flex;align-items:center;gap:16px;border-bottom:3px solid #1e3a5f;padding-bottom:14px;margin-bottom:16px">
            <div style="width:64px;height:64px;background:#1e3a5f;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;flex-shrink:0">🏛️</div>
            <div style="flex:1">
              <div style="font-size:1rem;font-weight:800;color:#1e3a5f;line-height:1.2">ALCALDÍA MUNICIPAL</div>
              <div style="font-size:0.82rem;color:#374151">Oficina de Almacén e Inventarios</div>
              <div style="font-size:0.75rem;color:#6b7280">Sistema de Gestión de Activos y Suministros</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:0.62rem;color:#6b7280;text-transform:uppercase;letter-spacing:1px">N° de Acta</div>
              <div style="font-size:1.5rem;font-weight:800;color:#1e3a5f">${d.acta_number}</div>
              <div style="font-size:0.72rem;color:#6b7280">Fecha: ${fEntrega}</div>
              ${d.is_returned
                ? `<div style="font-size:0.72rem;color:#dc2626;font-weight:700">⚠️ DEVUELTO: ${fDevuelto}</div>`
                : `<div style="font-size:0.72rem;color:#059669;font-weight:700">● VIGENTE</div>`}
            </div>
          </div>

          <!-- FUNCIONARIO RECEPTOR -->
          <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:14px">
            <div style="background:#1e3a5f;color:white;padding:7px 14px;font-size:0.75rem;font-weight:700;letter-spacing:0.5px">FUNCIONARIO RECEPTOR</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
              <table style="width:100%;border-collapse:collapse;font-size:0.8rem">
                <tr><td style="padding:6px 12px;color:#6b7280;width:40%">Nombre completo</td><td style="padding:6px 12px;font-weight:700">${d.official?.full_name||'—'}</td></tr>
                <tr style="background:#f9fafb"><td style="padding:6px 12px;color:#6b7280">Identificación</td><td style="padding:6px 12px">${d.official?.document_type||''} ${d.official?.document_number||''}</td></tr>
              </table>
              <table style="width:100%;border-collapse:collapse;font-size:0.8rem">
                <tr><td style="padding:6px 12px;color:#6b7280;width:40%">Cargo</td><td style="padding:6px 12px">${d.official?.position||'—'}</td></tr>
                <tr style="background:#f9fafb"><td style="padding:6px 12px;color:#6b7280">Dependencia</td><td style="padding:6px 12px;font-weight:600">${d.official?.office?.name||'—'}</td></tr>
              </table>
            </div>
          </div>

          <!-- TABLA DE BIENES ENTREGADOS -->
          <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:14px">
            <div style="background:#1e3a5f;color:white;padding:7px 14px;font-size:0.75rem;font-weight:700;letter-spacing:0.5px">
              BIENES ENTREGADOS — ${items.length} ítem(s)
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.79rem">
              <thead>
                <tr style="background:#f1f5f9">
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">#</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">TIPO</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">DESCRIPCIÓN</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">CATEGORÍA</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">CÓD / CANT.</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">N° SERIE</th>
                  <th style="padding:7px 10px;text-align:left;color:#475569;font-size:0.68rem;letter-spacing:0.5px">VALOR</th>
                </tr>
              </thead>
              <tbody>${itemsRowsHtml}</tbody>
            </table>
          </div>

          <!-- DETALLES DE LA ENTREGA -->
          <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:18px">
            <div style="background:#1e3a5f;color:white;padding:7px 14px;font-size:0.75rem;font-weight:700;letter-spacing:0.5px">DETALLES DE LA ENTREGA</div>
            <table style="width:100%;border-collapse:collapse;font-size:0.8rem">
              <tr>
                <td style="padding:6px 12px;color:#6b7280;width:22%">Entregado por</td>
                <td style="padding:6px 12px;font-weight:600;width:28%">${d.delivered_by}</td>
                <td style="padding:6px 12px;color:#6b7280;width:22%">Fecha de entrega</td>
                <td style="padding:6px 12px;width:28%">${fEntrega}</td>
              </tr>
              ${d.notes ? `<tr style="background:#f9fafb"><td style="padding:6px 12px;color:#6b7280">Observaciones</td><td colspan="3" style="padding:6px 12px">${d.notes}</td></tr>` : ''}
              ${d.is_returned ? `<tr><td style="padding:6px 12px;color:#dc2626;font-weight:700">Devuelto el</td><td style="padding:6px 12px;color:#dc2626">${fDevuelto}</td><td style="padding:6px 12px;color:#6b7280">Motivo</td><td style="padding:6px 12px">${d.return_notes||'—'}</td></tr>` : ''}
            </table>
          </div>

          <!-- FIRMAS FÍSICAS -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:28px">
            <div style="text-align:center">
              <div style="height:70px;border-bottom:2px solid #1e3a5f;margin-bottom:10px">
                <!-- Espacio para firma física -->
              </div>
              <div style="font-size:0.82rem;color:#111;font-weight:700">${d.delivered_by}</div>
              <div style="font-size:0.7rem;color:#6b7280;margin-top:2px">Funcionario que Entrega</div>
              <div style="font-size:0.7rem;color:#6b7280">Oficina de Almacén e Inventarios</div>
              <div style="font-size:0.68rem;color:#9ca3af;margin-top:3px">C.C. / Firma y Sello</div>
            </div>
            <div style="text-align:center">
              <div style="height:70px;border-bottom:2px solid #1e3a5f;margin-bottom:10px">
                <!-- Espacio para firma física -->
              </div>
              <div style="font-size:0.82rem;color:#111;font-weight:700">${d.official?.full_name||'—'}</div>
              <div style="font-size:0.7rem;color:#6b7280;margin-top:2px">${d.official?.position||'Funcionario Receptor'}</div>
              <div style="font-size:0.7rem;color:#6b7280">${d.official?.office?.name||''}</div>
              <div style="font-size:0.68rem;color:#9ca3af;margin-top:3px">C.C. ${d.official?.document_number||''} · Firma y Sello</div>
            </div>
          </div>

          <!-- PIE DE PÁGINA -->
          <div style="margin-top:20px;padding-top:10px;border-top:1px dashed #d1d5db;text-align:center">
            <p style="font-size:0.66rem;color:#9ca3af">Generado el ${today} · MuniGest — Sistema de Gestión de Inventario y Activos Fijos · Documento con validez administrativa</p>
          </div>
        </div>`;

        openModal('acta-print-modal');
    } catch(e){ console.error(e); showToast('Error al cargar el acta','error'); }
}

/* ============================================================
   ALERTS
============================================================ */
async function loadAlerts() {
    showLoader('alerts');
    try {
        const res = await apiFetch('/alerts/low-stock');
        const items = res.data || [];
        const badge = document.getElementById('badge-alerts');
        if(items.length > 0) { badge.textContent = items.length; badge.style.display=''; }
        else { badge.style.display='none'; }
        const tbody = document.getElementById('alerts-table-body');
        if(!items.length){ tbody.innerHTML=''; document.getElementById('alerts-empty').style.display=''; return; }
        document.getElementById('alerts-empty').style.display='none';
        tbody.innerHTML = items.map(i => {
            const pct = Math.min(100, Math.round((i.stock / (i.min_stock||1)) * 100));
            const cls = i.stock === 0 ? 'stock-crit' : (i.stock < i.min_stock ? 'stock-warn' : 'stock-ok');
            const label = i.stock === 0 ? '<span class="badge badge-malo">Agotado</span>' :
                          (i.stock < i.min_stock ? '<span class="badge badge-regular">Stock Bajo</span>' :
                          '<span class="badge badge-nuevo">Normal</span>');
            return `<tr class="${i.stock<=0?'low-stock-row':''}">
                <td><strong>${i.name}</strong></td>
                <td><small>${i.category?.name||'—'}</small></td>
                <td><strong style="font-size:1.1rem">${i.stock}</strong> unid.</td>
                <td>${i.min_stock} unid.</td>
                <td><div class="stock-bar-wrap"><div class="stock-bar ${cls}" style="width:${pct}%"></div></div> <small style="margin-left:6px;color:var(--text-dim)">${pct}%</small></td>
                <td>${label}</td>
            </tr>`;
        }).join('');
    } catch(e){ console.error(e); } finally { hideLoader('alerts'); }
}

/* ============================================================
   UI HELPERS
============================================================ */
function formatDate(raw) {
    if(!raw) return '—';
    // Acepta 'YYYY-MM-DD', 'YYYY-MM-DDTHH:MM:SS...' o ISO completo
    const d = new Date(raw.length === 10 ? raw + 'T12:00:00' : raw);
    if(isNaN(d)) return raw;
    return d.toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' });
}
function badgeStatus(status) {
    const map = { nuevo:'badge-nuevo', bueno:'badge-bueno', regular:'badge-regular', malo:'badge-malo', baja:'badge-baja' };
    const labels = { nuevo:'Nuevo', bueno:'Bueno', regular:'Regular', malo:'Malo', baja:'De Baja' };
    return `<span class="badge ${map[status]||'badge-bueno'}">${labels[status]||status}</span>`;
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function showLoader(page) {
    const el = document.getElementById(page + '-loader');
    if(el) el.style.display = 'flex';
    const tbody = document.getElementById(page + '-table-body');
    if(tbody) tbody.innerHTML = '';
}
function hideLoader(page) {
    const el = document.getElementById(page + '-loader');
    if(el) el.style.display = 'none';
}
function showToast(msg, type='info') {
    const icons = { success:'✅', error:'❌', info:'ℹ️' };
    const div = document.createElement('div');
    div.className = `toast ${type}`;
    div.innerHTML = `<span>${icons[type]}</span> ${msg}`;
    document.getElementById('toast-container').appendChild(div);
    setTimeout(() => div.remove(), 3500);
}

/* ============================================================
   INIT
============================================================ */
window.addEventListener('click', e => {
    if(e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

// Cargar alerta de stock al inicio
apiFetch('/alerts/low-stock').then(r => {
    const c = r.count||0;
    const b = document.getElementById('badge-alerts');
    if(c>0){b.textContent=c;b.style.display='';} else{b.style.display='none';}
});

// Cargar dashboard al inicio
loadPage('dashboard');
</script>

<script>
/* ============================================================
   MÓDULO: ENTRADAS DE INVENTARIO
============================================================ */
let _allEntries = [], _allConsumableItems = [];

async function loadEntries() {
    showLoader('entries');
    const r = await apiFetch('/inventory-entries');
    _allEntries = r.data || [];
    renderEntries(_allEntries);
    hideLoader('entries');
}

function renderEntries(list) {
    const tbody = document.getElementById('entries-table-body');
    const empty  = document.getElementById('entries-empty');
    if (!list.length) { tbody.innerHTML=''; empty.style.display=''; return; }
    empty.style.display = 'none';
    const statusLabels = { completed:'✅ Completada', pending:'⏳ Pendiente', cancelled:'❌ Cancelada' };
    tbody.innerHTML = list.map(e => `
        <tr>
            <td><span style="font-weight:700;color:var(--primary);font-family:monospace">${e.entry_number}</span></td>
            <td>${e.entry_date}</td>
            <td>${e.supplier?.company_name || '<em style="color:var(--text-dim)">Sin proveedor</em>'}</td>
            <td>${e.invoice_number || '—'}</td>
            <td>${e.received_by}</td>
            <td><span style="background:rgba(99,102,241,0.12);color:var(--primary);padding:2px 8px;border-radius:12px;font-size:0.75rem">${e.items?.length || 0} artículo(s)</span></td>
            <td>${e.total_amount ? '$'+parseFloat(e.total_amount).toLocaleString('es-CO') : '—'}</td>
            <td>${statusLabels[e.status] || e.status}</td>
            <td>
                ${e.status !== 'cancelled' ? `<button class="action-btn delete-btn" title="Cancelar" onclick="cancelEntry(${e.id})">✕</button>` : ''}
            </td>
        </tr>`).join('');
}

function filterEntries(q) {
    const s = q.toLowerCase();
    renderEntries(_allEntries.filter(e =>
        e.entry_number.toLowerCase().includes(s) ||
        (e.supplier?.company_name||'').toLowerCase().includes(s) ||
        (e.invoice_number||'').toLowerCase().includes(s)));
}

async function openEntryModal() {
    openModal('entry-modal');
    document.getElementById('entry-items-list').innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-dim);font-size:0.82rem">⏳ Cargando...</div>';
    document.getElementById('entry-date').value = new Date().toISOString().split('T')[0];
    ['entry-received-by','entry-invoice','entry-notes'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });

    const [suppRes, invRes] = await Promise.all([
        allProviders.length ? Promise.resolve({data:allProviders}) : apiFetch('/providers'),
        apiFetch('/inventory'),
    ]);
    allProviders = suppRes.data || [];
    _allConsumableItems = (invRes.data || []); // Se muestran todos (consumibles y activos) para que la sinergia funcione

    document.getElementById('entry-supplier').innerHTML =
        '<option value="">Sin proveedor</option>' +
        allProviders.map(p => `<option value="${p.id}">${p.company_name}</option>`).join('');
    document.getElementById('entry-items-list').innerHTML = '';
    _entryItemCount = 0;
    addEntryItemRow();
}

let _entryItemCount = 0;
function addEntryItemRow() {
    const idx = ++_entryItemCount;
    const opts = _allConsumableItems.map(i => `<option value="${i.id}">${i.name} (Stock: ${i.stock})</option>`).join('');
    const row = document.createElement('div');
    row.className = 'delivery-item-row'; row.id = `eitem-${idx}`;
    row.innerHTML = `
        <select id="eitem-item-${idx}" class="form-control" style="flex:1">
            <option value="">Seleccionar artículo...</option>${opts}
        </select>
        <input type="number" id="eitem-qty-${idx}" class="form-control" value="1" min="1" placeholder="Cant." style="width:80px">
        <input type="number" id="eitem-price-${idx}" class="form-control" placeholder="Precio unit." min="0" step="0.01" style="width:110px">
        <button type="button" class="delivery-item-remove" onclick="document.getElementById('eitem-${idx}').remove()">✕</button>`;
    document.getElementById('entry-items-list').appendChild(row);
}

async function saveEntry() {
    const entry_date  = document.getElementById('entry-date').value;
    const received_by = document.getElementById('entry-received-by').value.trim();
    if (!entry_date || !received_by) { showToast('Fecha y Recibido Por son obligatorios','error'); return; }
    const rows = document.querySelectorAll('#entry-items-list .delivery-item-row');
    const items = [];
    let valid = true;
    rows.forEach(row => {
        const idx     = row.id.split('-')[1];
        const item_id = document.getElementById(`eitem-item-${idx}`)?.value;
        const qty     = parseInt(document.getElementById(`eitem-qty-${idx}`)?.value) || 0;
        const price   = parseFloat(document.getElementById(`eitem-price-${idx}`)?.value) || null;
        if (!item_id || qty < 1) { showToast('Completa todos los artículos','error'); valid=false; return; }
        items.push({ item_id, quantity: qty, unit_price: price });
    });
    if (!valid || !items.length) return;
    const btn = document.getElementById('entry-save-btn');
    btn.textContent = 'Guardando...'; btn.disabled = true;
    try {
        const res = await apiFetch('/inventory-entries', { method:'POST', body: JSON.stringify({
            supplier_id: document.getElementById('entry-supplier').value || null,
            invoice_number: document.getElementById('entry-invoice').value.trim() || null,
            entry_date, received_by,
            notes: document.getElementById('entry-notes').value || null,
            items,
        })});
        if (res.data) {
            closeModal('entry-modal');
            showToast(`Entrada ${res.data.entry_number} registrada. Stock actualizado.`, 'success');
            loadEntries();
        } else { showToast(res.message || 'Error al guardar', 'error'); }
    } catch(e) { showToast('Error de conexión','error'); }
    btn.textContent = '📥 Registrar Entrada'; btn.disabled = false;
}

async function cancelEntry(id) {
    if (!confirm('¿Cancelar esta entrada? El stock será revertido.')) return;
    await apiFetch(`/inventory-entries/${id}`, {method:'DELETE'});
    showToast('Entrada cancelada y stock revertido','success');
    loadEntries();
}

/* ============================================================
   BAJAS DE ACTIVOS
============================================================ */
let _allDisposals = [];
const DISPOSAL_REASONS = { damage:'Daño irreparable', loss:'Pérdida', obsolescence:'Obsolescencia', theft:'Hurto/Robo', transfer:'Transferencia', other:'Otro' };

async function loadDisposals() {
    showLoader('disposals');
    const r = await apiFetch('/asset-disposals');
    _allDisposals = r.data || [];
    renderDisposals(_allDisposals);
    hideLoader('disposals');
}

function renderDisposals(list) {
    const tbody = document.getElementById('disposals-table-body');
    const empty  = document.getElementById('disposals-empty');
    if (!list.length) { tbody.innerHTML=''; empty.style.display=''; return; }
    empty.style.display = 'none';
    tbody.innerHTML = list.map(d => `
        <tr>
            <td><span style="font-weight:700;color:#ef4444;font-family:monospace">${d.disposal_number}</span></td>
            <td>${d.fixed_asset?.item?.name || '—'}</td>
            <td style="font-family:monospace;font-size:0.8rem">${d.fixed_asset?.inventory_code || '—'}</td>
            <td><span style="background:rgba(239,68,68,0.1);color:#ef4444;padding:2px 8px;border-radius:12px;font-size:0.75rem">${DISPOSAL_REASONS[d.reason]||d.reason}</span></td>
            <td>${d.disposal_date}</td>
            <td>${d.authorized_by}</td>
            <td>${d.resolution_number || '—'}</td>
            <td>—</td>
        </tr>`).join('');
}

function filterDisposals(q) {
    const s = q.toLowerCase();
    renderDisposals(_allDisposals.filter(d =>
        d.disposal_number.toLowerCase().includes(s) ||
        (d.fixed_asset?.item?.name||'').toLowerCase().includes(s)));
}

async function openDisposalModal() {
    if (!allAssets.length) { const r = await apiFetch('/fixed-assets'); allAssets = r.data||[]; }
    const available = allAssets.filter(a => !a.is_disposed);
    document.getElementById('disposal-asset').innerHTML =
        '<option value="">Seleccionar activo...</option>' +
        available.map(a => `<option value="${a.id}">${a.inventory_code} – ${a.item?.name||''}</option>`).join('');
    document.getElementById('disposal-date').value = new Date().toISOString().split('T')[0];
    ['disposal-authorized','disposal-processed','disposal-resolution','disposal-description'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    openModal('disposal-modal');
}

async function saveDisposal() {
    const fixed_asset_id = document.getElementById('disposal-asset').value;
    const authorized_by  = document.getElementById('disposal-authorized').value.trim();
    const processed_by   = document.getElementById('disposal-processed').value.trim();
    const disposal_date  = document.getElementById('disposal-date').value;
    if (!fixed_asset_id || !authorized_by || !processed_by || !disposal_date) { showToast('Completa los campos obligatorios','error'); return; }
    const btn = document.getElementById('disposal-save-btn');
    btn.textContent='Guardando...'; btn.disabled=true;
    try {
        const res = await apiFetch('/asset-disposals', { method:'POST', body: JSON.stringify({
            fixed_asset_id, authorized_by, processed_by, disposal_date,
            reason: document.getElementById('disposal-reason').value,
            resolution_number: document.getElementById('disposal-resolution').value||null,
            description: document.getElementById('disposal-description').value||null,
        })});
        if (res.data) {
            closeModal('disposal-modal');
            showToast(`Baja ${res.data.disposal_number} registrada`, 'success');
            allAssets = [];
            loadDisposals();
        } else { showToast(res.message||'Error','error'); }
    } catch(e) { showToast('Error de conexión','error'); }
    btn.textContent='🗑️ Registrar Baja'; btn.disabled=false;
}

/* ============================================================
   TRASLADOS
============================================================ */
let _allTransfers = [];

async function loadTransfers() {
    showLoader('transfers');
    const r = await apiFetch('/asset-transfers');
    _allTransfers = r.data || [];
    renderTransfers(_allTransfers);
    hideLoader('transfers');
}

function renderTransfers(list) {
    const tbody = document.getElementById('transfers-table-body');
    const empty  = document.getElementById('transfers-empty');
    if (!list.length) { tbody.innerHTML=''; empty.style.display=''; return; }
    empty.style.display = 'none';
    tbody.innerHTML = list.map(t => `
        <tr>
            <td><span style="font-weight:700;color:var(--primary);font-family:monospace">${t.transfer_number}</span></td>
            <td>${t.fixed_asset?.item?.name||'—'} <span style="font-size:0.73rem;color:var(--text-dim)">${t.fixed_asset?.inventory_code||''}</span></td>
            <td>${t.from_office?.name||'—'}</td>
            <td style="font-weight:600">${t.to_office?.name||'—'}</td>
            <td>${t.transferred_by}</td>
            <td>${t.transfer_date}</td>
            <td><span style="background:rgba(34,197,94,0.12);color:#22c55e;padding:2px 8px;border-radius:12px;font-size:0.75rem">✅ Completado</span></td>
            <td>—</td>
        </tr>`).join('');
}

function filterTransfers(q) {
    const s = q.toLowerCase();
    renderTransfers(_allTransfers.filter(t =>
        t.transfer_number.toLowerCase().includes(s) ||
        (t.fixed_asset?.item?.name||'').toLowerCase().includes(s)));
}

async function openTransferModal() {
    const [assetsRes, officesRes] = await Promise.all([
        allAssets.length ? Promise.resolve({data:allAssets}) : apiFetch('/fixed-assets'),
        allOffices.length ? Promise.resolve({data:allOffices}) : apiFetch('/offices'),
    ]);
    allAssets  = assetsRes.data || [];
    allOffices = officesRes.data || [];
    const available = allAssets.filter(a => !a.is_disposed);
    document.getElementById('transfer-asset').innerHTML =
        '<option value="">Seleccionar activo...</option>' +
        available.map(a => `<option value="${a.id}">${a.inventory_code} – ${a.item?.name||''}</option>`).join('');
    const officesHtml = allOffices.map(o=>`<option value="${o.id}">${o.name}</option>`).join('');
    document.getElementById('transfer-from').innerHTML = '<option value="">Origen...</option>'+officesHtml;
    document.getElementById('transfer-to').innerHTML   = '<option value="">Destino...</option>'+officesHtml;
    document.getElementById('transfer-date').value = new Date().toISOString().split('T')[0];
    ['transfer-by','transfer-received','transfer-notes'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    openModal('transfer-modal');
}

async function saveTransfer() {
    const fixed_asset_id = document.getElementById('transfer-asset').value;
    const to_office_id   = document.getElementById('transfer-to').value;
    const transferred_by = document.getElementById('transfer-by').value.trim();
    const transfer_date  = document.getElementById('transfer-date').value;
    if (!fixed_asset_id || !to_office_id || !transferred_by || !transfer_date) { showToast('Completa los campos obligatorios','error'); return; }
    const btn = document.getElementById('transfer-save-btn');
    btn.textContent='Guardando...'; btn.disabled=true;
    try {
        const res = await apiFetch('/asset-transfers', { method:'POST', body: JSON.stringify({
            fixed_asset_id, to_office_id, transferred_by, transfer_date,
            from_office_id: document.getElementById('transfer-from').value||null,
            received_by: document.getElementById('transfer-received').value||null,
            notes: document.getElementById('transfer-notes').value||null,
        })});
        if (res.data) {
            closeModal('transfer-modal');
            showToast(`Traslado ${res.data.transfer_number} registrado`,'success');
            loadTransfers();
        } else { showToast(res.message||'Error','error'); }
    } catch(e){showToast('Error de conexión','error');}
    btn.textContent='🔄 Registrar Traslado'; btn.disabled=false;
}

/* ============================================================
   MANTENIMIENTO
============================================================ */
let _allMaintenances = [], _maintenanceStatusFilter = '';
const MAINT_TYPES  = { preventive:'🔵 Preventivo', corrective:'🔴 Correctivo', upgrade:'🟢 Mejora' };
const MAINT_STATUS = { scheduled:'📅 Programado', in_progress:'🔄 En Proceso', completed:'✅ Completado', cancelled:'❌ Cancelado' };

async function loadMaintenances() {
    showLoader('maintenances');
    const r = await apiFetch('/asset-maintenances');
    _allMaintenances = r.data || [];
    renderMaintenances(_allMaintenances);
    hideLoader('maintenances');
}

function renderMaintenances(list) {
    const tbody = document.getElementById('maintenances-table-body');
    const empty  = document.getElementById('maintenances-empty');
    const filtered = _maintenanceStatusFilter ? list.filter(m=>m.status===_maintenanceStatusFilter) : list;
    if (!filtered.length) { tbody.innerHTML=''; empty.style.display=''; return; }
    empty.style.display='none';
    tbody.innerHTML = filtered.map(m => `
        <tr>
            <td>${m.fixed_asset?.item?.name||'—'} <span style="font-size:0.72rem;color:var(--text-dim)">${m.fixed_asset?.inventory_code||''}</span></td>
            <td>${MAINT_TYPES[m.type]||m.type}</td>
            <td>${m.maintenance_date}</td>
            <td>${m.next_maintenance_date||'—'}</td>
            <td>${m.technician||'—'}</td>
            <td>${m.cost ? '$'+parseFloat(m.cost).toLocaleString('es-CO') : '—'}</td>
            <td><span style="font-size:0.75rem">${MAINT_STATUS[m.status]||m.status}</span></td>
            <td><button class="action-btn delete-btn" onclick="deleteMaintenance(${m.id})">🗑</button></td>
        </tr>`).join('');
}

function filterMaintenances(q) {
    const s = q.toLowerCase();
    renderMaintenances(_allMaintenances.filter(m =>
        (m.fixed_asset?.item?.name||'').toLowerCase().includes(s) ||
        (m.technician||'').toLowerCase().includes(s)));
}
function filterMaintenancesByStatus(v) { _maintenanceStatusFilter=v; renderMaintenances(_allMaintenances); }

async function openMaintenanceModal() {
    if (!allAssets.length) { const r = await apiFetch('/fixed-assets'); allAssets = r.data||[]; }
    const available = allAssets.filter(a => !a.is_disposed);
    document.getElementById('maintenance-asset').innerHTML =
        '<option value="">Seleccionar activo...</option>' +
        available.map(a=>`<option value="${a.id}">${a.inventory_code} – ${a.item?.name||''}</option>`).join('');
    document.getElementById('maintenance-date').value = new Date().toISOString().split('T')[0];
    ['maintenance-next','maintenance-technician','maintenance-description'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('maintenance-cost').value='';
    openModal('maintenance-modal');
}

async function saveMaintenance() {
    const fixed_asset_id   = document.getElementById('maintenance-asset').value;
    const description      = document.getElementById('maintenance-description').value.trim();
    const maintenance_date = document.getElementById('maintenance-date').value;
    if (!fixed_asset_id || !description || !maintenance_date) { showToast('Activo, fecha y descripción son obligatorios','error'); return; }
    const btn = document.getElementById('maintenance-save-btn');
    btn.textContent='Guardando...'; btn.disabled=true;
    try {
        const res = await apiFetch('/asset-maintenances', { method:'POST', body: JSON.stringify({
            fixed_asset_id, description, maintenance_date,
            type:   document.getElementById('maintenance-type').value,
            status: document.getElementById('maintenance-status').value,
            next_maintenance_date: document.getElementById('maintenance-next').value||null,
            technician: document.getElementById('maintenance-technician').value||null,
            cost: document.getElementById('maintenance-cost').value||null,
        })});
        if (res.data) { closeModal('maintenance-modal'); showToast('Mantenimiento registrado','success'); loadMaintenances(); }
        else { showToast(res.message||'Error','error'); }
    } catch(e){showToast('Error de conexión','error');}
    btn.textContent='🔧 Guardar'; btn.disabled=false;
}

async function deleteMaintenance(id) {
    if (!confirm('¿Eliminar este registro?')) return;
    await apiFetch(`/asset-maintenances/${id}`, {method:'DELETE'});
    showToast('Eliminado','success'); loadMaintenances();
}

/* ============================================================
   SOLICITUDES DE SUMINISTROS
============================================================ */
let _allSupplyRequests=[], _supplyStatusFilter='', _currentSR=null;
const SR_STATUS_MAP = {
    pending:   '<span style="background:rgba(234,179,8,0.15);color:#eab308;padding:2px 8px;border-radius:12px;font-size:0.74rem">⏳ Pendiente</span>',
    approved:  '<span style="background:rgba(34,197,94,0.12);color:#22c55e;padding:2px 8px;border-radius:12px;font-size:0.74rem">✅ Aprobada</span>',
    dispatched:'<span style="background:rgba(99,102,241,0.12);color:var(--primary);padding:2px 8px;border-radius:12px;font-size:0.74rem">📦 Despachada</span>',
    rejected:  '<span style="background:rgba(239,68,68,0.12);color:#ef4444;padding:2px 8px;border-radius:12px;font-size:0.74rem">❌ Rechazada</span>',
};

async function loadSupplyRequests() {
    showLoader('supply-requests');
    const r = await apiFetch('/supply-requests');
    _allSupplyRequests = r.data || [];
    renderSupplyRequests(_allSupplyRequests);
    hideLoader('supply-requests');
    const pending = _allSupplyRequests.filter(s=>s.status==='pending').length;
    const badge = document.getElementById('badge-supply');
    if(pending>0){badge.textContent=pending;badge.style.display='';}else{badge.style.display='none';}
}

function renderSupplyRequests(list) {
    const tbody = document.getElementById('supply-requests-table-body');
    const empty  = document.getElementById('supply-requests-empty');
    const filtered = _supplyStatusFilter ? list.filter(s=>s.status===_supplyStatusFilter) : list;
    if (!filtered.length) { tbody.innerHTML=''; empty.style.display=''; return; }
    empty.style.display='none';
    tbody.innerHTML = filtered.map(s => `
        <tr>
            <td><span style="font-weight:700;color:var(--primary);font-family:monospace">${s.request_number}</span></td>
            <td>${s.office?.name||'—'}</td>
            <td>${s.requested_by}</td>
            <td>${s.request_date}</td>
            <td>${s.needed_by||'—'}</td>
            <td><span style="background:rgba(99,102,241,0.12);color:var(--primary);padding:2px 8px;border-radius:12px;font-size:0.75rem">${s.items?.length||0} ítem(s)</span></td>
            <td>${SR_STATUS_MAP[s.status]||s.status}</td>
            <td>
                ${(s.status==='pending'||s.status==='approved') ? `<button class="action-btn view-btn" title="Gestionar" onclick="openDispatchModal(${s.id})">⚙️</button>` : ''}
            </td>
        </tr>`).join('');
}

function filterSupplyRequests(q) {
    const s = q.toLowerCase();
    renderSupplyRequests(_allSupplyRequests.filter(r =>
        r.request_number.toLowerCase().includes(s)||
        (r.office?.name||'').toLowerCase().includes(s)||
        r.requested_by.toLowerCase().includes(s)));
}
function filterSupplyByStatus(v) { _supplyStatusFilter=v; renderSupplyRequests(_allSupplyRequests); }

async function openSupplyRequestModal() {
    if (!allOffices.length) { const r = await apiFetch('/offices'); allOffices = r.data||[]; }
    const invRes = await apiFetch('/inventory');
    _allConsumableItems = (invRes.data||[]).filter(i=>!i.is_asset);
    document.getElementById('sr-office').innerHTML =
        '<option value="">Seleccionar...</option>'+allOffices.map(o=>`<option value="${o.id}">${o.name}</option>`).join('');
    document.getElementById('sr-date').value = new Date().toISOString().split('T')[0];
    ['sr-requested-by','sr-notes'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('sr-needed-by').value='';
    document.getElementById('sr-items-list').innerHTML='';
    _srItemCount=0; addSRItemRow();
    openModal('supply-request-modal');
}

let _srItemCount=0;
function addSRItemRow() {
    const idx = ++_srItemCount;
    const opts = _allConsumableItems.map(i=>`<option value="${i.id}">${i.name} (Stock: ${i.stock})</option>`).join('');
    const row = document.createElement('div');
    row.className='delivery-item-row'; row.id=`sritem-${idx}`;
    row.innerHTML=`
        <select id="sritem-item-${idx}" class="form-control" style="flex:1">
            <option value="">Seleccionar artículo...</option>${opts}
        </select>
        <input type="number" id="sritem-qty-${idx}" class="form-control" value="1" min="1" style="width:80px">
        <button type="button" class="delivery-item-remove" onclick="document.getElementById('sritem-${idx}').remove()">✕</button>`;
    document.getElementById('sr-items-list').appendChild(row);
}

async function saveSupplyRequest() {
    const office_id    = document.getElementById('sr-office').value;
    const requested_by = document.getElementById('sr-requested-by').value.trim();
    const request_date = document.getElementById('sr-date').value;
    if (!office_id||!requested_by||!request_date) { showToast('Dependencia, solicitante y fecha son obligatorios','error'); return; }
    const rows = document.querySelectorAll('#sr-items-list .delivery-item-row');
    const items=[]; let valid=true;
    rows.forEach(row=>{
        const idx=row.id.split('-')[1];
        const item_id=document.getElementById(`sritem-item-${idx}`)?.value;
        const qty=parseInt(document.getElementById(`sritem-qty-${idx}`)?.value)||0;
        if(!item_id||qty<1){showToast('Completa todos los artículos','error');valid=false;return;}
        items.push({item_id,requested_quantity:qty});
    });
    if(!valid||!items.length) return;
    const btn=document.getElementById('sr-save-btn');
    btn.textContent='Enviando...'; btn.disabled=true;
    try {
        const res = await apiFetch('/supply-requests',{method:'POST',body:JSON.stringify({
            office_id, requested_by, request_date,
            needed_by: document.getElementById('sr-needed-by').value||null,
            notes: document.getElementById('sr-notes').value||null, items,
        })});
        if(res.data){ closeModal('supply-request-modal'); showToast(`Solicitud ${res.data.request_number} enviada`,'success'); loadSupplyRequests(); }
        else { showToast(res.message||'Error','error'); }
    } catch(e){ showToast('Error','error'); }
    btn.textContent='📋 Enviar Solicitud'; btn.disabled=false;
}

async function openDispatchModal(id) {
    const r = await apiFetch(`/supply-requests/${id}`);
    _currentSR = r.data;
    document.getElementById('dispatch-sr-ref').textContent = _currentSR.request_number;
    const isApproved = _currentSR.status==='approved';
    document.getElementById('dispatch-approve-btn').style.display  = isApproved?'none':'';
    document.getElementById('dispatch-dispatch-btn').style.display = isApproved?''  :'none';
    document.getElementById('dispatch-by-wrap').style.display      = isApproved ? '' : 'none';
    document.getElementById('reject-reason-wrap').style.display    = 'none';
    const area = document.getElementById('dispatch-items-area');
    area.innerHTML = `<table style="width:100%;border-collapse:collapse;font-size:0.82rem;margin-bottom:12px">
        <thead><tr style="background:rgba(255,255,255,0.05)"><th style="padding:6px">Artículo</th><th style="padding:6px;text-align:center">Solicitado</th><th style="padding:6px;text-align:center">Cantidad</th></tr></thead>
        <tbody>${_currentSR.items.map(item=>`
            <tr><td style="padding:6px">${item.item?.name||'—'}</td>
            <td style="padding:6px;text-align:center">${item.requested_quantity}</td>
            <td style="padding:6px;text-align:center"><input type="number" id="dispatch-qty-${item.id}" class="form-control" value="${item.requested_quantity}" min="0" style="width:70px;display:inline-block"></td></tr>`).join('')}
        </tbody></table>`;
    openModal('dispatch-modal');
}

async function processSupplyAction(action) {
    if(!_currentSR) return;
    const items = _currentSR.items.map(item => {
        const qty = parseInt(document.getElementById(`dispatch-qty-${item.id}`)?.value)||0;
        return action==='approve'
            ? {supply_request_item_id:item.id, approved_quantity:qty}
            : {supply_request_item_id:item.id, dispatched_quantity:qty};
    });
    try {
        const res = await apiFetch(`/supply-requests/${_currentSR.id}`,{method:'PATCH',body:JSON.stringify({
            action, items,
            dispatched_by: document.getElementById('dispatch-by')?.value||null,
            rejection_reason: document.getElementById('reject-reason')?.value||null,
        })});
        if(res.data){ closeModal('dispatch-modal'); showToast('Solicitud actualizada','success'); loadSupplyRequests(); }
        else { showToast(res.message||'Error','error'); }
    } catch(e){showToast('Error','error');}
}

/* ============================================================
   KARDEX
============================================================ */
async function openKardex(itemId, itemName) {
    document.getElementById('kardex-item-name').textContent = itemName;
    document.getElementById('kardex-body').innerHTML = '<div style="text-align:center;padding:24px">⏳ Cargando movimientos...</div>';
    openModal('kardex-modal');
    try {
        const r = await apiFetch(`/kardex/${itemId}`);
        const movs = r.movements || [];
        const TYPE_COLOR = { entrada:'#22c55e', salida:'#ef4444', devolucion:'#f59e0b' };
        const TYPE_LABEL = { entrada:'📥 ENTRADA', salida:'📤 SALIDA', devolucion:'↩️ DEV.' };
        document.getElementById('kardex-body').innerHTML = `
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px">
                <div style="background:#f1f5f9;padding:8px 14px;border-radius:8px;font-size:0.82rem"><strong>${r.item.name}</strong></div>
                <div style="background:#dcfce7;padding:8px 14px;border-radius:8px;font-size:0.82rem;font-weight:700;color:#166534">Stock: ${r.current_stock}</div>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.79rem;color:#111">
                <thead><tr style="background:#1e3a5f;color:white">
                    <th style="padding:7px 10px">Fecha</th><th style="padding:7px 10px">Tipo</th>
                    <th style="padding:7px 10px">Referencia</th><th style="padding:7px 10px">Descripción</th>
                    <th style="padding:7px 10px;text-align:center">Entrada</th>
                    <th style="padding:7px 10px;text-align:center">Salida</th>
                    <th style="padding:7px 10px;text-align:center">Saldo</th>
                </tr></thead>
                <tbody>${movs.length ? movs.map((m,i) => `
                    <tr style="background:${i%2===0?'#fff':'#f9fafb'}">
                        <td style="padding:6px 10px">${m.date||'—'}</td>
                        <td style="padding:6px 10px"><span style="color:${TYPE_COLOR[m.type]};font-weight:700;font-size:0.72rem">${TYPE_LABEL[m.type]||m.type}</span></td>
                        <td style="padding:6px 10px;font-family:monospace;font-size:0.73rem">${m.reference}</td>
                        <td style="padding:6px 10px">${m.description}</td>
                        <td style="padding:6px 10px;text-align:center;color:#166534;font-weight:${m.entrada>0?700:400}">${m.entrada>0?m.entrada:'—'}</td>
                        <td style="padding:6px 10px;text-align:center;color:#991b1b;font-weight:${m.salida>0?700:400}">${m.salida>0?m.salida:'—'}</td>
                        <td style="padding:6px 10px;text-align:center;font-weight:700;color:${m.saldo<=0?'#dc2626':'#1e3a5f'}">${m.saldo}</td>
                    </tr>`).join('') : '<tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af">Sin movimientos aún</td></tr>'}
                </tbody>
            </table>`;
    } catch(e) { document.getElementById('kardex-body').innerHTML='<div style="color:#ef4444;text-align:center;padding:20px">Error al cargar</div>'; }
}

/* ============================================================
     SISTEMA DE REPORTES Y AUDITORÍA
============================================================ */
async function generateReport(type) {
    const start = document.getElementById('report-start-date').value;
    const end   = document.getElementById('report-end-date').value;
    
    const container = document.getElementById('report-preview-container');
    container.innerHTML = '<div class="spinner"></div><p style="margin-top:10px">Generando reporte...</p>';

    try {
        const url = `/api/reports/${type}?start=${start}&end=${end}`;
        const res = await apiFetch(url);
        
        if(!res.data || (Array.isArray(res.data) && res.data.length === 0)) {
            container.innerHTML = `<div style="text-align:center"><span style="font-size:3rem">📑</span><p>No se encontraron datos para los filtros seleccionados.</p></div>`;
            return;
        }

        let html = `
            <div class="print-header" style="text-align:center; border-bottom:2px solid #000; padding-bottom:15px; margin-bottom:20px; color:#000">
                <h2 style="margin:0; font-size:1.6rem">ALCALDÍA MUNICIPAL</h2>
                <h3 style="margin:5px 0; font-weight:normal; font-size:1.1rem">Sistema de Gestión Administrativa - MuniGest</h3>
                <h4 style="margin:10px 0; font-size:1.3rem; text-decoration:underline;">${res.title.toUpperCase()}</h4>
                <p style="font-size:0.85rem">Generado el: ${new Date().toLocaleString()} | Período: ${start||'Siempre'} - ${end||'Hoy'}</p>
            </div>
            <table style="width:100%; border-collapse:collapse; margin-bottom:30px; color:#000">
        `;

        if(type === 'inventory-stock') {
            html += `<thead><tr style="background:#f0f0f0"><th style="border:1px solid #000;padding:8px">Código</th><th style="border:1px solid #000;padding:8px">Artículo</th><th style="border:1px solid #000;padding:8px">Categoría</th><th style="border:1px solid #000;padding:8px">Ubicación</th><th style="border:1px solid #000;padding:8px;text-align:right">Stock Actual</th><th style="border:1px solid #000;padding:8px;text-align:right">Ctd Mínima</th></tr></thead><tbody>`;
            res.data.forEach(i => {
                html += `<tr>
                    <td style="border:1px solid #000;padding:8px">${i.code || '-'}</td>
                    <td style="border:1px solid #000;padding:8px">${i.name}</td>
                    <td style="border:1px solid #000;padding:8px">${i.category?.name || '-'}</td>
                    <td style="border:1px solid #000;padding:8px">${i.location || 'Almacén Central'}</td>
                    <td style="border:1px solid #000;padding:8px;text-align:right;font-weight:bold">${i.stock}</td>
                    <td style="border:1px solid #000;padding:8px;text-align:right">${i.min_stock || 0}</td>
                </tr>`;
            });
        } else if(type === 'assets-by-office') {
            html += `<thead><tr style="background:#f0f0f0"><th style="border:1px solid #000;padding:8px">Dependencia</th><th style="border:1px solid #000;padding:8px">Código</th><th style="border:1px solid #000;padding:8px">Activo</th><th style="border:1px solid #000;padding:8px">Serie</th><th style="border:1px solid #000;padding:8px">Custodio</th><th style="border:1px solid #000;padding:8px">Estado</th></tr></thead><tbody>`;
            Object.keys(res.data).forEach(officeName => {
                res.data[officeName].forEach(a => {
                    html += `<tr>
                        <td style="border:1px solid #000;padding:8px"><strong>${officeName}</strong></td>
                        <td style="border:1px solid #000;padding:8px">${a.inventory_code}</td>
                        <td style="border:1px solid #000;padding:8px">${a.item?.name || '-'}</td>
                        <td style="border:1px solid #000;padding:8px">${a.serial_number || 'S/N'}</td>
                        <td style="border:1px solid #000;padding:8px">${a.active_assignment?.custodian_name || '-'}</td>
                        <td style="border:1px solid #000;padding:8px">${a.status.toUpperCase()}</td>
                    </tr>`;
                });
            });
        } else if(type === 'movements') {
            html += `<thead><tr style="background:#f0f0f0"><th style="border:1px solid #000;padding:8px">Fecha</th><th style="border:1px solid #000;padding:8px">Tipo</th><th style="border:1px solid #000;padding:8px">Referencia</th><th style="border:1px solid #000;padding:8px">Descripción</th><th style="border:1px solid #000;padding:8px;text-align:right">Ctd</th></tr></thead><tbody>`;
            res.data.forEach(m => {
                html += `<tr>
                    <td style="border:1px solid #000;padding:8px">${new Date(m.date).toLocaleDateString()}</td>
                    <td style="border:1px solid #000;padding:8px"><strong>${m.type}</strong></td>
                    <td style="border:1px solid #000;padding:8px">${m.ref}</td>
                    <td style="border:1px solid #000;padding:8px">${m.desc}</td>
                    <td style="border:1px solid #000;padding:8px;text-align:right">${m.items || '-'}</td>
                </tr>`;
            });
        }

        html += `</tbody></table>
            <div style="margin-top:50px; display:flex; justify-content:space-around; color:#000">
                <div style="text-align:center">
                    <p style="margin-bottom:60px">__________________________</p>
                    <p style="font-size:0.9rem">Firma Responsable Almacén</p>
                </div>
                <div style="text-align:center">
                    <p style="margin-bottom:60px">__________________________</p>
                    <p style="font-size:0.9rem">Firma Auditor Municipal</p>
                </div>
            </div>
            <div style="text-align:center; margin-top:30px; font-size:0.7rem; color:#666">
                *** Documento oficial generado automáticamente desde MuniGest ***
            </div>
        `;

        container.innerHTML = `
            <div style="width:100%; border:1px solid var(--border); padding:24px; background:#fff; border-radius:12px; transform:scale(0.95); transform-origin:top; transition:all 0.3s">
                ${html}
            </div>
            <div style="margin-top:20px; display:flex; gap:12px">
                <button class="btn btn-primary" onclick="window._currentReportHtml=\`${html.replace(/`/g, '\\`')}\`; openPrintModal()">🖨️ Imprimir / Guardar PDF</button>
                <button class="btn btn-secondary" onclick="generateReport('${type}')">🔄 Actualizar</button>
            </div>`;
        
        window._currentReportHtml = html;

    } catch (err) {
        console.error(err);
        container.innerHTML = `<div style="color:var(--danger); text-align:center"><span style="font-size:3rem">⚠️</span><p>Error al generar reporte: ${err.message}</p></div>`;
    }
}

function openPrintModal() {
    document.getElementById('report-print-body').innerHTML = window._currentReportHtml;
    openModal('report-print-modal');
}

</script>
@endsection
