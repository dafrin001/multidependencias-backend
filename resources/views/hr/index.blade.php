@extends('layouts.admin')

@section('title', 'Talento Humano')
@section('page_title', 'Gestión de Talento Humano')
@section('page_subtitle', 'Servidores públicos, nómina, bienestar y SST')

@section('styles')
<style>
    :root {
        --hr-primary: #0ea5e9;
        --hr-secondary: #6366f1;
        --hr-success: #10b981;
        --hr-warning: #f59e0b;
        --hr-danger: #ef4444;
        --bg-card: rgba(15, 23, 42, 0.6);
        --border: rgba(255, 255, 255, 0.08);
    }

    /* GRID DE TABS (2x4 simétrico) */
    .hr-tabs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 8px;
        margin-bottom: 24px;
    }
    .hr-tab {
        background: var(--bg-card);
        border: 1px solid var(--border);
        padding: 10px 4px; /* Reduced from 12x6 */
        border-radius: 12px; /* Smaller radius */
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        backdrop-filter: blur(10px);
    }
    .hr-tab:hover {
        background: rgba(14, 165, 233, 0.1);
        border-color: var(--hr-primary);
        transform: translateY(-2px);
    }
    .hr-tab.active {
        background: linear-gradient(135deg, var(--hr-primary), var(--hr-secondary));
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    }
    .hr-tab .tab-icon { font-size: 1.1rem; } /* Reduced from 1.2 */
    .hr-tab .tab-label { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    /* PAGINAS */
    .hr-page { display: none; width: 100%; }
    .hr-page.active { display: block; animation: slideUp 0.3s ease forwards; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* STATS CRITICALS */
    .hr-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .hr-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px; /* Reduced from 16 */
        padding: 14px; /* Reduced from 20 */
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .hr-stat-val { font-size: 1.3rem; font-weight: 800; font-family: 'Outfit', sans-serif; display: block; }
    .hr-stat-label { font-size: 0.7rem; color: #94a3b8; }

    /* TABLES */
    .hr-table-container {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px; /* Reduced from 16 */
        overflow: hidden;
    }
    .hr-table { width: 100%; border-collapse: collapse; }
    .hr-table th {
        background: rgba(255,255,255,0.03);
        padding: 10px 14px; /* Reduced from 14x20 */
        text-align: left;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #94a3b8;
        border-bottom: 1px solid var(--border);
    }
    .hr-table td { padding: 10px 14px; font-size: 0.8rem; border-bottom: 1px solid var(--border); }
    .hr-table tr:hover { background: rgba(255,255,255,0.02); }

    /* MODALES */
    .hr-modal {
        display: none;
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(8px);
        z-index: 1100;
        align-items: center; justify-content: center;
        padding: 20px;
    }
    .hr-modal-content {
        background: #1e293b;
        padding: 24px; /* Reduced from 40 */
        border-radius: 16px;
        width: 100%;
        max-width: 600px;
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    }

    /* BADGES */
    .badge { padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
    .badge-carrera { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
    .badge-provisional { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    .badge-libre { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
    .badge-contrato { background: rgba(100, 116, 139, 0.15); color: #94a3b8; }
</style>
@section('content')

<!-- CABECERA DE BIENVENIDA Y AYUDA -->
<div style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid var(--border); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; font-size: 6rem; opacity: 0.05;">📁</div>
    <div style="max-width: 600px; position: relative; z-index: 1;">
        <h1 style="font-size: 1.4rem; margin-bottom: 8px; font-family: 'Outfit', sans-serif;">¡Hola! Bienvenid@ a Gestión Humana</h1>
        <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6;">Aquí puedes administrar a los servidores de la Alcaldía, gestionar la nómina, vigilar la salud en el trabajo (SST) y registrar las capacitaciones de forma sencilla.</p>
        <div style="margin-top: 20px; display: flex; gap: 15px;">
            <button class="nav-link active" onclick="showPage('officials')" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);">📋 Ver Directorio</button>
            <button class="nav-link" onclick="openModal('payroll-modal')" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">💸 Liquidar Nómina</button>
        </div>
    </div>
</div>

<!-- NAVEGACIÓN TH -->
<div class="hr-tabs">
    <div class="hr-tab active" onclick="showPage('dashboard', this)" title="Resumen general del área">
        <span class="tab-icon">🏠</span>
        <span class="tab-label">Inicio</span>
    </div>
    <div class="hr-tab" onclick="showPage('positions', this)" title="Lista de cargos y manual de funciones">
        <span class="tab-icon">🏛️</span>
        <span class="tab-label">Cargos/Planta</span>
    </div>
    <div class="hr-tab" onclick="showPage('officials', this)" title="Lista de empleados y servidores">
        <span class="tab-icon">👤</span>
        <span class="tab-label">Personal</span>
    </div>
    <div class="hr-tab" onclick="showPage('payroll', this)" title="Pagos mensuales y aportes">
        <span class="tab-icon">💰</span>
        <span class="tab-label">Pagos/Nómina</span>
    </div>
    <div class="hr-tab" onclick="showPage('contracts', this)" title="Contratos de prestadores y deducciones">
        <span class="tab-icon">📄</span>
        <span class="tab-label">Contratos</span>
    </div>
    <div class="hr-tab" onclick="showPage('development', this)" title="Capacitaciones y bienestar">
        <span class="tab-icon">✨</span>
        <span class="tab-label">Bienestar/PIC</span>
    </div>
    <div class="hr-tab" onclick="showPage('sst', this)" title="Salud en el trabajo y comités">
        <span class="tab-icon">🏥</span>
        <span class="tab-label">Salud (SST)</span>
    </div>
    <div class="hr-tab" onclick="showPage('situations', this)" title="Vacaciones, licencias y permisos">
        <span class="tab-icon">⛱️</span>
        <span class="tab-label">Permisos</span>
    </div>
    <div class="hr-tab" onclick="showPage('evaluation', this)" title="Evaluación anual del desempeño">
        <span class="tab-icon">📊</span>
        <span class="tab-label">Evaluaciones</span>
    </div>
    <div class="hr-tab" onclick="showPage('reports', this)" title="Descargar reportes y estadísticas">
        <span class="tab-icon">📥</span>
        <span class="tab-label">Reportes</span>
    </div>
    <div class="hr-tab" id="tab-settings" onclick="showPage('settings', this)" title="Configuración de estampillas y porcentajes" style="border: 1px dashed var(--hr-primary);">
        <span class="tab-icon">⚙️</span>
        <span class="tab-label">SISTEMA</span>
    </div>
</div>

<div id="hr-pages">
    <!-- DASHBOARD -->
    <section class="hr-page active" id="page-dashboard">
        <div class="hr-stats-grid" id="hr-stats-container">
            <div class="hr-stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div>
                    <span class="hr-stat-label" style="color: rgba(255,255,255,0.8);">Última Nómina Pagada</span>
                    <span class="hr-stat-val" id="stat-last-payroll">$0</span>
                    <span class="hr-stat-label" id="stat-last-month" style="color: rgba(255,255,255,0.8); font-weight: bold; font-size: 0.9rem;">-</span>
                </div>
                <span style="font-size: 2rem; opacity: 0.3;">💸</span>
            </div>
            <div class="hr-stat-card">
                <div class="hr-stat-icon" style="font-size: 2rem; background: rgba(14, 165, 233, 0.1); padding: 10px; border-radius: 12px;">👥</div>
                <div>
                    <span class="hr-stat-val" id="st-total">0</span>
                    <span class="hr-stat-label">Personas Activas</span>
                </div>
            </div>
            <div class="hr-stat-card">
                <div class="hr-stat-icon" style="font-size: 2rem; background: rgba(99, 102, 241, 0.1); padding: 10px; border-radius: 12px;">🎓</div>
                <div>
                    <span class="hr-stat-val" id="st-training">0</span>
                    <span class="hr-stat-label">Capacitaciones</span>
                </div>
            </div>
            <div class="hr-stat-card">
                <div class="hr-stat-icon" style="font-size: 2rem; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 12px;">🏥</div>
                <div>
                    <span class="hr-stat-val" id="st-sst">0</span>
                    <span class="hr-stat-label">Alertas de Salud</span>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                    <h3 style="font-size: 0.95rem;">Acciones Recomendadas Hoy</h3>
                </div>
                <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <span style="display:block; font-size: 1.2rem; margin-bottom: 5px;">✍️</span>
                        <span style="display:block; font-weight: 600; font-size: 0.85rem;">Evaluar Desempeño</span>
                        <p style="font-size: 0.7rem; color: #94a3b8; margin: 4px 0 10px 0;">Hay servidores de carrera pendientes por calificar este periodo.</p>
                        <button class="nav-link active" onclick="showPage('evaluation')" style="padding: 2px 10px; font-size: 0.65rem;">Ir a Evaluaciones</button>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <span style="display:block; font-size: 1.2rem; margin-bottom: 5px;">🏥</span>
                        <span style="display:block; font-weight: 600; font-size: 0.85rem;">Revisión SST</span>
                        <p style="font-size: 0.7rem; color: #94a3b8; margin: 4px 0 10px 0;">Asegúrese de que los nuevos ingresos tengan su examen médico.</p>
                        <button class="nav-link active" onclick="showPage('sst')" style="padding: 2px 10px; font-size: 0.65rem;">Ver Salud en Trabajo</button>
                    </div>
                </div>
                <div style="padding: 20px; border-top: 1px solid var(--border);">
                    <h3 style="font-size: 0.85rem; margin-bottom: 15px;">Últimas Personas Vinculadas</h3>
                    <table class="hr-table" id="table-recent-officials">
                        <thead>
                            <tr><th>Funcionario</th><th>Cargo</th><th>Contrato</th><th>Ingreso</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="hr-table-container">
                 <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">Resumen de Planta</h3>
                </div>
                <div style="padding: 20px; height: 310px; position: relative;">
                    <canvas id="chart-planta-canvas"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- PLANTA Y CARGOS -->
    <section class="hr-page" id="page-positions">
        <div style="background: rgba(14, 165, 233, 0.05); border-left: 4px solid var(--hr-primary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">En esta sección defines los <strong>Cargos de la Alcaldía</strong>. Aquí configuras cuánto gana cada cargo y qué funciones cumple según el manual oficial.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Manual de Cargos</h2>
            <button class="nav-link active" onclick="openModal('position-modal')">+ Crear Nuevo Cargo</button>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-positions">
                <thead>
                    <tr><th>Código/Grado</th><th>Denominación</th><th>Nivel</th><th>Salaro Base</th><th>Opciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- SERVIDORES -->
    <section class="hr-page" id="page-officials">
        <div style="background: rgba(99, 102, 241, 0.05); border-left: 4px solid var(--hr-secondary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Aquí están todas las <strong>personas que trabajan en la Alcaldía</strong>. Puedes ver su contrato, si están al día con el SIGEP y su estado actual.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Directorio de Personal</h2>
            <div style="display: flex; gap: 10px;">
                <input type="text" placeholder="Buscar por nombre o cédula..." style="background: rgba(15,23,42,0.8); border: 1px solid var(--border); border-radius: 8px; padding: 6px 14px; color: white; outline: none;">
                <button class="nav-link active" onclick="openModal('official-modal')">+ Contratar/Vincular</button>
            </div>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-officials">
                <thead>
                    <tr><th>Identificación</th><th>Nombre</th><th>Dependencia</th><th>Tipo</th><th>SIGEP</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- NOMINA -->
    <section class="hr-page" id="page-payroll">
        <div style="background: rgba(16, 185, 129, 0.05); border-left: 4px solid var(--hr-success); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Control de <strong>Pagos Mensuales</strong>. Puedes generar la nómina del mes para la planta o para los contratistas y revisar cuánto se le paga a cada uno.</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">Historial de Pagos</h3>
                </div>
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <button class="nav-link active" style="width: 100%; justify-content: center;" onclick="openModal('payroll-modal')">Nueva Liquidación del Mes</button>
                </div>
                <table class="hr-table" id="table-payroll-periods">
                    <thead><tr><th>Periodo</th><th>Total</th><th>Estado</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="hr-table-container">
                 <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">Detalle de Liquidación Actual</h3>
                </div>
                <table class="hr-table" id="table-payroll-details">
                    <thead><tr><th>Funcionario</th><th>Base</th><th>Deduc. Salud/Pensión</th><th>Neto</th><th>Acción</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- CONTRATOS -->
    <section class="hr-page" id="page-contracts">
        <div style="background: rgba(14, 165, 233, 0.05); border-left: 4px solid var(--hr-primary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Gestión de <strong>Contratistas</strong>. Aquí registras los contratos externos y configuras las estampillas (Cultura, Adulto Mayor, etc.) que se descuentan automáticamente.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Contratos Externos</h2>
            <button class="nav-link active" onclick="openModal('contract-modal')">+ Registrar Nuevo Contrato</button>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-contracts">
                <thead><tr><th>Contratista</th><th>Tipo</th><th># Contrato</th><th>Valor Total</th><th>Pagado</th><th>Saldo</th><th>Vigencia</th><th>Acciones</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- SITUACIONES ADMINISTRATIVAS -->
    <section class="hr-page" id="page-situations">
        <div style="background: rgba(245, 158, 11, 0.05); border-left: 4px solid var(--hr-warning); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Control de <strong>Novedades y Permisos</strong>. Registra vacaciones, incapacidades médicas o licencias para que queden registradas en la historia de cada servidor.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Novedades y Permisos</h2>
            <button class="nav-link active" onclick="openModal('situation-modal')">+ Registrar Permiso/Novedad</button>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-situations">
                <thead>
                    <tr><th>Funcionario</th><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
    
    <!-- DESARROLLO (PIC) -->
    <section class="hr-page" id="page-development">
        <div style="background: rgba(99, 102, 241, 0.05); border-left: 4px solid var(--hr-secondary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Plan Institucional de <strong>Capacitaciones y Bienestar</strong>. Organiza actividades, talleres e integraciones, y lleva el registro de quiénes asistieron.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
             <h2 style="font-size: 1.2rem;">Actividades (PIC)</h2>
             <button class="nav-link active" onclick="openModal('training-modal')">+ Programar Actividad</button>
        </div>
        <div class="hr-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
             <div class="hr-stat-card">
                <div><span class="hr-stat-val">12</span><span class="hr-stat-label">Programas Ejecutados</span></div>
             </div>
             <div class="hr-stat-card">
                <div><span class="hr-stat-val">85%</span><span class="hr-stat-label">Cumplimiento Metas</span></div>
             </div>
             <div class="hr-stat-card">
                <div><span class="hr-stat-val">4.8</span><span class="hr-stat-label">Calificación Promedio</span></div>
             </div>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-training">
                <thead><tr><th>Actividad</th><th>Tipo</th><th>Fecha</th><th>Horas</th><th>Asistentes</th><th>Estado</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- EVALUACION (EDL) -->
    <section class="hr-page" id="page-evaluation">
        <div style="background: rgba(14, 165, 233, 0.05); border-left: 4px solid var(--hr-primary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Evaluación del <strong>Desempeño Laboral</strong>. Aquí mides el cumplimiento de metas de los servidores de Carrera Administrativa.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Evaluaciones de Desempeño</h2>
            <button class="nav-link active" onclick="openModal('edl-modal')">+ Iniciar Evaluación</button>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-edl">
                <thead>
                    <tr><th>Funcionario</th><th>Año</th><th>Periodo</th><th>Compromisos</th><th>Calificación</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- SST -->
    <section class="hr-page" id="page-sst">
        <div style="background: rgba(239, 68, 68, 0.05); border-left: 4px solid var(--hr-danger); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Seguridad y Salud: Gestiona el <strong>COPASST</strong>, las reuniones obligatorias y lleva el control de los exámenes médicos de ingreso y periódicos.</p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">Seguridad y Salud (SST)</h2>
            <div style="display: flex; gap: 10px;">
                <button class="nav-link active" onclick="openModal('sst-record-modal')">+ Examen Médico/Incidente</button>
                <button class="nav-link active" onclick="openModal('committee-modal')">+ Nuevo Comité</button>
                <button class="nav-link active" onclick="openModal('meeting-modal')">+ Nueva Acta</button>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">📋 Reuniones y Actas de Comités</h3>
                </div>
                <table class="hr-table" id="table-meetings">
                    <thead><tr><th>Fecha</th><th>Comité</th><th>Título</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody></tbody>
                </table>
                <div style="padding: 20px; border-top: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">🏥 Registros Médicos e Incidentes</h3>
                </div>
                <table class="hr-table" id="table-sst-records">
                    <thead><tr><th>Funcionario</th><th>Tipo</th><th>Fecha</th><th>Hallazgos</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.95rem;">🛠️ Comités Vigentes</h3>
                </div>
                <div id="committees-list" style="padding: 20px;">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>
        </div>
    </section>

    <!-- REPORTES -->
    <section class="hr-page" id="page-reports">
        <div style="background: rgba(14, 165, 233, 0.05); border-left: 4px solid var(--hr-primary); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Descarga la información de Talento Humano en formato Excel (CSV) para tus informes oficiales.</p>
        </div>
        <div class="hr-stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            <div class="hr-stat-card" style="cursor:pointer; flex-direction: column; align-items: flex-start; gap: 15px;" onclick="generateReport('officials')">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size: 1.5rem; background: rgba(14,165,233,0.1); padding: 10px; border-radius: 10px;">👤</div>
                    <span style="font-weight: 600;">Listado de Personal</span>
                </div>
                <p style="font-size: 0.7rem; color:#94a3b8;">Nombres, cargos, tipos de vinculación y datos básicos.</p>
                <span style="font-size: 0.65rem; color:#38bdf8;">📥 Descargar Reporte</span>
            </div>
            
            <div class="hr-stat-card" style="cursor:pointer; flex-direction: column; align-items: flex-start; gap: 15px;" onclick="generateReport('contracts')">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size: 1.5rem; background: rgba(16,185,129,0.1); padding: 10px; border-radius: 10px;">📄</div>
                    <span style="font-weight: 600;">Saldos de Contratos</span>
                </div>
                <p style="font-size: 0.7rem; color:#94a3b8;">Control de cuánto se ha pagado y el saldo restante de cada contrato.</p>
                <span style="font-size: 0.65rem; color:#10b981;">📥 Descargar Reporte</span>
            </div>

            <div class="hr-stat-card" style="cursor:pointer; flex-direction: column; align-items: flex-start; gap: 15px;" onclick="generateReport('payroll')">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size: 1.5rem; background: rgba(99,102,241,0.1); padding: 10px; border-radius: 10px;">💰</div>
                    <span style="font-weight: 600;">Histórico Nómina</span>
                </div>
                <p style="font-size: 0.7rem; color:#94a3b8;">Resumen de pagos mensuales realizados durante el año.</p>
                <span style="font-size: 0.65rem; color:#6366f1;">📥 Descargar Reporte</span>
            </div>
        </div>
    </section>

    <!-- CONFIGURACIÓN -->
    <section class="hr-page" id="page-settings">
        <div style="background: rgba(148, 163, 184, 0.05); border-left: 4px solid #64748b; padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Configuración del sistema. Ajusta los <strong>porcentajes de ley</strong> y gestiona el catálogo de <strong>estampillas</strong> municipales.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="margin:0; font-size: 1rem;">⚖️ Tasas de Descuento (Ley)</h3>
                </div>
                <div id="settings-list" style="padding: 20px;">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>
            
            <div class="hr-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin:0; font-size: 1rem;">📜 Catálogo de Estampillas</h3>
                    <button class="nav-link active" style="padding: 4px 12px; font-size: 0.8rem;" onclick="openModal('stamp-modal')">+ Nueva</button>
                </div>
                <table class="hr-table" id="table-stamps">
                    <thead><tr><th>Nombre</th><th>Tasa</th><th>Tipo</th><th>Acciones</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- MODAL ESTAMPILLA CATALOGO -->
<div class="hr-modal" id="stamp-modal">
    <div class="hr-modal-content">
        <h3>Nueva Estampilla Global</h3>
        <div style="display: grid; gap: 15px;">
             <div><label>Nombre de la Estampilla</label><input type="text" id="stamp-name" placeholder="Ej: Pro-Cultura"></div>
             <div><label>Valor / Porcentaje</label><input type="number" id="stamp-value" step="0.01" value="1.5"></div>
             <div><label>Tipo</label>
                <select id="stamp-type">
                    <option value="percentage">Porcentaje (%)</option>
                    <option value="fixed">Valor Fijo ($)</option>
                </select>
             </div>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('stamp-modal')">Cerrar</button>
            <button class="nav-link active" onclick="saveStampCatalog()">✅ Guardar en Catálogo</button>
        </div>
    </div>
</div>

<!-- MODAL CARGO -->
<div class="hr-modal" id="position-modal">
    <div class="hr-modal-content">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span style="font-size: 1.5rem;">🏢</span>
            <h3 style="margin: 0;">Configurar Nuevo Cargo</h3>
        </div>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 20px;">Utilice este formulario para agregar una vacante o cargo oficial a la planta de la alcaldía.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div><label>Nombre del Cargo</label><input type="text" id="pos-name" placeholder="Ej: Auxiliar Administrativo"></div>
             <div><label>Código y Grado</label><input type="text" id="pos-code" placeholder="Ej: 405-01"></div>
             <div><label>Nivel del Cargo</label>
                <select id="pos-level">
                    <option value="asistencial">Asistencial (Apoyo)</option>
                    <option value="tecnico">Técnico (Operativo)</option>
                    <option value="profesional">Profesional (Especializado)</option>
                    <option value="asesor">Asesor (Directivo)</option>
                </select>
             </div>
             <div><label>Sueldo Mensual</label><input type="number" id="pos-salary" placeholder="0.00"></div>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('position-modal')">Cerrar</button>
            <button class="nav-link active" onclick="savePosition()">✅ Guardar Cargo</button>
        </div>
    </div>
</div>

<!-- MODAL NOMINA -->
<div class="hr-modal" id="payroll-modal">
    <div class="hr-modal-content">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span style="font-size: 1.5rem;">💸</span>
            <h3 style="margin: 0;">Generar Pagos del Mes</h3>
        </div>
        <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
            <p style="margin: 0; font-size: 0.8rem; color: #10b981;"><strong>Ayuda:</strong> El sistema calculará automáticamente los descuentos de ley (4% Salud y 4% Pensión) para para la planta de personal.</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div><label>Mes a Pagar</label><input type="number" id="pay-month" min="1" max="12" value="4"></div>
             <div><label>Año</label><input type="number" id="pay-year" value="2024"></div>
             <div style="grid-column: span 2;"><label>¿A quién desea pagar?</label>
                <select id="pay-type">
                    <option value="employees">Personal de Planta (Funcionarios)</option>
                    <option value="contractors">Contratistas (Por Orden de Servicio)</option>
                </select>
             </div>
             <div style="grid-column: span 2; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px;">
                <input type="checkbox" id="pay-clone" style="width: 18px; height: 18px;">
                <label style="margin:0; font-size: 0.8rem; cursor: pointer;" for="pay-clone">Usar los mismos datos del mes pasado (Recomendado para ahorrar tiempo)</label>
             </div>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('payroll-modal')">Cerrar</button>
            <button class="nav-link active" onclick="runPayroll()">🚀 Iniciar Liquidación</button>
        </div>
    </div>
</div>

<!-- MODAL DETALLE NOMINA -->
<div class="hr-modal" id="payroll-detail-modal">
    <div class="hr-modal-content" style="max-width: 900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="pay-detail-title">Detalle de Nómina</h3>
            <button class="nav-link" onclick="closeModal('payroll-detail-modal')">Cerrar</button>
        </div>
        <div class="hr-table-container">
            <table class="hr-table" id="table-pay-items">
                <thead>
                    <tr><th>Funcionario</th><th>Base</th><th>Deducciones</th><th>Neto</th><th>Acciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CONTRATO -->
<div class="hr-modal" id="contract-modal">
    <div class="hr-modal-content">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span style="font-size: 1.5rem;">📄</span>
            <h3 style="margin: 0;">Nuevo Contrato / Prestador</h3>
        </div>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 20px;">Puede ingresar el valor total del contrato y el sistema le ayudará a calcular la mensualidad automáticamente.</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Contratista (Seleccione de la lista)</label><select id="con-official"></select></div>
             <div><label>Número de Contrato</label><input type="text" id="con-number" placeholder="Ej: CPS-001-2024"></div>
             <div><label>Rubro Presupuestal</label><input type="text" id="con-rubro" placeholder="Ej: 2.1.2.01.01.003"></div>
             <div><label>CDP #</label><input type="text" id="con-cdp" placeholder="Ej: 552"></div>
             <div><label>Registro Presupuestal (RP) #</label><input type="text" id="con-rp" placeholder="Ej: 110"></div>
             <div>
                <label>Tipo de Contrato</label>
                <select id="con-type">
                    <option value="ops">Prestación de Servicios (OPS)</option>
                    <option value="planta_temporal">Planta Temporal</option>
                    <option value="administrativo">Administrativo</option>
                </select>
             </div>
             <div><label>Nivel de Riesgo ARL</label>
                <select id="con-arl">
                    <option value="1">Clase I (Mínimo)</option>
                    <option value="2">Clase II (Bajo)</option>
                    <option value="3">Clase III (Medio)</option>
                    <option value="4">Clase IV (Alto)</option>
                    <option value="5">Clase V (Máximo)</option>
                </select>
             </div>
             <div><label>Valor Total del Contrato ($)</label><input type="number" id="con-total-value" placeholder="Ej: 30000000" oninput="calculateMonthlyFromTotal()"></div>
             
             <div><label>Fecha Inicio</label><input type="date" id="con-start" onchange="calculateMonthlyFromTotal()"></div>
             <div><label>Fecha Fin</label><input type="date" id="con-end" onchange="calculateMonthlyFromTotal()"></div>
             
             <div style="background: rgba(14, 165, 233, 0.1); padding: 10px; border-radius: 8px; grid-column: span 2;">
                <label style="color: #38bdf8;">Pago Mensual Estimado</label>
                <input type="number" id="con-value" style="font-size: 1.2rem; font-weight: bold; color: #38bdf8;" placeholder="0.00">
                <small style="color: #94a3b8; display: block; margin-top: 5px;">Este es el valor que se pagará cada mes en la nómina.</small>
             </div>

             <div style="grid-column: span 2;"><label>Objeto del Contrato</label><textarea id="con-object" placeholder="Descripción breve del contrato..." style="width:100%; border:1px solid var(--border); background:#0f172a; color:white; min-height: 60px;"></textarea></div>
             
             <div><label>Nombre del Supervisor</label><input type="text" id="con-supervisor-name" placeholder="Ej: Juan Valdéz"></div>
             <div><label>Cargo del Supervisor</label><input type="text" id="con-supervisor-pos" placeholder="Ej: Secretario de Planeación"></div>
        </div>

        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('contract-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveContract()">✅ Guardar Contrato</button>
        </div>
    </div>
</div>

<!-- MODAL FICHA FUNCIONARIO -->
<div class="hr-modal" id="official-details-modal">
    <div class="hr-modal-content" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <div style="width: 80px; height: 80px; background: var(--hr-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;" id="fd-initials">AD</div>
                <div>
                    <h2 id="fd-name" style="margin: 0; font-size: 1.5rem;">Nombre del Funcionario</h2>
                    <p id="fd-position" style="margin: 0; color: #94a3b8;">Cargo / Dependencia</p>
                </div>
            </div>
            <button class="nav-link" onclick="closeModal('official-details-modal')">Cerrar</button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="hr-table-container" style="padding: 20px; background: rgba(255,255,255,0.02);">
                <h4 style="margin-top: 0; color: var(--hr-primary);">Información Básica</h4>
                <div id="fd-basic-info" style="font-size: 0.9rem; display: grid; gap: 10px;">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>
            <div class="hr-table-container" style="padding: 20px; background: rgba(255,255,255,0.02);">
                <h4 style="margin-top: 0; color: var(--hr-success);">Desempeño y SST</h4>
                <p id="fd-eval" style="font-size: 0.85rem; color: #94a3b8;"></p>
                <div id="fd-sst-summary" style="margin-top: 15px;">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
             <h4 style="margin-top: 0; color: var(--hr-warning);">Historial de Contratos (Si aplica)</h4>
             <table class="hr-table" id="table-fd-contracts">
                 <thead><tr><th># Contrato</th><th>Fecha Inicio</th><th>Estado</th></tr></thead>
                 <tbody></tbody>
             </table>
        </div>
    </div>
</div>

<!-- MODAL AJUSTAR PAGO -->
<div class="hr-modal" id="adjust-pay-modal">
    <div class="hr-modal-content">
        <h3>Ajustar Liquidación Manual</h3>
        <input type="hidden" id="adj-item-id">
        <div style="display: grid; gap: 15px;">
             <div><label>Sueldo Base ($)</label><input type="number" id="adj-base"></div>
             <div><label>Bonos / Primas ($)</label><input type="number" id="adj-allowances"></div>
             <div><label>Horas Extras ($)</label><input type="number" id="adj-overtime"></div>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('adjust-pay-modal')">Cerrar</button>
            <button class="nav-link active" onclick="saveAdjustedPay()">✅ Guardar Cambios</button>
        </div>
    </div>
</div>

<!-- MODAL FUNCIONARIO -->
<div class="hr-modal" id="official-modal">
    <div class="hr-modal-content">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span style="font-size: 1.5rem;">👤</span>
            <h3 style="margin: 0;">Contratar o Vincular Personal</h3>
        </div>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 20px;">Complete los datos básicos de la persona que ingresa a laborar en la entidad.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Nombre Completo</label><input type="text" id="off-name" placeholder="Ej: Juan Pérez"></div>
             <div><label>Cédula / Documento</label><input type="text" id="off-doc"></div>
             <div><label>Cargo que ocupará</label><select id="off-position"></select></div>
             <div><label>Secretaría / Dependencia</label><select id="off-office"></select></div>
             <div><label>Tipo de Contrato</label>
                <select id="off-type">
                    <option value="carrera">Carrera Administrativa (Planta)</option>
                    <option value="provisional">Provisionalidad (Temporal)</option>
                    <option value="libre_nombramiento">Libre Nombramiento (Secretarios/Jefes)</option>
                </select>
             </div>
             <div><label>Fecha de Inicio del Trabajo</label><input type="date" id="off-entry"></div>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
            <button class="nav-link" onclick="closeModal('official-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveOfficial()">✅ Registrar Ingreso</button>
        </div>
    </div>
</div>

<!-- MODAL COMITÉ -->
<div class="hr-modal" id="committee-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Configuración de Comité</h3>
        <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
             <div><label>Nombre del Comité</label><input type="text" id="com-name" placeholder="Ej: COPASST 2026-2028"></div>
             <div><label>Descripción / Alcance</label><textarea id="com-desc" style="width:100%; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
             <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div><label>Vigente Desde</label><input type="date" id="com-from"></div>
                <div><label>Vigente Hasta</label><input type="date" id="com-to"></div>
             </div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('committee-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveCommittee()">Crear Comité</button>
        </div>
    </div>
</div>

<!-- MODAL MIEMBRO COMITÉ -->
<div class="hr-modal" id="member-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Agregar Miembro al Comité</h3>
        <input type="hidden" id="mem-committee-id">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Servidor Público</label><select id="mem-official"></select></div>
             <div><label>Rol en el Comité</label><select id="mem-role">
                <option value="Presidente">Presidente</option>
                <option value="Secretario">Secretario</option>
                <option value="Principal">Principal (Representante)</option>
                <option value="Suplente">Suplente</option>
             </select></div>
             <div><label>Fecha de Designación</label><input type="date" id="mem-date"></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('member-modal')">Cancelar</button>
            <button class="nav-link active" onclick="addMember()">Asignar</button>
        </div>
    </div>
</div>

<!-- MODAL REUNIÓN / ACTA -->
<div class="hr-modal" id="meeting-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Registro de Acta de Reunión</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div><label>Comité</label><select id="meet-committee"></select></div>
             <div><label>Título de la Sesión</label><input type="text" id="meet-title"></div>
             <div><label>Fecha</label><input type="date" id="meet-date"></div>
             <div><label>Lugar</label><input type="text" id="meet-location"></div>
             <div style="grid-column: span 2;"><label>Orden del Día</label><textarea id="meet-agenda" style="width:100%; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
             <div style="grid-column: span 2;"><label>Desarrollo y Acuerdos (Acta)</label><textarea id="meet-content" style="width:100%; height: 200px; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('meeting-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveMeeting()">Guardar Acta</button>
        </div>
    </div>
</div>

<!-- MODAL SITUACION ADMIN -->
<div class="hr-modal" id="situation-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Registro de Situación Administrativa</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Funcionario</label><select id="sit-official"></select></div>
             <div><label>Tipo de Situación</label><select id="sit-type">
                <option value="vacaciones">Vacaciones</option>
                <option value="permiso">Permiso Remunerado</option>
                <option value="incapacidad">Incapacidad Médica</option>
                <option value="licencia_maternidad">Licencia Maternidad</option>
                <option value="comision">Comisión de Servicios</option>
                <option value="encargo">Encargo</option>
             </select></div>
             <div><label>Motivo</label><input type="text" id="sit-reason"></div>
             <div><label>Desde</label><input type="date" id="sit-start"></div>
             <div><label>Hasta</label><input type="date" id="sit-end"></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('situation-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveSituation()">Registrar</button>
        </div>
    </div>
</div>

<!-- MODAL CAPACITACION -->
<div class="hr-modal" id="training-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Nueva Actividad PIC / Bienestar</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Título de la Actividad</label><input type="text" id="train-title"></div>
             <div><label>Tipo</label><select id="train-type">
                <option value="tecnica">Capacitación Técnica</option>
                <option value="profesional">Desarrollo Profesional</option>
                <option value="induccion">Inducción / Reinducción</option>
                <option value="bienestar">Clima / Bienestar</option>
             </select></div>
             <div><label>Fecha Programada</label><input type="date" id="train-date"></div>
             <div><label>Intensidad Horaria</label><input type="number" id="train-hours"></div>
             <div style="grid-column: span 2;"><label>Descripción</label><textarea id="train-desc" style="width:100%; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('training-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveTraining()">Programar</button>
        </div>
    </div>
</div>

<!-- MODAL SST RECORD -->
<div class="hr-modal" id="sst-record-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Nuevo Registro SST</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Servidor Público</label><select id="sst-official"></select></div>
             <div><label>Tipo de Registro</label><select id="sst-type">
                <option value="examen_ingreso">Examen de Ingreso</option>
                <option value="examen_periodico">Examen Periódico</option>
                <option value="examen_egreso">Examen de Egreso</option>
                <option value="incidente">Incidente Laboral</option>
                <option value="accidente">Accidente de Trabajo</option>
             </select></div>
             <div><label>Fecha</label><input type="date" id="sst-date"></div>
             <div style="grid-column: span 2;"><label>Proveedor / IPS / Entidad</label><input type="text" id="sst-provider"></div>
             <div style="grid-column: span 2;"><label>Hallazgos / Observaciones</label><textarea id="sst-findings" style="width:100%; height: 80px; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('sst-record-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveSstRecord()">Guardar Registro</button>
        </div>
    </div>
</div>

<!-- MODAL EDL -->
<div class="hr-modal" id="edl-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Nueva Evaluación / Compromisos</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Servidor Público</label><select id="edl-official"></select></div>
             <div><label>Año</label><input type="number" id="edl-year" value="2026"></div>
             <div><label>Periodo</label><select id="edl-period">
                <option value="anual">Anual</option>
                <option value="semestral_1">1er Semestre</option>
                <option value="semestral_2">2do Semestre</option>
             </select></div>
             <div style="grid-column: span 2;"><label>Compromisos Laborales / Metas</label><textarea id="edl-compromises" style="width:100%; height: 100px; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('edl-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveEdl()">Guardar Evaluación</button>
        </div>
    </div>
</div>

<!-- MODAL CALIFICAR EDL -->
<div class="hr-modal" id="edl-score-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Calificar Desempeño</h3>
        <input type="hidden" id="edl-score-id">
        <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
             <div><label>Calificación Final (0-100)</label><input type="number" id="edl-score-val" min="0" max="100"></div>
             <div><label>Retroalimentación / Fortalezas / Por mejorar</label><textarea id="edl-score-feedback" style="width:100%; height: 100px; background: #0f172a; border: 1px solid var(--border); color: white;"></textarea></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('edl-score-modal')">Cancelar</button>
            <button class="nav-link active" onclick="submitEdlScore()">Finalizar Calificación</button>
        </div>
    </div>
</div>

<!-- MODAL DEDUCCIONES -->
<div class="hr-modal" id="deduction-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Gestionar Deducción</h3>
        <input type="hidden" id="ded-id">
        <input type="hidden" id="ded-contract-id">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div style="grid-column: span 2;"><label>Catálogo</label><select id="ded-catalog" onchange="applyStampFromCatalog()"></select></div>
             <div><label>Nombre</label><input type="text" id="ded-name"></div>
             <div><label>Tipo</label><select id="ded-type"><option value="percentage">Porcentaje</option><option value="fixed">Valor Fijo</option></select></div>
             <div><label>Valor</label><input type="number" id="ded-value"></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('deduction-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveDeduction()">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL AJUSTE PAGO -->
<div class="hr-modal" id="adjust-pay-modal">
    <div class="hr-modal-content">
        <h3 style="margin-bottom: 20px;">Ajustar Devengados</h3>
        <input type="hidden" id="adj-item-id">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
             <div><label>Salario Base</label><input type="number" id="adj-base"></div>
             <div><label>Auxilios</label><input type="number" id="adj-allowances"></div>
             <div><label>Horas Extra</label><input type="number" id="adj-overtime"></div>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="nav-link" onclick="closeModal('adjust-pay-modal')">Cancelar</button>
            <button class="nav-link active" onclick="saveAdjustedPay()">Guardar Ajustes</button>
        </div>
    </div>
</div>

<!-- MODAL FICHA FUNCIONARIO -->
<div class="hr-modal" id="official-details-modal">
    <div class="hr-modal-content">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div id="fd-initials" style="width: 50px; height: 50px; background: var(--hr-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"></div>
            <div>
                <h3 id="fd-name" style="margin: 0;"></h3>
                <div id="fd-position" style="font-size: 0.8rem; color: #94a3b8;"></div>
            </div>
        </div>
        <div id="fd-basic-info" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;"></div>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <button class="nav-link" onclick="closeModal('official-details-modal')">Cerrar</button>
        </div>
    </div>
</div>

<style>
    .hr-modal-content label { display: block; font-size: 0.7rem; color: #94a3b8; margin-bottom: 4px; }
    .hr-modal-content input, .hr-modal-content select, .hr-modal-content textarea { width: 100%; background: #0f172a; border: 1px solid var(--border); padding: 8px; color: white; border-radius: 6px; outline: none; }
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const api = {
        fetchStats: () => fetch('{{ url("/api/hr/stats") }}').then(r => r.json()),
        fetchPositions: () => fetch('{{ url("/api/hr/positions") }}').then(r => r.json()),
        fetchOfficials: () => fetch('{{ url("/api/officials") }}').then(r => r.json()),
        fetchPayroll: () => fetch('{{ url("/api/hr/payroll") }}').then(r => r.json()),
        fetchTraining: () => fetch('{{ url("/api/hr/training") }}').then(r => r.json()),
        fetchSst: () => fetch('{{ url("/api/hr/sst") }}').then(r => r.json()),
        fetchCommittees: () => fetch('{{ url("/api/hr/committees") }}').then(r => r.json()),
        fetchMeetings: () => fetch('{{ url("/api/hr/meetings") }}').then(r => r.json()),
        fetchSituations: () => fetch('{{ url("/api/hr/situations") }}').then(r => r.json()),
        fetchContracts: () => fetch('{{ url("/api/hr/contracts") }}').then(r => r.json()),
        fetchOffices: () => fetch('{{ url("/api/offices") }}').then(r => r.json()),
        fetchEdl: () => fetch('{{ url("/api/hr/edl") }}').then(r => r.json()),
    };

    async function loadSettings() {
        const res = await fetch('{{ url("/api/hr/settings") }}');
        const settings = await res.json();
        const container = document.getElementById('settings-list');
        if (container) {
            container.innerHTML = settings.map(s => `
                <div style="margin-bottom: 20px; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--border);">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600;">${s.label}</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" step="0.001" value="${s.value}" id="set-${s.id}" style="flex: 1;">
                        <button class="nav-link active" onclick="updateSetting(${s.id})">Guardar</button>
                    </div>
                </div>
            `).join('');
        }
        loadStampsCatalog();
    }

    async function updateSetting(id) {
        const val = document.getElementById(`set-${id}`).value;
        await fetch(`{{ url("/api/hr/settings") }}/${id}`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({value: val})
        });
        alert('Configuración actualizada con éxito');
    }

    async function viewOfficialDetails(id) {
        const res = await fetch(`{{ url("/api/officials") }}/${id}`);
        const official = await res.json();
        
        document.getElementById('fd-name').innerText = official.full_name;
        document.getElementById('fd-initials').innerText = official.full_name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        document.getElementById('fd-position').innerText = `${official.position_rel ? official.position_rel.name : 'Sin Cargo'} | ${official.employment_type || 'N/A'}`;
        
        document.getElementById('fd-basic-info').innerHTML = `
            <div><strong>Documento:</strong> ${official.document_number}</div>
            <div><strong>Vinculación:</strong> ${official.entry_date || 'N/A'}</div>
            <div><strong>Estado:</strong> <span class="badge" style="background:#064e3b; color:#10b981;">Activo</span></div>
            <div><strong>Email:</strong> ${official.email || 'N/A'}</div>
        `;
        
        openModal('official-details-modal');
    }

    function prepareAdjustment(id, base, allowances, overtime) {
        document.getElementById('adj-item-id').value = id;
        document.getElementById('adj-base').value = base;
        document.getElementById('adj-allowances').value = allowances;
        document.getElementById('adj-overtime').value = overtime;
        openModal('adjust-pay-modal');
    }

    async function saveAdjustedPay() {
        const id = document.getElementById('adj-item-id').value;
        const data = {
            salary_base: document.getElementById('adj-base').value,
            allowances: document.getElementById('adj-allowances').value,
            overtime: document.getElementById('adj-overtime').value
        };
        await fetch(`{{ url("/api/hr/payroll/items") }}/${id}`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('adjust-pay-modal');
        loadPayroll(); 
    }

    function applyStampFromCatalog() {
        const sel = document.getElementById('ded-catalog');
        if (!sel.value) return;
        const stamp = JSON.parse(sel.value);
        document.getElementById('ded-name').value = stamp.name;
        document.getElementById('ded-type').value = stamp.type;
        document.getElementById('ded-value').value = stamp.default_value;
    }

    async function terminateContract(id) {
        if (!confirm('¿Seguro que desea cerrar este contrato?')) return;
        await fetch(`{{ url("/api/hr/contracts") }}/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        loadContracts();
    }

    async function loadStampsCatalog() {
        const res = await fetch('{{ url("/api/hr/stamps") }}');
        const stamps = await res.json();
        const tbody = document.querySelector('#table-stamps tbody');
        if (tbody) {
            tbody.innerHTML = (stamps || []).map(s => `
                <tr>
                    <td><strong>${s.name}</strong></td>
                    <td>${s.default_value}${s.type === 'percentage' ? '%' : '$'}</td>
                    <td><span style="font-size: 0.7rem; background: #334155; padding: 2px 6px; border-radius: 4px;">${s.type.toUpperCase()}</span></td>
                    <td>
                        <button class="nav-link danger" style="padding: 2px 8px; font-size: 10px;" onclick="deleteStampCatalog(${s.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Cargar también en el selector de deducciones del contrato
        const sel = document.getElementById('ded-catalog');
        if (sel) {
            sel.innerHTML = '<option value="">Seleccione del catálogo...</option>' + 
                stamps.map(s => `<option value='${JSON.stringify(s)}'>${s.name} (${s.default_value}${s.type === 'percentage' ? '%' : '$'})</option>`).join('');
        }
    }

    async function saveStampCatalog() {
        const data = {
            name: document.getElementById('stamp-name').value,
            default_value: document.getElementById('stamp-value').value,
            type: document.getElementById('stamp-type').value
        };
        
        if(!data.name || !data.default_value) {
            alert('Por favor complete todos los campos');
            return;
        }

        await fetch('{{ url("/api/hr/stamps") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        
        // Limpiar campos
        document.getElementById('stamp-name').value = '';
        document.getElementById('stamp-value').value = '1.5';
        
        closeModal('stamp-modal');
        loadStampsCatalog();
    }

    async function deleteStampCatalog(id) {
        if(!confirm('¿Seguro que desea eliminar esta estampilla del catálogo global?')) return;
        await fetch(`{{ url("/api/hr/stamps") }}/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        loadStampsCatalog();
    }

    let hrTypeChart = null;

    function showPage(name, el) {
        document.querySelectorAll('.hr-page').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.hr-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('page-' + name).classList.add('active');
        if(el) el.classList.add('active');
        loadPage(name);
    }

    function loadPage(name) {
        if(name === 'dashboard') loadDashboard();
        if(name === 'positions') loadPositions();
        if(name === 'officials') loadOfficials();
        if(name === 'payroll') loadPayroll();
        if(name === 'development') loadDevelopment();
        if(name === 'sst') loadSst();
        if(name === 'situations') loadSituations();
        if(name === 'contracts') loadContracts();
        if(name === 'evaluation') loadEvaluation();
        if(name === 'reports') loadReports();
        if(name === 'settings') loadSettings();
    }

    // Helper to format raw database labels
    function formatLabel(str) {
        if (!str) return 'N/A';
        const labels = {
            'carrera_administrativa': 'Carrera Administrativa',
            'libre_nombramiento': 'Libre Nombramiento',
            'provisional': 'Provisional',
            'periodo_prueba': 'Periodo de Prueba',
            'ops': 'Prestación de Servicios (OPS)',
            'evaluacion_desempeno': 'Evaluación de Desempeño',
            'examen_periodico': 'Examen Periódico',
            'accidente_laboral': 'Accidente Laboral',
            'incapacidad': 'Incapacidad',
            'vacaciones': 'Vacaciones',
            'activo': 'Activo',
            'inactivo': 'Inactivo',
            'suspendido': 'Suspendido',
            'liquidado': 'Liquidado',
            'pendiente': 'Pendiente',
            'realizada': 'Realizada',
            'por_realizar': 'Por Realizar',
            'programada': 'Programada'
        };
        return labels[str.toLowerCase()] || str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
    }

    async function loadDashboard() {
        try {
            const stats = await api.fetchStats();
            const statsData = stats.data || stats;
            
            if(document.getElementById('st-total')) document.getElementById('st-total').innerText = statsData.total_officials || 0;
            if(document.getElementById('st-training')) document.getElementById('st-training').innerText = statsData.active_trainings || 0;
            if(document.getElementById('stat-last-payroll')) {
                document.getElementById('stat-last-payroll').innerText = `$ ${new Intl.NumberFormat().format(statsData.last_payroll_amount || 0)}`;
                document.getElementById('stat-last-month').innerText = statsData.last_payroll_month || '-';
            }

            const res = await api.fetchOfficials();
            const officials = res.data || res;
            
            const tbody = document.querySelector('#table-recent-officials tbody');
            if (tbody) {
                tbody.innerHTML = (Array.isArray(officials) ? officials : []).slice(0, 5).map(o => `
                    <tr>
                        <td><strong>${o.full_name}</strong></td>
                        <td>${o.position_rel ? o.position_rel.name : 'No asignado'}</td>
                        <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-radius: 6px;">${formatLabel(o.employment_type)}</span></td>
                        <td>${o.entry_date ? new Date(o.entry_date).toLocaleDateString() : 'Reciente'}</td>
                    </tr>
                `).join('');
            }
            
            // Professional Chart.js implementation
            const ctx = document.getElementById('chart-planta-canvas');
            if (ctx && statsData.by_type) {
                if (hrTypeChart) hrTypeChart.destroy();
                
                const labels = statsData.by_type.map(t => t.employment_type.replace('_', ' ').toUpperCase());
                const values = statsData.by_type.map(t => t.total);

                hrTypeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(14, 165, 233, 0.8)',
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)'
                            ],
                            borderRadius: 8,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 13 },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
                                ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 1 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 9 } }
                            }
                        }
                    }
                });
            }
        } catch (e) {
            console.error("HrDashboard Error:", e);
        }
    }

    async function loadPositions() {
        const res = await api.fetchPositions();
        const positions = res.data || res;
        const tbody = document.querySelector('#table-positions tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(positions) ? positions : []).map(p => `
                <tr>
                    <td>${p.code || ''} Gr. ${p.grade || ''}</td>
                    <td><strong>${p.name}</strong></td>
                    <td><span class="badge" style="background:rgba(99,102,241,0.1); color:#6366f1; border-radius:6px;">${formatLabel(p.level)}</span></td>
                    <td style="font-weight:700;">$ ${new Intl.NumberFormat().format(p.base_salary)}</td>
                    <td><button class="nav-link danger" style="padding: 2px 8px; font-size: 10px;" onclick="deletePosition(${p.id})">Eliminar</button></td>
                </tr>
            `).join('');
        }
    }

    async function loadOfficials() {
        const res = await api.fetchOfficials();
        const officials = res.data || res;
        const tbody = document.querySelector('#table-officials tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(officials) ? officials : []).map(o => {
                return `
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="font-family: monospace; color: #94a3b8;">${o.document_number}</td>
                    <td><strong>${o.full_name}</strong></td>
                    <td>${o.position_rel ? o.position_rel.name : 'Sin asignar'}</td>
                    <td><span class="badge" style="background: rgba(99,102,241,0.1); color: #6366f1; border-radius: 6px;">${formatLabel(o.employment_type)}</span></td>
                    <td style="font-size: 0.8rem;">${o.sigep_updated ? '<span style="color: #10b981;">✅ Al día</span>' : '<span style="color: #f59e0b;">⚠️ Pendiente</span>'}</td>
                    <td><span class="badge" style="background: ${o.employment_status === 'activo' ? 'var(--hr-success)' : 'var(--hr-danger)'}; color: white; border-radius: 6px; font-size: 0.65rem;">${formatLabel(o.employment_status)}</span></td>
                    <td style="display: flex; gap: 5px;">
                        <button class="nav-link" style="padding: 4px; display: flex; align-items: center;" onclick="viewOfficialDetails(${o.id})" title="Ver Ficha"><i data-lucide="file-text" style="width:14px; height:14px;"></i></button>
                        <button class="nav-link danger" style="padding: 4px; display: flex; align-items: center;" onclick="deleteOfficial(${o.id})" title="Eliminar"><i data-lucide="trash-2" style="width:14px; height:14px;"></i></button>
                    </td>
                </tr>`;
            }).join('');
        }
    }

    async function loadPayroll() {
        const res = await api.fetchPayroll();
        const periods = res.data || res;
        const tbody = document.querySelector('#table-payroll-periods tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(periods) ? periods : []).map(p => `
                <tr onclick="viewPayrollDetail(${p.id}, false)" style="cursor:pointer" title="Haga clic para ver el detalle de este pago">
                    <td><strong>${p.month}/${p.year}</strong> <br> <small style="color: #64748b;">${p.type === 'contractors' ? 'Contratistas' : 'Planta'}</small></td>
                    <td>$ ${new Intl.NumberFormat().format(p.total_amount)}</td>
                    <td>
                        <span class="badge" style="background: rgba(16,185,129,0.1); color: #10b981;">${p.status === 'paid' ? 'Pagado' : 'Borrador'}</span>
                        ${p.status !== 'paid' ? `<button class="nav-link" style="padding: 2px 5px; font-size: 8px; margin-top: 5px;" onclick="markAsPaid(${p.id}, event)">Confirmar Pago</button>` : ''}
                    </td>
                </tr>
            `).join('');
            
            // Auto-cargar el primero si existe
            if (periods.length > 0) viewPayrollDetail(periods[0].id, false);
        }
    }

    async function viewPayrollDetail(id, showModal = true) {
        const response = await fetch('{{ url("/api/hr/payroll") }}/' + id);
        const json = await response.json();
        const period = json.data || json;
        
        const html = (period.items || []).map(item => {
            const details = item.details || {};
            const dedsArr = details.deductions ? Object.entries(details.deductions) : [];
            const deds = dedsArr.map(([k, v]) => `${k}: $${new Intl.NumberFormat().format(v)}`).join('<br>');
            
            // Deducciones planta
            const plantDeds = (item.deductions_health > 0 || item.deductions_pension > 0) 
                ? `Salud: $${new Intl.NumberFormat().format(item.deductions_health)} <br> Pensión: $${new Intl.NumberFormat().format(item.deductions_pension)}`
                : '';

            return `
                <tr>
                    <td><strong>${item.official ? item.official.full_name : 'N/A'}</strong></td>
                    <td>$ ${new Intl.NumberFormat().format(item.salary_base)}</td>
                    <td style="font-size: 9px; color: #94a3b8;">${deds || plantDeds || 'Sin deducciones'}</td>
                    <td><span style="color: var(--hr-success); font-weight: 700;">$ ${new Intl.NumberFormat().format(item.net_pay)}</span></td>
                    <td>
                        <button class="nav-link" style="padding: 2px 8px; font-size: 10px;" onclick="prepareAdjustment(${item.id}, ${item.salary_base}, ${item.allowances}, ${item.overtime})">Ajustar</button>
                    </td>
                </tr>
            `;
        }).join('');

        // Actualizar tabla principal
        const tbodyMain = document.querySelector('#table-payroll-details tbody');
        if (tbodyMain) tbodyMain.innerHTML = html;

        // Actualizar modal si es necesario
        const tbodyModal = document.querySelector('#table-pay-items tbody');
        if (tbodyModal) tbodyModal.innerHTML = html;
        
        if (showModal) {
            document.getElementById('pay-detail-title').innerText = `Detalle de Nómina: ${period.month}/${period.year}`;
            openModal('payroll-detail-modal');
        }
    }

    async function loadDevelopment() {
        const res = await api.fetchTraining();
        const trainings = res.data || res;
        const tbody = document.querySelector('#table-training tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(trainings) ? trainings : []).map(t => `
                <tr>
                    <td><strong>${t.title}</strong></td>
                    <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-radius: 6px;">${formatLabel(t.type)}</span></td>
                    <td>${t.scheduled_date}</td>
                    <td>${t.hours}h</td>
                    <td>${t.attendees_count || 0} Serv.</td>
                    <td><span class="badge" style="background: ${t.status === 'realizada' ? 'var(--hr-success)' : 'var(--hr-warning)'}; color: white; border-radius: 6px; font-size: 0.65rem;">${formatLabel(t.status)}</span></td>
                </tr>
            `).join('');
        }
    }

    async function loadSst() {
        const resC = await api.fetchCommittees();
        const committees = resC.data || resC;
        const resM = await api.fetchMeetings();
        const meetings = resM.data || resM;
        
        // Cargar Lista de Comités
        const committeesList = document.getElementById('committees-list');
        if (committeesList) {
            committeesList.innerHTML = (Array.isArray(committees) ? committees : []).map(c => `
                <div style="border: 1px solid var(--border); border-radius: 12px; padding: 15px; margin-bottom: 15px; background: rgba(255,255,255,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <h4 style="font-size: 0.85rem; color: var(--hr-primary); margin: 0;">${c.name}</h4>
                        <button class="nav-link" style="padding: 2px 8px; font-size: 9px;" onclick="prepareMemberModal(${c.id})">+ Miembro</button>
                    </div>
                    <p style="font-size: 0.65rem; color: #94a3b8; margin: 4px 0 10px 0;">Vigencia: ${c.valid_from || '?'} a ${c.valid_to || '?'}</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                        ${(c.members || []).map(m => `
                            <span class="badge" style="background: rgba(16,185,129,0.1); color: #6ee7b7; font-size: 9px;" title="${m.official ? m.official.full_name : 'N/A'}">${m.role}: ${m.official ? m.official.full_name.split(' ')[0] : 'N/A'}</span>
                        `).join('')}
                        ${(c.members || []).length === 0 ? '<span style="font-size: 0.65rem; color: var(--hr-danger);">Sin miembros asignados</span>' : ''}
                    </div>
                </div>
            `).join('');
        }

        // Cargar Tabla de Reuniones
        const tbodyMeetings = document.querySelector('#table-meetings tbody');
        if (tbodyMeetings) {
            tbodyMeetings.innerHTML = (Array.isArray(meetings) ? meetings : []).map(m => `
                <tr>
                    <td>${m.meeting_date}</td>
                    <td><span style="font-size: 10px; font-weight: 700; color: var(--hr-primary);">${m.committee ? m.committee.name : 'N/A'}</span></td>
                    <td><strong>${m.title}</strong></td>
                    <td><span class="badge" style="background: rgba(14,165,233,0.1); color: #38bdf8;">${m.status}</span></td>
                    <td>
                        <button class="nav-link" style="padding: 2px 8px; font-size: 10px;">Ver Acta</button>
                    </td>
                </tr>
            `).join('');
        }

        // Cargar Tabla de SST Records
        const resS = await api.fetchSst();
        const sstRecords = resS.data || resS;
        const tbodySst = document.querySelector('#table-sst-records tbody');
        if (tbodySst) {
            tbodySst.innerHTML = (Array.isArray(sstRecords) ? sstRecords : []).map(s => `
                <tr>
                    <td><strong>${s.official ? s.official.full_name : 'N/A'}</strong></td>
                    <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-radius: 6px;">${formatLabel(s.type)}</span></td>
                    <td>${s.record_date}</td>
                    <td style="font-size: 0.75rem; color: #94a3b8;">${s.findings || '-'}</td>
                </tr>
            `).join('');
        }
    }

    async function loadSituations() {
        const res = await api.fetchSituations();
        const data = res.data || res;
        const tbody = document.querySelector('#table-situations tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(data) ? data : []).map(s => `
                <tr>
                    <td><strong>${s.official ? s.official.full_name : 'N/A'}</strong></td>
                    <td><span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border-radius: 6px;">${formatLabel(s.type)}</span></td>
                    <td>${s.start_date}</td>
                    <td>${s.end_date}</td>
                    <td><span class="badge" style="background: ${s.status === 'aprobado' ? 'var(--hr-success)' : (s.status === 'rechazado' ? 'var(--hr-danger)' : 'var(--hr-warning)')}; color: white; border-radius: 6px; font-size: 0.65rem;">${formatLabel(s.status)}</span></td>
                    <td>
                        <div style="display: flex; gap: 4px;">
                            <button class="nav-link" style="padding: 2px 8px; font-size: 10px;" onclick="updateSit(${s.id}, 'aprobado')">Aprobar</button>
                            <button class="nav-link danger" style="padding: 2px 8px; font-size: 10px;" onclick="updateSit(${s.id}, 'rechazado')">Rechazar</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    }

    async function loadContracts() {
        const res = await api.fetchContracts();
        const data = res.data || res;
        const tbody = document.querySelector('#table-contracts tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(data) ? data : []).map(c => {
                const balanceColor = c.balance <= 0 ? '#10b981' : '#f59e0b';
                return `
                <tr>
                    <td><strong>${c.official ? c.official.full_name : 'N/A'}</strong></td>
                    <td style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: #94a3b8;">${c.contract_type || 'OPS'}</td>
                    <td>${c.contract_number}</td>
                    <td>$ ${new Intl.NumberFormat().format(c.total_contract_value || 0)}</td>
                    <td style="color: #10b981;">$ ${new Intl.NumberFormat().format(c.total_paid || 0)}</td>
                    <td style="color: ${balanceColor}; font-weight: bold;">$ ${new Intl.NumberFormat().format(c.balance || 0)}</td>
                    <td><span style="font-size: 0.7rem;">${c.start_date} a ${c.end_date}</span></td>
                    <td>
                        <button class="nav-link" style="padding: 2px 8px; font-size: 10px;" onclick="prepareDeductionModal(${c.id})" title="Configurar Estampillas">+ Estampilla</button>
                    </td>
                </tr>`;
            }).join('');
        }
    }

    async function loadEvaluation() {
        const res = await api.fetchEdl();
        const data = res.data || res;
        const tbody = document.querySelector('#table-edl tbody');
        if (tbody) {
            tbody.innerHTML = (Array.isArray(data) ? data : []).map(e => `
                <tr>
                    <td><strong>${e.official ? e.official.full_name : 'N/A'}</strong></td>
                    <td>${e.year}</td>
                    <td><span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border-radius: 6px;">${formatLabel(e.period)}</span></td>
                    <td style="font-size: 0.7rem; color: #94a3b8; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${e.compromises || '-'}</td>
                    <td><span style="font-weight: 800; color: ${e.score >= 90 ? 'var(--hr-success)' : (e.score >= 70 ? 'var(--hr-warning)' : 'var(--hr-danger)')}">${e.score || '-'} / 100</span></td>
                    <td><span class="badge" style="background: ${e.status === 'closed' ? 'var(--hr-success)' : 'var(--hr-warning)'}; color: white; border-radius: 6px; font-size: 0.65rem;">${formatLabel(e.status)}</span></td>
                    <td>
                        <button class="nav-link" style="padding: 2px 8px; font-size: 10px;" onclick="prepareScoreEdl(${e.id})">Calificar</button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function loadReports() {
        console.log("Sección de reportes lista");
    }

    async function generateReport(type) {
        window.location.href = `{{ url('api/hr/reports') }}?type=${type}`;
    }

    async function updateSit(id, status) {
        await fetch(`{{ url('api/hr/situations') }}/${id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ status })
        });
        loadPage('situations');
    }

    async function markAsPaid(id, event) {
        if(event) event.stopPropagation();
        if(!confirm('¿Confirmar que esta nómina ya fue pagada?')) return;
        await fetch(`{{ url('api/hr/payroll') }}/${id}/pay`, {
            method: 'PATCH',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        loadPayroll();
        loadDashboard();
    }

    async function deletePosition(id) {
        if(!confirm('¿Eliminar cargo?')) return;
        await fetch(`{{ url('api/hr/positions') }}/${id}`, { method: 'DELETE' });
        loadPage('positions');
    }

    async function deleteOfficial(id) {
        if(!confirm('¿Eliminar registro de servidor?')) return;
        await fetch(`{{ url('api/hr/officials') }}/${id}`, { method: 'DELETE' });
        loadPage('officials');
    }

    // MODALES
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    async function savePosition() {
        const data = {
            name: document.getElementById('pos-name').value,
            code: document.getElementById('pos-code').value,
            level: document.getElementById('pos-level').value,
            base_salary: document.getElementById('pos-salary').value,
        };
        await fetch('{{ url("/api/hr/positions") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('position-modal');
        loadPage('positions');
    }

    async function runPayroll() {
        const type = document.getElementById('pay-type').value;
        const endpoint = type === 'contractors' ? '{{ url("/api/hr/payroll/contractors") }}' : '{{ url("/api/hr/payroll") }}';
        
        const btn = document.querySelector('#payroll-modal .active');
        btn.innerText = 'Liquidando...';
        btn.disabled = true;
        
        const data = {
            month: document.getElementById('pay-month').value,
            year: document.getElementById('pay-year').value,
            clone_previous: document.getElementById('pay-clone').checked,
        };
        
        await fetch(endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        
        btn.innerText = 'Ejecutar Liquidación';
        btn.disabled = false;
        closeModal('payroll-modal');
        loadPage('payroll');
    }

    // FUNCIONES DE GUARDADO
    async function saveOfficial() {
        const data = {
            full_name: document.getElementById('off-name').value,
            document_number: document.getElementById('off-doc').value,
            position_id: document.getElementById('off-position').value,
            office_id: document.getElementById('off-office').value,
            employment_type: document.getElementById('off-type').value,
            entry_date: document.getElementById('off-entry').value,
        };
        await fetch('{{ url("/api/officials") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('official-modal');
        loadPage('officials');
    }

    async function saveCommittee() {
        const data = {
            name: document.getElementById('com-name').value,
            description: document.getElementById('com-desc').value,
            valid_from: document.getElementById('com-from').value,
            valid_to: document.getElementById('com-to').value,
        };
        await fetch('{{ url("/api/hr/committees") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('committee-modal');
        loadPage('sst');
    }

    function prepareMemberModal(committeeId) {
        document.getElementById('mem-committee-id').value = committeeId;
        populateDropdowns();
        openModal('member-modal');
    }

    async function addMember() {
        const data = {
            hr_committee_id: document.getElementById('mem-committee-id').value,
            official_id: document.getElementById('mem-official').value,
            role: document.getElementById('mem-role').value,
            appointment_date: document.getElementById('mem-date').value,
        };
        await fetch('{{ url("/api/hr/committees/members") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('member-modal');
        loadPage('sst');
    }

    async function saveMeeting() {
        const data = {
            hr_committee_id: document.getElementById('meet-committee').value,
            title: document.getElementById('meet-title').value,
            meeting_date: document.getElementById('meet-date').value,
            location: document.getElementById('meet-location').value,
            agenda: document.getElementById('meet-agenda').value,
            minutes_content: document.getElementById('meet-content').value,
        };
        await fetch('{{ url("/api/hr/meetings") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('meeting-modal');
        loadPage('sst');
    }

    async function saveSituation() {
        const data = {
            official_id: document.getElementById('sit-official').value,
            type: document.getElementById('sit-type').value,
            reason: document.getElementById('sit-reason').value,
            start_date: document.getElementById('sit-start').value,
            end_date: document.getElementById('sit-end').value,
        };
        await fetch('{{ url("/api/hr/situations") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('situation-modal');
        loadPage('situations');
    }

    async function saveTraining() {
        const data = {
            title: document.getElementById('train-title').value,
            type: document.getElementById('train-type').value,
            scheduled_date: document.getElementById('train-date').value,
            hours: document.getElementById('train-hours').value,
            description: document.getElementById('train-desc').value,
        };
        await fetch('{{ url("/api/hr/training") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('training-modal');
        loadPage('development');
    }

    function calculateMonthlyFromTotal() {
        const totalValue = parseFloat(document.getElementById('con-total-value').value) || 0;
        const startDate = new Date(document.getElementById('con-start').value);
        const endDate = new Date(document.getElementById('con-end').value);
        const monthlyInput = document.getElementById('con-value');

        if (totalValue > 0 && startDate && endDate && endDate > startDate) {
            // Calcular diferencia en meses
            let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
            months -= startDate.getMonth();
            months += endDate.getMonth();
            
            // Ajuste por días
            const diffDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
            const estimatedMonths = Math.max(1, Math.round(diffDays / 30.44)); // Promedio de días al mes

            const monthlyValue = (totalValue / estimatedMonths).toFixed(2);
            monthlyInput.value = monthlyValue;
        }
    }

    async function saveContract() {
        const data = {
            official_id: document.getElementById('con-official').value,
            contract_number: document.getElementById('con-number').value,
            cdp: document.getElementById('con-cdp').value,
            rp: document.getElementById('con-rp').value,
            rubro: document.getElementById('con-rubro').value,
            contract_type: document.getElementById('con-type').value,
            arl_risk_level: document.getElementById('con-arl').value,
            object: document.getElementById('con-object').value,
            supervisor_name: document.getElementById('con-supervisor-name').value,
            supervisor_position: document.getElementById('con-supervisor-pos').value,
            start_date: document.getElementById('con-start').value,
            end_date: document.getElementById('con-end').value,
            total_contract_value: document.getElementById('con-total-value').value,
            monthly_payment_value: document.getElementById('con-value').value,
        };
        await fetch('{{ url("/api/hr/contracts") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('contract-modal');
        loadPage('contracts');
    }

    function prepareDeductionModal(contractId) {
        document.getElementById('ded-contract-id').value = contractId;
        openModal('deduction-modal');
    }

    async function saveDeduction() {
        const data = {
            contract_id: document.getElementById('ded-contract-id').value,
            name: document.getElementById('ded-name').value,
            type: document.getElementById('ded-type').value,
            value: document.getElementById('ded-value').value,
        };
        await fetch('{{ url("/api/hr/contracts/deductions") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('deduction-modal');
        loadPage('contracts');
    }

    async function populateDropdowns() {
        const resP = await api.fetchPositions();
        const positions = resP.data || resP;
        const resO = await api.fetchOffices();
        const offices = resO.data || resO;
        const resF = await api.fetchOfficials();
        const officials = resF.data || resF;
        
        const posSelects = ['off-position'];
        const offSelects = ['off-office'];
        const officialSelects = ['mem-official', 'sit-official', 'con-official'];
        const commSelects = ['meet-committee'];

        posSelects.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = (Array.isArray(positions) ? positions : []).map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        });
        offSelects.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = (Array.isArray(offices) ? offices : []).map(o => `<option value="${o.id}">${o.name}</option>`).join('');
        });
        officialSelects.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = (Array.isArray(officials) ? officials : []).map(o => `<option value="${o.id}">${o.full_name}</option>`).join('');
        });
        
        const resC = await api.fetchCommittees();
        const committees = resC.data || resC;
        commSelects.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = (Array.isArray(committees) ? committees : []).map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        });

        const edlOfficialSelect = document.getElementById('edl-official');
        if(edlOfficialSelect) edlOfficialSelect.innerHTML = (Array.isArray(officials) ? officials : []).map(o => `<option value="${o.id}">${o.full_name}</option>`).join('');

        const sstOfficialSelect = document.getElementById('sst-official');
        if(sstOfficialSelect) sstOfficialSelect.innerHTML = (Array.isArray(officials) ? officials : []).map(o => `<option value="${o.id}">${o.full_name}</option>`).join('');
    }

    async function saveSstRecord() {
        const data = {
            official_id: document.getElementById('sst-official').value,
            type: document.getElementById('sst-type').value,
            record_date: document.getElementById('sst-date').value,
            provider_name: document.getElementById('sst-provider').value,
            findings: document.getElementById('sst-findings').value,
        };
        await fetch('{{ url("/api/hr/sst") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('sst-record-modal');
        loadPage('sst');
    }

    async function saveEdl() {
        const data = {
            official_id: document.getElementById('edl-official').value,
            year: document.getElementById('edl-year').value,
            period: document.getElementById('edl-period').value,
            compromises: document.getElementById('edl-compromises').value,
        };
        await fetch('{{ url("/api/hr/edl") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('edl-modal');
        loadPage('evaluation');
    }

    function prepareScoreEdl(id) {
        document.getElementById('edl-score-id').value = id;
        openModal('edl-score-modal');
    }

    async function submitEdlScore() {
        const id = document.getElementById('edl-score-id').value;
        const data = {
            score: document.getElementById('edl-score-val').value,
            feedback: document.getElementById('edl-score-feedback').value,
            status: 'closed'
        };
        await fetch(`{{ url("/api/hr/edl") }}/${id}/score`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        });
        closeModal('edl-score-modal');
        loadPage('evaluation');
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        loadDashboard();
        populateDropdowns();
    });
</script>
@endsection
