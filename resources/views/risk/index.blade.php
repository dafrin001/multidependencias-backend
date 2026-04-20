@extends('layouts.admin')

@section('title', 'Gestión del Riesgo')
@section('page_title', 'Gestión del Riesgo (RUFE)')
@section('page_subtitle', 'Registro Unifamiliar de Emergencias y Control de Riesgos')

@section('styles')
<style>
    :root {
        --risk-primary: #f97316; /* Orange - Alert */
        --risk-secondary: #fb923c;
        --risk-success: #10b981;
        --risk-warning: #f59e0b;
        --risk-danger: #ef4444;
        --bg-card: rgba(15, 23, 42, 0.6);
        --border: rgba(255, 255, 255, 0.08);
    }

    .risk-tabs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* Miniaturized */
        gap: 8px;
        margin-bottom: 16px;
    }
    .risk-tab {
        background: var(--bg-card);
        border: 1px solid var(--border);
        padding: 10px 6px; /* Reduced from 16x8 */
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        backdrop-filter: blur(12px);
    }
    .risk-tab:hover {
        background: rgba(249, 115, 22, 0.1);
        border-color: var(--risk-primary);
        transform: translateY(-4px);
        box-shadow: 0 10px 20px -5px rgba(249, 115, 22, 0.2);
    }
    .risk-tab.active {
        background: linear-gradient(135deg, var(--risk-primary), #ea580c);
        color: white;
        border-color: transparent;
        box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
    }
    .risk-tab .tab-icon { font-size: 1.2rem; } /* Reduced from 1.5 */
    .risk-tab .tab-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }

    /* PAGINAS */
    .risk-page { display: none; width: 100%; }
    .risk-page.active { display: block; animation: slideUp 0.4s ease-out forwards; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .risk-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* More compact */
        gap: 12px;
        margin-bottom: 20px;
    }
    .risk-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px; /* Reduced from 24 */
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        overflow: hidden;
    }
    .risk-stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%);
        pointer-events: none;
    }
    .risk-stat-val { font-size: 1.4rem; font-weight: 800; font-family: 'Outfit', sans-serif; display: block; line-height: 1; }
    .risk-stat-label { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }

    /* TABLES */
    .risk-table-container {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow-x: auto; /* Ensure horizontal scroll */
        backdrop-filter: blur(10px);
    }
    .risk-table { width: 100%; border-collapse: collapse; min-width: 800px; } /* Set min-width for scroll */
    .risk-table th {
        background: rgba(255,255,255,0.03);
        padding: 10px 16px; /* Reduced from 16x24 */
        text-align: left;
        font-size: 0.7rem; /* Smaller */
        text-transform: uppercase;
        color: #94a3b8;
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .risk-table td { padding: 10px 16px; font-size: 0.8rem; border-bottom: 1px solid var(--border); color: #e2e8f0; }
    .risk-table tr:hover { background: rgba(255,255,255,0.02); }

    .risk-form-section {
        background: rgba(30, 41, 59, 0.4);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px; /* Reduced from 24 */
        margin-bottom: 16px;
    }
    .risk-form-title {
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--risk-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .risk-input-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* More columns fit */
        gap: 12px;
    }
    .risk-field label { display: block; font-size: 0.65rem; font-weight: 600; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; }
    .risk-field input, .risk-field select, .risk-field textarea {
        width: 100%;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 6px 10px; /* Reduced from 10x14 */
        color: white;
        outline: none;
        transition: all 0.2s;
        font-size: 0.8rem;
    }
    .risk-field input:focus, .risk-field select:focus {
        border-color: var(--risk-primary);
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
    }

    /* MODALES */
    .risk-modal {
        display: none;
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(10px);
        z-index: 1200;
        align-items: center; justify-content: center;
        padding: 24px;
    }
    .risk-modal-content {
        background: #1e293b;
        padding: 24px; /* Reduced from 40 */
        border-radius: 20px;
        width: 95%; /* Better space usage on mobile */
        max-width: 900px;
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 30px 60px -12px rgba(0,0,0,0.6);
    }

    /* LEGEND BOX */
    .legend-box {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px;
        font-size: 0.7rem;
        color: #94a3b8;
    }
    .legend-title { font-weight: 700; color: #e2e8f0; margin-bottom: 5px; text-decoration: underline; }

    /* BUTTONS */
    .btn-risk {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-risk-primary { background: var(--risk-primary); color: white; border: none; }
    .btn-risk-primary:hover { background: #ea580c; transform: scale(1.02); }
    .btn-risk-outline { background: transparent; color: #e2e8f0; border: 1px solid var(--border); }
    .btn-risk-outline:hover { background: rgba(255,255,255,0.05); }

    /* PRINT STYLES */
    @media print {
        .sidebar, .top-nav, .risk-tabs, .btn-risk, #risk-pages > section:not(.active), .risk-form-title button {
            display: none !important;
        }
        body, .main-content { background: white !important; color: black !important; padding: 0 !important; margin: 0 !important; }
        .risk-modal { position: relative; background: white; display: block !important; }
        .risk-modal-content { box-shadow: none; border: none; padding: 0; max-width: 100%; }
        .risk-table-container { border: 1px solid #000; }
        .risk-table th, .risk-table td { color: black !important; border-bottom: 1px solid #000; }
        .risk-form-section { border: 1px solid #000; background: none; }
    }
</style>
@endsection

@section('content')

<!-- HEADER SECTION -->
<div style="background: linear-gradient(135deg, #27272a, #09090b); border-radius: 24px; padding: 40px; margin-bottom: 30px; border: 1px solid var(--border); position: relative; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.4);">
    <div style="position: absolute; right: -30px; top: -30px; font-size: 10rem; opacity: 0.08;">🔥</div>
    <div style="max-width: 700px; position: relative; z-index: 1;">
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(249, 115, 22, 0.15); color: #fb923c; padding: 6px 14px; border-radius: 100px; font-size: 0.75rem; font-weight: 700; margin-bottom: 20px; border: 1px solid rgba(249, 115, 22, 0.2);">
            <span class="pulse" style="width: 8px; height: 8px; background: #f97316; border-radius: 50%; display: inline-block;"></span>
            SISTEMA DE GESTIÓN DEL RIESGO - RUFE
        </div>
        <h1 style="font-size: 2.2rem; margin-bottom: 15px; font-family: 'Outfit', sans-serif; font-weight: 800; letter-spacing: -0.5px;">Panel de Emergencias y Desastres</h1>
        <p style="color: #94a3b8; font-size: 1rem; line-height: 1.6; margin-bottom: 25px;">Registro Unifamiliar de Emergencias (RUFE) para la atención integral, censo de damnificados y evaluación de daños en el municipio.</p>
        <div style="display: flex; gap: 15px;">
            <button class="btn-risk btn-risk-primary" onclick="showPage('create')">
                <span>➕</span> Nuevo Registro RUFE
            </button>
            <button class="btn-risk btn-risk-outline" onclick="showPage('records')">
                <span>📋</span> Ver Historial
            </button>
        </div>
    </div>
</div>

<!-- NAVEGACIÓN -->
<div class="risk-tabs">
    <div class="risk-tab active" onclick="showPage('dashboard', this)">
        <span class="tab-icon">🏠</span>
        <span class="tab-label">Inicio</span>
    </div>
    <div class="risk-tab" onclick="showPage('records', this)">
        <span class="tab-icon">🗒️</span>
        <span class="tab-label">Registros</span>
    </div>
    <div class="risk-tab" onclick="showPage('create', this)">
        <span class="tab-icon">✏️</span>
        <span class="tab-label">Nuevo RUFE</span>
    </div>
    <div class="risk-tab" onclick="showPage('stats', this)">
        <span class="tab-icon">📈</span>
        <span class="tab-label">Estadísticas</span>
    </div>
    <div class="risk-tab" onclick="showPage('reports', this)">
        <span class="tab-icon">📥</span>
        <span class="tab-label">Reportes</span>
    </div>
</div>

<div id="risk-pages">
    <!-- DASHBOARD -->
    <section class="risk-page active" id="page-dashboard">
        <div class="risk-stats-grid">
            <div class="risk-stat-card" style="border-left: 4px solid var(--risk-primary);">
                <div style="font-size: 2.5rem;">🚨</div>
                <div>
                    <span class="risk-stat-val" id="stat-total-records">0</span>
                    <span class="risk-stat-label">Total Registros RUFE</span>
                </div>
            </div>
            <div class="risk-stat-card" style="border-left: 4px solid var(--risk-danger);">
                <div style="font-size: 2.5rem;">🏘️</div>
                <div>
                    <span class="risk-stat-val" id="stat-destroid-assets">0</span>
                    <span class="risk-stat-label">Bienes Destruidos</span>
                </div>
            </div>
            <div class="risk-stat-card" style="border-left: 4px solid var(--risk-warning);">
                <div style="font-size: 2.5rem;">👨‍👩‍👧‍👦</div>
                <div>
                    <span class="risk-stat-val" id="stat-total-people">0</span>
                    <span class="risk-stat-label">Personas Sensadas</span>
                </div>
            </div>
            <div class="risk-stat-card" style="border-left: 4px solid var(--risk-success);">
                <div style="font-size: 2.5rem;">🌽</div>
                <div>
                    <span class="risk-stat-val" id="stat-agro-affected">0</span>
                    <span class="risk-stat-label">Hectáreas Afectadas</span>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div class="risk-table-container">
                <div style="padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1.1rem; margin:0; font-weight: 700;">Últimas Emergencias Registradas</h3>
                    <button class="btn-risk btn-risk-outline" style="padding: 4px 12px; font-size: 0.75rem;" onclick="showPage('records')">Ver Todo</button>
                </div>
                <table class="risk-table" id="table-recent-rufe">
                    <thead>
                        <tr><th>ID</th><th>Registro</th><th>Municipio</th><th>Evento</th><th>Fecha Suceso</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="text-align:center; padding: 40px; color:#94a3b8;">Cargando registros...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="risk-table-container">
                <div style="padding: 24px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 1.1rem; margin:0; font-weight: 700;">Tipos de Evento</h3>
                </div>
                <div id="chart-events" style="padding: 24px; height: 300px; display: flex; align-items: center; justify-content: center; color:#94a3b8;">
                    [Gráfico de Distribución]
                </div>
            </div>
        </div>
    </section>

    <!-- LISTADO DE REGISTROS -->
    <section class="risk-page" id="page-records">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 1.5rem; font-weight: 800;">Historial Formatos RUFE</h2>
            <div style="display: flex; gap: 12px;">
                <input type="text" id="rufe-search" placeholder="Buscar por evento o municipio..." style="background: rgba(15,23,42,0.8); border: 1px solid var(--border); border-radius: 12px; padding: 10px 18px; color: white; outline: none; width: 300px;">
                <button class="btn-risk btn-risk-primary" onclick="loadRufeRecords()">🔍</button>
            </div>
        </div>
        <div class="risk-table-container">
            <table class="risk-table" id="table-all-rufe">
                <thead>
                    <tr><th>ID</th><th>Registro</th><th>Municipio</th><th>Evento</th><th>Fecha Suceso</th><th>Estado</th><th>Opciones</th></tr>
                </thead>
                <tbody></tbody>
            </table>
            <div id="rufe-pagination" style="padding: 20px; display: flex; justify-content: center; gap: 10px;"></div>
        </div>
    </section>

    <!-- FORMULARIO NUEVO REGISTRO -->
    <section class="risk-page" id="page-create">
        <form id="rufe-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="risk-form-section">
                    <div class="risk-form-title"><span>📍</span> Información General</div>
                    <div class="risk-input-group">
                        <div class="risk-field">
                            <label>Departamento</label>
                            <select name="departamento" id="rufe-dept" onchange="updateMunicipalities()">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="risk-field">
                            <label>Municipio</label>
                            <select name="municipio" id="rufe-muni">
                                <option value="">Seleccione departamento primero</option>
                            </select>
                        </div>
                        <div class="risk-field"><label>Evento</label><input type="text" name="evento" placeholder="Ej: Inundación, Vendaval"></div>
                        <div class="risk-field"><label>Fecha Evento</label><input type="date" name="fecha_evento"></div>
                        <div class="risk-field"><label>Fecha RUFE</label><input type="date" name="fecha_rufe"></div>
                    </div>
                </div>

                <div class="risk-form-section">
                    <div class="risk-form-title"><span>🏠</span> Ubicación y Tenencia</div>
                    <div class="risk-input-group">
                        <div class="risk-field">
                            <label>Ubicación</label>
                            <select name="ubicacion_tipo">
                                <option value="URBANO">Urbano</option>
                                <option value="RURAL">Rural</option>
                            </select>
                        </div>
                        <div class="risk-field"><label>Corregimiento</label><input type="text" name="corregimiento"></div>
                        <div class="risk-field"><label>Vereda/Sector/Barrio</label><input type="text" name="vereda_sector_barrio"></div>
                        <div class="risk-field" style="grid-column: span 2;"><label>Dirección</label><input type="text" name="direccion"></div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="risk-form-section">
                    <div class="risk-form-title"><span>🛡️</span> Estado del Bien</div>
                    <div class="risk-input-group">
                        <div class="risk-field">
                            <label>Forma de Tenencia</label>
                            <select name="forma_tenencia">
                                <option value="ARRENDATARIO">Arrendatario</option>
                                <option value="OCUPANTE">Ocupante</option>
                                <option value="POSEEDOR">Poseedor</option>
                                <option value="PROPIETARIO">Propietario</option>
                                <option value="NO INFORMA">No Informa</option>
                            </select>
                        </div>
                        <div class="risk-field">
                            <label>Estado del Bien</label>
                            <select name="estado_bien">
                                <option value="HABITABLE">Habitable</option>
                                <option value="NO HABITABLE">No Habitable</option>
                                <option value="DESTRUIDO">Destruido</option>
                                <option value="NO INFORMA">No Informa</option>
                                <option value="AVERIADO">Averiado</option>
                            </select>
                        </div>
                        <div class="risk-field">
                            <label>Tipo de Bien</label>
                            <select name="tipo_bien">
                                <option value="VIVIENDA">Vivienda</option>
                                <option value="FINCA">Finca</option>
                                <option value="LOCAL COMERCIAL">Local Comercial</option>
                                <option value="FABRICA">Fábrica</option>
                                <option value="BODEGA">Bodega</option>
                                <option value="LOTE">Lote</option>
                                <option value="CENTRO DE BIENESTAR">Centro de Bienestar</option>
                                <option value="CENTRO EDUCATIVO">Centro Educativo/Escuela</option>
                                <option value="HOSPITAL">Hospital</option>
                                <option value="ESTADIO">Estadio</option>
                                <option value="IGLESIA">Iglesia/Religioso</option>
                                <option value="ALCALDIA">Alcaldía Municipal</option>
                                <option value="POLICIA">Estación de Policía</option>
                            </select>
                        </div>
                        <div class="risk-field">
                            <label>Alojamiento Actual</label>
                            <select name="alojamiento_actual_tipo">
                                <option value="HABITUAL">Lugar Habitual de Residencia</option>
                                <option value="EVACUADO">Evacuado fuera de su Residencia</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="risk-form-section">
                    <div class="risk-form-title"><span>🗨️</span> Observaciones Finales</div>
                    <div class="risk-field"><label>Comentarios Adicionales</label><textarea name="observaciones" style="height: 100px; resize: none;"></textarea></div>
                    <div class="risk-field" style="margin-top: 10px;"><label>Vo.Bo. CMGRD/CDGRD</label><input type="text" name="vo_bo"></div>
                </div>
            </div>

            <!-- TABLA DEMOGRÁFICA -->
            <div class="risk-form-section">
                <div class="risk-form-title" style="justify-content: space-between;">
                    <span>👥 Información Demográfica</span>
                    <button type="button" class="btn-risk btn-risk-outline" style="padding: 2px 10px; font-size: 0.7rem;" onclick="addDemographicRow()">+ Agregar Persona</button>
                </div>
                <div class="risk-table-container">
                    <table class="risk-table" id="table-form-demographics">
                        <thead>
                            <tr><th>Nombre(s)</th><th>Apellido(s)</th><th>Documento</th><th>Parentezco</th><th>Género</th><th>Nacimiento</th><th>Étnia</th><th>Teléfono</th><th></th></tr>
                        </thead>
                        <tbody>
                            <!-- Una fila inicial -->
                        </tbody>
                    </table>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px;">
                    <div class="legend-box">
                        <div class="legend-title">DOCS</div>
                        1. Registro Civil | 2. TI | 3. CC | 4. CE | 5. Pasaporte | 10. Otro
                    </div>
                    <div class="legend-box">
                        <div class="legend-title">PARENT.</div>
                        1. Jefe | 2. Pareja | 3. Hijo | 4. Abuelo | 5. Sobrino | 15. No informa
                    </div>
                    <div class="legend-box">
                        <div class="legend-title">GÉNERO</div>
                        1. Masculino | 2. Femenino | 3. Transgénero
                    </div>
                    <div class="legend-box">
                        <div class="legend-title">ÉTNIA</div>
                        1. Indígena | 2. ROM | 3. Raizal | 4. Palenquero | 6. No aplica
                    </div>
                </div>
            </div>

            <!-- TABLA AGROPECUARIA -->
            <div class="risk-form-section">
                <div class="risk-form-title" style="justify-content: space-between;">
                    <span>🌾 Sector Agropecuario</span>
                    <button type="button" class="btn-risk btn-risk-outline" style="padding: 2px 10px; font-size: 0.7rem;" onclick="addAgroRow()">+ Agregar Afectación</button>
                </div>
                <div class="risk-table-container">
                    <table class="risk-table" id="table-form-agro">
                        <thead>
                            <tr><th>Tipo de Cultivo</th><th>Unidad Medida</th><th>Área (Cantidad)</th><th>Sector Pecuario (Especie)</th><th>Cantidad (Unidades)</th><th></th></tr>
                        </thead>
                        <tbody>
                            <!-- Una fila inicial -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 20px; padding-bottom: 50px;">
                <button type="button" class="btn-risk btn-risk-outline" style="padding: 12px 30px;" onclick="showPage('dashboard')">Cancelar</button>
                <button type="submit" class="btn-risk btn-risk-primary" style="padding: 12px 50px; font-size: 1rem;">✅ GUARDAR REGISTRO RUFE</button>
            </div>
        </form>
    </section>

    <!-- ESTADÍSTICAS -->
    <section class="risk-page" id="page-stats">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="risk-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="margin:0; font-size: 1rem;">Distribución de Emergencias por Evento</h3>
                </div>
                <div style="padding: 20px; height: 350px;">
                    <canvas id="chart-pie-events"></canvas>
                </div>
            </div>
            <div class="risk-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="margin:0; font-size: 1rem;">Registros por Municipio (Top 5)</h3>
                </div>
                <div style="padding: 20px; height: 350px;">
                    <canvas id="chart-bar-municipios"></canvas>
                </div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="risk-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="margin:0; font-size: 1rem;">Distribución por Género</h3>
                </div>
                <div style="padding: 20px; height: 300px;">
                    <canvas id="chart-doughnut-gender"></canvas>
                </div>
            </div>
            <div class="risk-table-container">
                <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                    <h3 style="margin:0; font-size: 1rem;">Tendencia de Emergencias</h3>
                </div>
                <div style="padding: 20px; height: 300px;">
                    <canvas id="chart-line-trend"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- REPORTES -->
    <section class="risk-page" id="page-reports">
        <div style="background: rgba(14, 165, 233, 0.05); border-left: 4px solid var(--risk-primary); padding: 20px; border-radius: 0 16px 16px 0; margin-bottom: 30px;">
            <p style="margin: 0; font-size: 0.95rem; color: #94a3b8;">Exporte la base de datos de damnificados y evaluaciones para envío a la UNGRD o entidades nacionales.</p>
        </div>
        <div class="risk-stats-grid">
            <div class="risk-stat-card" style="cursor:pointer;" onclick="downloadReport('all')">
                <div style="font-size: 2rem;">📊</div>
                <div>
                    <span style="font-weight: 700; display: block;">Base Completa RUFE</span>
                    <span style="font-size: 0.7rem; color: #94a3b8;">Todos los campos en formato Excel.</span>
                </div>
            </div>
            <div class="risk-stat-card" style="cursor:pointer;" onclick="downloadReport('people')">
                <div style="font-size: 2rem;">👥</div>
                <div>
                    <span style="font-weight: 700; display: block;">Censo Poblacional</span>
                    <span style="font-size: 0.7rem; color: #94a3b8;">Listado de personas afectadas.</span>
                </div>
            </div>
            <div class="risk-stat-card" style="cursor:pointer;" onclick="downloadReport('agro')">
                <div style="font-size: 2rem;">🚜</div>
                <div>
                    <span style="font-weight: 700; display: block;">Pérdidas Agro</span>
                    <span style="font-size: 0.7rem; color: #94a3b8;">Inventario de cultivos y animales.</span>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL VER DETALLE -->
<div class="risk-modal" id="modal-rufe-detail">
    <div class="risk-modal-content">
        <div style="display:flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 24px;">
            <div>
                <h2 style="margin:0; font-size: 1.8rem; font-weight: 800; color: var(--risk-primary);" id="detail-title">FORMATO RUFE</h2>
                <p style="margin:0; color: #94a3b8;" id="detail-subtitle">Cargando...</p>
            </div>
            <div style="display:flex; gap:10px;">
                <button class="btn-risk btn-risk-primary" onclick="printRufe()">🖨️ Imprimir RUFE</button>
                <button class="btn-risk btn-risk-outline" onclick="closeModal('modal-rufe-detail')">Cerrar</button>
            </div>
        </div>
        
        <div id="rufe-detail-container" style="display: grid; gap: 30px;">
             <!-- Dinámico -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const API_BASE = '{{ url("api/risk-management") }}';
    
    // Colombia Geo Data (Expanded Thoroughly)
    const colombiaData = {
        "AMAZONAS": ["LETICIA", "PUERTO NARIÑO", "LA CHORRERA", "EL ENCANTO", "LA PEDRERA", "TARAPACÁ", "PUERTO ALEGRÍA", "PUERTO ARICA"],
        "ANTIOQUIA": ["MEDELLÍN", "BELLO", "ITAGÜÍ", "ENVIGADO", "APARTADÓ", "TURBO", "CAUCASIA", "RIONEGRO", "SABANETA", "COPACABANA", "CHIGORODÓ", "CAREPA", "MUTATÁ", "DABEIBA", "SANTA FE DE ANTIOQUIA", "YARUMAL", "SONSON", "AMALFI", "SEGOVIA", "REMEDIOS", "EL CARMEN DE VIBORAL", "MARINILLA"],
        "ARAUCA": ["ARAUCA", "TAME", "SARAVENA", "ARAUQUITA", "FORTUL", "PUERTO RONDÓN", "CRAVO NORTE"],
        "ATLÁNTICO": ["BARRANQUILLA", "SOLEDAD", "MALAMBO", "SABANALARGA", "PUERTO COLOMBIA", "BARANOA", "PALMAR DE VARELA", "SANTO TOMÁS", "GALAPA", "SABANAGRANDE"],
        "BOLÍVAR": ["CARTAGENA", "MAGANGUÉ", "TURBACO", "ARJONA", "EL CARMEN DE BOLÍVAR", "MARIA LA BAJA", "MOMPÓS", "TURBANÁ", "SANTA ROSA", "SAN JACINTO", "ACHÍ"],
        "BOYACÁ": ["TUNJA", "SOGAMOSO", "DUITAMA", "CHIQUINQUIRÁ", "PAIPA", "MONIQUIRÁ", "PUERTO BOYACÁ", "GUATEQUE", "SANTANA", "VILLA DE LEYVA"],
        "CALDAS": ["MANIZALES", "LA DORADA", "RIOSUCIO", "CHINCHINÁ", "VILLAMARÍA", "ANSERMA", "NEIRA", "PACORA", "SALAMINA"],
        "CAQUETÁ": ["FLORENCIA", "SAN VICENTE DEL CAGUÁN", "PUERTO RICO", "CARTAGENA DEL CHAIRÁ", "CURILLO", "EL DONCELLO", "PAUJIL"],
        "CASANARE": ["YOPAL", "AGUAZUL", "PAZ DE ARIPORO", "VILLANUEVA", "MONTERREY", "TAURAMENA", "SAN LUIS DE PALENQUE"],
        "CAUCA": ["POPAYÁN", "SANTANDER DE QUILICHAO", "PUERTO TEJADA", "PATÍA", "EL TAMBO", "PIENDAMÓ", "GUAPI", "TIERRA ADENTRO"],
        "CESAR": ["VALLEDUPAR", "AGUACHICA", "AGUSTÍN CODAZZI", "PAZ DEL RÍO", "BOSCONIA", "CURUMANÍ", "EL PASO", "LA PAZ"],
        "CHOCÓ": ["QUIBDÓ", "ISTMINA", "CONDOTO", "NUEVO BELÉN DE BAJIRÁ", "RÍOSUCIO", "CARMEN DEL DARIÉN", "ACANDÍ", "UNGUÍA", "TADÓ", "BAGADÓ", "BAHÍA SOLANO", "BAJO BAUDÓ", "BOJAYÁ", "CANTÓN DE SAN PABLO", "CÉRTEGUI", "EL CARMEN DE ATRATO", "JURADÓ", "LITORAL DEL SAN JUAN", "LLORÓ", "MEDIO ATRATO", "MEDIO BAUDÓ", "MEDIO SAN JUAN", "NÓVITA", "NUQUÍ", "PIAMONTE", "RÍO IRÓ", "RÍO QUITO", "SAN JOSÉ DEL PALMAR", "SIPÍ", "UNIÓN PANAMERICANA"],
        "CÓRDOBA": ["MONTERÍA", "CERETÉ", "SAHAGÚN", "LORICA", "MONTELÍBANO", "PLANETA RICA", "TIERRALTA", "AYAPEL", "CIÉNAGA DE ORO", "CHINÚ"],
        "CUNDINAMARCA": ["BOGOTÁ", "SOACHA", "FACATATIVÁ", "CHÍA", "ZIPAQUIRÁ", "GIRARDOT", "FUSAGASUGÁ", "MOSQUERA", "FUNZA", "CAJICÁ", "MADRID", "Ubaté", "Villeta"],
        "GUAINÍA": ["INÍRIDA", "BARRANCO MINAS", "MAPIRIPANA", "PUERTO COLOMBIA"],
        "GUAVIARE": ["SAN JOSÉ DEL GUAVIARE", "CALAMAR", "EL RETORNO", "MIRAFLORES"],
        "HUILA": ["NEIVA", "PITALITO", "GARZÓN", "LA PLATA", "GIGANTE", "CAMPOALEGRE", "RIVERA", "SAN AGUSTÍN"],
        "LA GUAJIRA": ["RIOHACHA", "MAICAO", "MANAURE", "URIBIA", "URUMITA", "BARRANCAS", "FONSECA", "SAN JUAN DEL CESAR", "DIBULLA", "HATO NUEVO", "VILLANUEVA"],
        "MAGDALENA": ["SANTA MARTA", "CIÉNAGA", "FUNDACIÓN", "EL BANCO", "PLATO", "ARACATACA", "SIVIA", "GUAMAL", "PIVIJAY"],
        "META": ["VILLAVICENCIO", "ACACÍAS", "GRANADA", "PUERTO LÓPEZ", "PUERTO GAITÁN", "SAN MARTÍN", "MESETAS"],
        "NARIÑO": ["PASTO", "TUMACO", "IPIALES", "TÚQUERRES", "SAMANIEGO", "CUMBAL", "BARBACOAS", "LA UNIÓN"],
        "NORTE DE SANTANDER": ["CÚCUTA", "OCAÑA", "VILLA DEL ROSARIO", "LOS PATIOS", "PAMPLONA", "TIBÚ", "ABREGO", "EL ZULIA", "SARDINATA"],
        "PUTUMAYO": ["MOCOA", "PUERTO ASÍS", "VALLE DEL GUAMUEZ", "ORITO", "SIBUNDOY", "VILLAGARZÓN", "PUERTO LEGUÍZAMO"],
        "QUINDÍO": ["ARMENIA", "CALARCÁ", "MONTENEGRO", "QUIMBAYA", "LA TEBAIDA", "CIRCASIA", "FILANDIA", "SALENTO"],
        "RISARALDA": ["PEREIRA", "DOSQUEBRADAS", "SANTA ROSA DE CABAL", "LA VIRGINIA", "BELÉN DE UMBRÍA", "QUINCHÍA", "MARSELLA"],
        "SAN ANDRÉS": ["SAN ANDRÉS", "PROVIDENCIA"],
        "SANTANDER": ["BUCARAMANGA", "FLORIDABLANCA", "BARRANCABERMEJA", "GIRÓN", "PIEDECUESTA", "SAN GIL", "SOCORRO", "VÉLEZ", "PUENTE NACIONAL", "SABANA DE TORRES"],
        "SUCRE": ["SINCELEJO", "COROZAL", "SAN MARCOS", "SAMPUÉS", "TOLÚ", "MAJAGUAL", "COVEÑAS", "OVEJAS"],
        "TOLIMA": ["IBAGUÉ", "ESPINAL", "CHAPARRAL", "LÍBANO", "HONDA", "MARIQUITA", "MELGAR", "FLANDES", "PURIFICACIÓN"],
        "VALLE DEL CAUCA": ["CALI", "BUENAVENTURA", "PALMIRA", "TULUÁ", "CARTAGO", "BUGA", "JAMUNDÍ", "YUMBO", "CAICEDONIA", "SEVILLA", "ZARZAL"],
        "VAUPÉS": ["MITÚ", "CARURÚ", "TARAIRA", "PACOA"],
        "VICHADA": ["PUERTO CARREÑO", "LA PRIMAVERA", "SANTA ROSALÍA", "CUMARIBO"]
    };

    function initGeography() {
        const deptSelect = document.getElementById('rufe-dept');
        if (!deptSelect) return;
        deptSelect.innerHTML = '<option value="">Seleccione Departamento...</option>';
        Object.keys(colombiaData).sort().forEach(dept => {
            const opt = document.createElement('option');
            opt.value = dept;
            opt.innerText = dept;
            deptSelect.appendChild(opt);
        });
    }

    function updateMunicipalities(selectedDept = null, selectedMuni = null) {
        const dept = selectedDept || document.getElementById('rufe-dept').value;
        const muniSelect = document.getElementById('rufe-muni');
        if (!muniSelect) return;
        
        muniSelect.innerHTML = '<option value="">Seleccione Municipio...</option>';
        if (dept && colombiaData[dept]) {
            colombiaData[dept].sort().forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.innerText = m;
                if (selectedMuni && m === selectedMuni) opt.selected = true;
                muniSelect.appendChild(opt);
            });
        }
    }

    // Navegación
    function showPage(pageId, tabElement) {
        document.querySelectorAll('.risk-page').forEach(p => p.classList.remove('active'));
        document.getElementById('page-' + pageId).classList.add('active');

        if (tabElement) {
            document.querySelectorAll('.risk-tab').forEach(t => t.classList.remove('active'));
            tabElement.classList.add('active');
        }

        if (pageId === 'create') {
            if (!isEditing) {
                document.getElementById('rufe-form').reset();
                initGeography();
            }
        }

        if (pageId === 'records' || pageId === 'dashboard') loadRufeRecords();
        if (pageId === 'stats') loadStats();
    }

    // Modales
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { 
        document.getElementById(id).style.display = 'none'; 
        if(id === 'modal-rufe-detail') document.getElementById('rufe-detail-container').innerHTML = ''; // Limpiar al cerrar
    }

    let isEditing = false;
    let currentEditId = null;

    // Cargar registros
    async function loadRufeRecords(page = 1) {
        try {
            const searchInput = document.getElementById('rufe-search');
            const search = searchInput ? searchInput.value : '';
            const res = await axios.get(`${API_BASE}?page=${page}&search=${search}`);
            const data = res.data;

            // Dashboard actualiza con la data de stats para ser exacto
            const statsRes = await axios.get(`${API_BASE}/stats`);
            const stats = statsRes.data.summary;

            const updateText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            };

            updateText('stat-total-records', stats.total_records);
            updateText('stat-total-people', stats.total_people);
            updateText('stat-destroid-assets', stats.destroyed_assets);
            updateText('stat-agro-affected', stats.total_agro_hectareas);

            const tbodyAll = document.querySelector('#table-all-rufe tbody');
            const tbodyRecent = document.querySelector('#table-recent-rufe tbody');
            
            let html = '';
            data.data.forEach(r => {
                const dateRufe = r.fecha_rufe ? r.fecha_rufe.split('T')[0] : 'N/A';
                const dateEvent = r.fecha_evento ? r.fecha_evento.split('T')[0] : 'N/A';
                
                let badgeClass = 'risk-success';
                if(['DESTRUIDO', 'NO HABITABLE'].includes(r.estado_bien)) badgeClass = 'risk-danger';
                if(['AVERIADO'].includes(r.estado_bien)) badgeClass = 'risk-warning';

                html += `
                    <tr>
                        <td style="font-weight:700; color:var(--risk-primary)">#${r.id}</td>
                        <td style="font-family: monospace; font-size: 0.75rem;">${dateRufe}</td>
                        <td>${r.municipio}</td>
                        <td><strong>${r.evento}</strong></td>
                        <td style="font-family: monospace; font-size: 0.75rem;">${dateEvent}</td>
                        <td><span class="badge" style="background: var(--${badgeClass}); color: white; border-radius: 6px; font-size: 0.65rem;">${r.estado_bien}</span></td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="nav-link" style="padding: 4px; display: flex; align-items: center; justify-content: center;" onclick="viewDetails(${r.id})" title="Ver Detalle"><i data-lucide="eye" style="width:14px; height:14px;"></i></button>
                                <button class="nav-link" style="padding: 4px; display: flex; align-items: center; justify-content: center;" onclick="editRecord(${r.id})" title="Editar"><i data-lucide="edit-3" style="width:14px; height:14px;"></i></button>
                                <button class="nav-link danger" style="padding: 4px; display: flex; align-items: center; justify-content: center; color: var(--risk-danger);" onclick="deleteRecord(${r.id})" title="Eliminar"><i data-lucide="trash-2" style="width:14px; height:14px;"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            if (tbodyAll) tbodyAll.innerHTML = html || '<tr><td colspan="7" style="text-align:center">No hay registros</td></tr>';
            if (tbodyRecent) tbodyRecent.innerHTML = html.split('</tr>').slice(0, 5).join('</tr>') || '<tr><td colspan="7" style="text-align:center">No hay registros</td></tr>';

            // Re-rener lucide icons after injecting HTML
            if (window.lucide) window.lucide.createIcons();

        } catch (e) { console.error(e); }
    }

    // Agregar filas al form
    function addDemographicRow() {
        const tbody = document.querySelector('#table-form-demographics tbody');
        if (!tbody) return;
        const count = tbody.children.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="demographics[${count}][nombres]" required></td>
            <td><input type="text" name="demographics[${count}][apellidos]" required></td>
            <td>
                <div style="display:flex; flex-direction:column; gap:2px;">
                    <select name="demographics[${count}][tipo_documento]" required style="width:100px; font-size:0.7rem;">
                        <option value="CC">C.C.</option>
                        <option value="TI">T.I.</option>
                        <option value="RC">R.C.</option>
                        <option value="CE">C.E.</option>
                        <option value="PPT">P.P.T.</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <input type="text" name="demographics[${count}][numero_documento]" required style="width:100px" placeholder="Número">
                </div>
            </td>
            <td>
                <select name="demographics[${count}][parentesco]" style="width:110px">
                    <option value="Jefe de Hogar">Jefe de Hogar</option>
                    <option value="Pareja">Pareja</option>
                    <option value="Hijo(a)">Hijo(a)</option>
                    <option value="Abuelo(a)">Abuelo(a)</option>
                    <option value="Nieto(a)">Nieto(a)</option>
                    <option value="Hermano(a)">Hermano(a)</option>
                    <option value="Sobrino(a)">Sobrino(a)</option>
                    <option value="Tío(a)">Tío(a)</option>
                    <option value="Primo(a)">Primo(a)</option>
                    <option value="Otro">Otro</option>
                </select>
            </td>
            <td>
                <select name="demographics[${count}][genero]">
                    <option value="MASCULINO">M</option>
                    <option value="FEMENINO">F</option>
                    <option value="TRANSGENERO">T</option>
                </select>
            </td>
            <td><input type="date" name="demographics[${count}][fecha_nacimiento]"></td>
            <td>
                <select name="demographics[${count}][pertenencia_etnica]" style="width:100px">
                    <option value="No aplica">No aplica</option>
                    <option value="Indígena">Indígena</option>
                    <option value="Rom">Rom</option>
                    <option value="Raizal">Raizal</option>
                    <option value="Palenquero">Palenquero</option>
                    <option value="Afrocolombiano">Afrocolombiano</option>
                </select>
            </td>
            <td><input type="text" name="demographics[${count}][telefono]" style="width:90px"></td>
            <td><button type="button" class="btn-risk btn-risk-outline" style="color:var(--risk-danger)" onclick="this.parentElement.parentElement.remove()">×</button></td>
        `;
        tbody.appendChild(tr);
    }

    function addAgroRow() {
        const tbody = document.querySelector('#table-form-agro tbody');
        if (!tbody) return;
        const count = tbody.children.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="agros[${count}][tipo_cultivo]"></td>
            <td><input type="text" name="agros[${count}][unidad_medida]"></td>
            <td><input type="text" name="agros[${count}][area_cantidad]"></td>
            <td><input type="text" name="agros[${count}][sector_pecuario_especie]"></td>
            <td><input type="text" name="agros[${count}][cantidad_unidades]"></td>
            <td><button type="button" class="btn-risk btn-risk-outline" style="color:var(--risk-danger)" onclick="this.parentElement.parentElement.remove()">×</button></td>
        `;
        tbody.appendChild(tr);
    }

    // Submit Form
    const rufeForm = document.getElementById('rufe-form');
    if (rufeForm) {
        rufeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = serializeForm(e.target);

            try {
                const url = isEditing ? `${API_BASE}/${currentEditId}` : API_BASE;
                const method = isEditing ? 'PUT' : 'POST';
                
                await axios({
                    method: method,
                    url: url,
                    data: payload
                });

                Swal.fire('Éxito', isEditing ? 'Registro actualizado correctamente' : 'Registro RUFE guardado correctamente', 'success');
                
                // Reset state
                isEditing = false;
                currentEditId = null;
                e.target.reset();
                const tbodyDemo = document.querySelector('#table-form-demographics tbody');
                const tbodyAgro = document.querySelector('#table-form-agro tbody');
                if (tbodyDemo) tbodyDemo.innerHTML = '';
                if (tbodyAgro) tbodyAgro.innerHTML = '';
                addDemographicRow();
                addAgroRow();
                
                showPage('records');
                loadRufeRecords();
            } catch (err) {
                Swal.fire('Error', 'No se pudo procesar el registro', 'error');
            }
        });
    }

    async function editRecord(id) {
        try {
            const res = await axios.get(`${API_BASE}/${id}`);
            const r = res.data;
            
            isEditing = true;
            currentEditId = id;
            
            showPage('create');
            
            // Poblar campos base
            const form = document.getElementById('rufe-form');
            if (form) {
                const setVal = (name, val) => {
                    const el = form.querySelector(`[name="${name}"]`);
                    if (el) el.value = val || '';
                };

                setVal('departamento', r.departamento);
                updateMunicipalities(r.departamento, r.municipio);
                
                setVal('evento', r.evento);
                setVal('fecha_evento', r.fecha_evento);
                setVal('fecha_rufe', r.fecha_rufe);
                setVal('ubicacion_tipo', r.ubicacion_tipo);
                setVal('corregimiento', r.corregimiento);
                setVal('vereda_sector_barrio', r.vereda_sector_barrio);
                setVal('direccion', r.direccion);
                setVal('forma_tenencia', r.forma_tenencia);
                setVal('estado_bien', r.estado_bien);
                setVal('tipo_bien', r.tipo_bien);
                setVal('alojamiento_actual_tipo', r.alojamiento_actual_tipo);
                setVal('observaciones', r.observaciones);
                setVal('vo_bo', r.vo_bo);

                // Poblar demografía
                const tbodyDemo = document.querySelector('#table-form-demographics tbody');
                if (tbodyDemo) {
                    tbodyDemo.innerHTML = '';
                    r.demographics.forEach((d, idx) => {
                        addDemographicRow();
                        const row = tbodyDemo.children[idx];
                        const setRowVal = (fname, val) => {
                            const el = row.querySelector(`[name="demographics[${idx}][${fname}]"]`);
                            if (el) el.value = val || '';
                        };
                        setRowVal('nombres', d.nombres);
                        setRowVal('apellidos', d.apellidos);
                        setRowVal('tipo_documento', d.tipo_documento);
                        setRowVal('numero_documento', d.numero_documento);
                        setRowVal('parentesco', d.parentesco);
                        setRowVal('genero', d.genero);
                        setRowVal('fecha_nacimiento', d.fecha_nacimiento);
                        setRowVal('pertenencia_etnica', d.pertenencia_etnica);
                        setRowVal('telefono', d.telefono);
                    });
                }

                // Poblar agro
                const tbodyAgro = document.querySelector('#table-form-agro tbody');
                if (tbodyAgro) {
                    tbodyAgro.innerHTML = '';
                    r.agros.forEach((a, idx) => {
                        addAgroRow();
                        const row = tbodyAgro.children[idx];
                        const setRowVal = (fname, val) => {
                            const el = row.querySelector(`[name="agros[${idx}][${fname}]"]`);
                            if (el) el.value = val || '';
                        };
                        setRowVal('tipo_cultivo', a.tipo_cultivo);
                        setRowVal('unidad_medida', a.unidad_medida);
                        setRowVal('area_cantidad', a.area_cantidad);
                        setRowVal('sector_pecuario_especie', a.sector_pecuario_especie);
                        setRowVal('cantidad_unidades', a.cantidad_unidades);
                    });
                }
            }

            Swal.fire('Edición Activa', `Cargando datos del registro #${id}`, 'info');
        } catch (e) {
            Swal.fire('Error', 'No se pudieron cargar los datos para edición', 'error');
        }
    }

    function serializeForm(form) {
        const obj = {};
        const formData = new FormData(form);
        const demographics = [];
        const agros = [];

        for (let [key, value] of formData.entries()) {
            if (key.startsWith('demographics')) {
                const match = key.match(/\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const idx = match[1];
                    const field = match[2];
                    if (!demographics[idx]) demographics[idx] = {};
                    demographics[idx][field] = value;
                }
            } else if (key.startsWith('agros')) {
                const match = key.match(/\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const idx = match[1];
                    const field = match[2];
                    if (!agros[idx]) agros[idx] = {};
                    agros[idx][field] = value;
                }
            } else {
                obj[key] = value;
            }
        }
        obj.demographics = demographics.filter(Boolean);
        obj.agros = agros.filter(Boolean);
        return obj;
    }

    async function viewDetails(id) {
        try {
            const res = await axios.get(`${API_BASE}/${id}`);
            const r = res.data;
            const subtitle = document.getElementById('detail-subtitle');
            if (subtitle) subtitle.innerText = `Evento: ${r.evento} - ${r.municipio} (${r.fecha_rufe})`;
            
            const container = document.getElementById('rufe-detail-container');
            if (container) {
                container.innerHTML = `
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="risk-form-section">
                            <div class="risk-form-title">🏠 Datos Generales</div>
                            <p><strong>Ubicación:</strong> ${r.ubicacion_tipo} - ${r.direccion || 'N/A'}</p>
                            <p><strong>Vereda/Barrio:</strong> ${r.vereda_sector_barrio || 'N/A'}</p>
                            <p><strong>Tenencia:</strong> ${r.forma_tenencia}</p>
                            <p><strong>Estado del Bien:</strong> ${r.estado_bien}</p>
                        </div>
                        <div class="risk-form-section">
                            <div class="risk-form-title">📝 Novedades</div>
                            <p><strong>Alojamiento:</strong> ${r.alojamiento_actual_tipo}</p>
                            <p><strong>Observaciones:</strong> ${r.observaciones || 'Sin observaciones.'}</p>
                            <p><strong>Vo.Bo:</strong> ${r.vo_bo || 'Pendiente.'}</p>
                        </div>
                    </div>
                    <div>
                       <h3 style="margin-bottom:15px">👥 Censo de Personas (${r.demographics.length})</h3>
                       <div class="risk-table-container">
                        <table class="risk-table">
                            <thead><tr><th>Nombre</th><th>Documento</th><th>Género</th><th>Teléfono</th></tr></thead>
                            <tbody>
                                ${r.demographics.map(d => `<tr><td>${d.nombres} ${d.apellidos}</td><td>${d.numero_documento}</td><td>${d.genero}</td><td>${d.telefono || '-'}</td></tr>`).join('')}
                            </tbody>
                        </table>
                       </div>
                    </div>
                `;
            }
            openModal('modal-rufe-detail');
        } catch (e) {
            Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
        }
    }

    async function deleteRecord(id) {
        if (!confirm('¿Desea eliminar este registro permanentemente?')) return;
        try {
            await axios.delete(`${API_BASE}/${id}`);
            loadRufeRecords();
            Swal.fire('Eliminado', 'El registro ha sido borrado', 'info');
        } catch (e) {
            Swal.fire('Error', 'No se pudo eliminar', 'error');
        }
    }

    function printRufe() {
        window.print();
    }

    function downloadReport(type) {
        Swal.fire('Generando Reporte', 'El archivo se descargará en unos segundos...', 'success');
        window.location.href = `${API_BASE}/export?type=${type}`;
    }

    let eventsChart, municipiosChart, genderChart, trendChart;

    async function loadStats() {
        try {
            const res = await axios.get(`${API_BASE}/stats`);
            const data = res.data;

            const ctxEventsEl = document.getElementById('chart-pie-events');
            if (ctxEventsEl) {
                const ctxEvents = ctxEventsEl.getContext('2d');
                if (eventsChart) eventsChart.destroy();
                eventsChart = new Chart(ctxEvents, {
                    type: 'pie',
                    data: {
                        labels: data.charts.events_by_type.map(e => e.evento),
                        datasets: [{
                            data: data.charts.events_by_type.map(e => e.count),
                            backgroundColor: ['#f97316', '#ef4444', '#f59e0b', '#10b981', '#6366f1']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8' } } } }
                });
            }

            const ctxMunEl = document.getElementById('chart-bar-municipios');
            if (ctxMunEl) {
                const ctxMun = ctxMunEl.getContext('2d');
                if (municipiosChart) municipiosChart.destroy();
                municipiosChart = new Chart(ctxMun, {
                    type: 'bar',
                    data: {
                        labels: data.charts.records_by_municipio.map(m => m.municipio),
                        datasets: [{
                            label: 'Registros',
                            data: data.charts.records_by_municipio.map(m => m.count),
                            backgroundColor: 'rgba(249, 115, 22, 0.6)',
                            borderColor: '#f97316',
                            borderWidth: 1
                        }]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        scales: { 
                            y: { beginAtZero: true, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            x: { ticks: { color: '#94a3b8' }, grid: { display: false } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            const ctxGenEl = document.getElementById('chart-doughnut-gender');
            if (ctxGenEl) {
                const ctxGen = ctxGenEl.getContext('2d');
                if (genderChart) genderChart.destroy();
                genderChart = new Chart(ctxGen, {
                    type: 'doughnut',
                    data: {
                        labels: data.charts.people_by_gender.map(g => g.genero),
                        datasets: [{
                            data: data.charts.people_by_gender.map(g => g.count),
                            backgroundColor: ['#6366f1', '#ec4899', '#94a3b8']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { color: '#94a3b8' } } } }
                });
            }

            const ctxTrendEl = document.getElementById('chart-line-trend');
            if (ctxTrendEl) {
                const ctxTrend = ctxTrendEl.getContext('2d');
                if (trendChart) trendChart.destroy();
                trendChart = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: data.charts.monthly_trend.map(t => t.month),
                        datasets: [{
                            label: 'Frecuencia Mensual',
                            data: data.charts.monthly_trend.map(t => t.count),
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        scales: { 
                            y: { beginAtZero: true, ticks: { color: '#94a3b8' } },
                            x: { ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            }
            
            renderDashboardSmallChart(data.charts.events_by_type);

        } catch (e) {
            console.error("Error loading stats:", e);
        }
    }

    function renderDashboardSmallChart(eventData) {
        const container = document.getElementById('chart-events');
        if (!container) return;
        
        container.innerHTML = '<canvas id="dashboard-events-chart"></canvas>';
        new Chart(document.getElementById('dashboard-events-chart'), {
            type: 'polarArea',
            data: {
                labels: eventData.map(e => e.evento),
                datasets: [{
                    data: eventData.map(e => e.count),
                    backgroundColor: ['rgba(249, 115, 22, 0.6)', 'rgba(239, 68, 68, 0.6)', 'rgba(245, 158, 11, 0.6)', 'rgba(16, 185, 129, 0.6)']
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { r: { ticks: { display: false }, grid: { color: 'rgba(255,255,255,0.05)' } } }
            }
        });
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', () => {
        initGeography();
        loadRufeRecords();
        loadStats(); // Render charts immediately on dashboard
        if (document.querySelector('#table-form-demographics tbody')) {
            addDemographicRow();
        }
        if (document.querySelector('#table-form-agro tbody')) {
            addAgroRow();
        }
    });
</script>
@endsection
