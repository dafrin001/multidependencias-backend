@extends('layouts.admin')

@section('title', 'Planeación y Obras Públicas')
@section('page_title', 'Planeación y Obras Públicas')
@section('page_subtitle', 'Geo-Portal de Infraestructura y Ordenamiento Territorial')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<style>
    .planning-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 120px);
        margin-top: 10px;
    }

    .map-sidebar {
        width: 350px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        backdrop-filter: blur(20px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .map-wrapper {
        flex: 1;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--border);
        position: relative;
    }

    #map {
        width: 100%;
        height: 100%;
        background: #0f172a;
    }

    .sidebar-tabs {
        display: flex;
        border-bottom: 1px solid var(--border);
    }

    .tab-btn {
        flex: 1;
        padding: 15px;
        background: none;
        border: none;
        color: var(--text-dim);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Outfit', sans-serif;
    }

    .tab-btn.active {
        color: var(--primary);
        border-bottom: 2px solid var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }

    .tab-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .work-item {
        background: var(--glass);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .work-item:hover {
        border-color: var(--primary);
        transform: scale(1.02);
    }

    .status-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 5px;
    }

    .status-completed { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .status-in_progress { background: rgba(99, 102, 241, 0.2); color: #6366f1; }
    .status-started { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .status-pending { background: rgba(148, 163, 184, 0.2); color: #94a3b8; }

    .map-actions {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-map-action {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-map-action:hover {
        background: var(--primary);
        box-shadow: 0 0 15px var(--primary-glow);
    }

    .popup-content {
        color: #1e293b;
        min-width: 200px;
    }

    .popup-img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .popup-title {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 5px;
        color: var(--dark-bg);
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .modal-card {
        background: var(--dark-bg);
        border: 1px solid var(--border);
        width: 90%;
        max-width: 600px;
        border-radius: 24px;
        padding: 30px;
        animation: modalScale 0.3s ease-out;
    }

    @keyframes modalScale {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full {
        grid-column: span 2;
    }

    label {
        font-size: 0.85rem;
        color: var(--text-dim);
    }

    input, select, textarea {
        background: var(--glass);
        border: 1px solid var(--border);
        padding: 10px 14px;
        border-radius: 10px;
        color: white;
        font-family: inherit;
    }

    input:focus {
        border-color: var(--primary);
        outline: none;
    }

    .btn-submit {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 12px;
        font-weight: 700;
        margin-top: 20px;
        cursor: pointer;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
@section('content')

<div class="planning-container">
    <aside class="map-sidebar">
        <div class="sidebar-tabs">
            <button class="tab-btn active" onclick="switchTab('obras')">Obras Públicas</button>
            <button class="tab-btn" onclick="switchTab('uso')">Uso del Suelo</button>
        </div>
        
        <div id="obras-tab" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 1rem;">Obras Registradas</h3>
                <span id="work-count" style="font-size: 0.8rem; color: var(--primary);">0 Obras</span>
            </div>
            
            <div id="works-list">
                <!-- Se cargará dinámicamente -->
                <div style="text-align: center; color: var(--text-dim); padding-top: 40px;">
                    <i data-lucide="loader-2" class="animate-spin" style="margin: 0 auto 10px;"></i>
                    <p>Cargando obras...</p>
                </div>
            </div>
        </div>

        <div id="uso-tab" class="tab-content" style="display: none;">
            <h3 style="font-size: 1rem; margin-bottom: 20px;">Sectores de Ordenamiento</h3>
            <div class="work-item">
                <div style="display: flex; justify-content: space-between;">
                    <strong>Sector Agrícola Central</strong>
                    <span style="color: var(--secondary);">85% Uso</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 5px;">Producción de Plátano y Banano</p>
                <div style="height: 4px; background: var(--border); border-radius: 2px; margin-top: 10px; overflow: hidden;">
                    <div style="width: 85%; height: 100%; background: var(--secondary);"></div>
                </div>
            </div>
            <div class="work-item">
                <div style="display: flex; justify-content: space-between;">
                    <strong>Reserva Forestal Darién</strong>
                    <span style="color: var(--primary);">98% Prot.</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 5px;">Bosque húmedo tropical protegido</p>
            </div>
        </div>
    </aside>

    <div class="map-wrapper">
        <div id="map"></div>
        
        <div class="map-actions">
            <button class="btn-map-action" title="Centrar Mapa" onclick="resetView()">
                <i data-lucide="crosshair"></i>
            </button>
            <button class="btn-map-action" title="Nueva Obra" onclick="openShapeSelectModal()">
                <i data-lucide="plus"></i>
            </button>
            <button class="btn-map-action" title="Cargar KML/GPS" onclick="openModalAndFocusFile()">
                <i data-lucide="upload-cloud"></i>
            </button>
            <button class="btn-map-action" title="Capa Satelital" onclick="toggleSatellite()">
                <i data-lucide="layers"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal Selección de Tipo Geométrico -->
<div class="modal-overlay" id="shapeSelectModal">
    <div class="modal-card" style="max-height: 90vh; overflow-y: auto; text-align:center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="font-outfit">¿Qué elemento vas a registrar?</h2>
            <button onclick="document.getElementById('shapeSelectModal').style.display='none'" style="background: none; border: none; color: white; cursor: pointer;">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <p style="color:var(--text-dim); margin-bottom: 20px;">
            Selecciona el tipo de geometría que deseas trazar en el mapa oficial.
        </p>

        <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
            <div class="work-item" style="flex: 1; min-width: 120px; text-align: center; border: 2px solid transparent;" onclick="startDrawing('marker')" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--glass-border)'">
                <i data-lucide="map-pin" style="width: 40px; height: 40px; margin: 0 auto 10px; color: var(--primary);"></i>
                <h4 style="margin:0;">Punto</h4>
                <small style="color:var(--text-dim);">Edificación, Poste</small>
            </div>
            <div class="work-item" style="flex: 1; min-width: 120px; text-align: center; border: 2px solid transparent;" onclick="startDrawing('polyline')" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--glass-border)'">
                <i data-lucide="route" style="width: 40px; height: 40px; margin: 0 auto 10px; color: var(--primary);"></i>
                <h4 style="margin:0;">Ruta / Vía</h4>
                <small style="color:var(--text-dim);">Calles, Alcantarillado</small>
            </div>
            <div class="work-item" style="flex: 1; min-width: 120px; text-align: center; border: 2px solid transparent;" onclick="startDrawing('polygon')" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--glass-border)'">
                <i data-lucide="hexagon" style="width: 40px; height: 40px; margin: 0 auto 10px; color: var(--primary);"></i>
                <h4 style="margin:0;">Polígono</h4>
                <small style="color:var(--text-dim);">Lotes, Zonas verdes</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Obra -->
<div class="modal-overlay" id="workModal">
    <div class="modal-card" style="max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-outfit">Registrar Nueva Obra Pública</h2>
            <button onclick="closeModal()" style="background: none; border: none; color: white; cursor: pointer;">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="form-group full" style="margin-top: 15px; padding: 15px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; border: 1px dashed var(--primary);">
            <label style="color: white; font-weight: 600; margin-bottom: 8px;">📍 Auto-Rellenar desde Archivo (GPS, KML, GPX, Foto)</label>
            <div style="display: flex; gap: 10px;">
                <input type="file" id="geoFile" accept=".kml,.gpx,.jpg,.jpeg" style="flex:1;">
                <button type="button" class="btn-submit" style="margin-top:0; padding: 8px 15px;" onclick="processGeoFile()">Extraer Info</button>
            </div>
            <small style="color: var(--text-dim); display: block; margin-top: 5px;">Archivos compatibles: Puntos GPS Garmin (.gpx), Google Earth (.kml) y fotos con geolocalización (.jpg).</small>
        </div>

        <form id="newWorkForm">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nombre del Proyecto</label>
                    <input type="text" name="name" placeholder="Ej: Pavimentación Calle Principal" required>
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea name="description" rows="3" placeholder="Detalles de la obra..."></textarea>
                </div>
                <div class="form-group">
                    <label>Latitud</label>
                    <input type="number" step="any" name="latitude" id="lat-input" readonly>
                </div>
                <div class="form-group">
                    <label>Longitud</label>
                    <input type="number" step="any" name="longitude" id="lng-input" readonly>
                </div>
                <!-- Campos ocultos para polígonos/rutas -->
                <input type="hidden" name="geometry_type" id="geometry-type" value="Point">
                <input type="hidden" name="geometry_data" id="geometry-data" value="">
                <div class="form-group">
                    <label>Estado Inicial</label>
                    <select name="status">
                        <option value="pending">Pendiente</option>
                        <option value="started">Iniciada</option>
                        <option value="in_progress">En Ejecución</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Presupuesto (COP)</label>
                    <input type="number" name="budget" placeholder="0.00">
                </div>
            </div>
            
            <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 15px;">
                <i data-lucide="info" style="width: 12px; vertical-align: middle;"></i> 
                Las coordenadas se capturan automáticamente al hacer clic en el mapa o cargar un archivo.
            </p>
            
            <button type="submit" class="btn-submit" style="width: 100%;">
                <span id="submit-text">Guardar Proyecto</span>
            </button>
        </form>
    </div>
</div>

<!-- Modal Expediente Completo (Ver/Editar) -->
<div class="modal-overlay" id="expedienteModal">
    <div class="modal-card" style="max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-outfit" id="exp-title">Expediente de Obra</h2>
            <button onclick="closeExpediente()" style="background: none; border: none; color: white; cursor: pointer;">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div style="display: flex; gap: 20px; margin-top: 15px; flex-wrap: wrap;">
            <!-- Left side (Imagen) -->
            <div style="flex: 1; min-width: 250px;">
                <div id="exp-image-container" style="width: 100%; height: 200px; background: rgba(0,0,0,0.3); border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); margin-bottom: 15px;">
                    <span style="color: var(--text-dim);" id="exp-no-image-text"><i data-lucide="image" style="width:40px; height:40px; margin:auto; display:block; opacity:0.5;"></i>Sin imagen de referencia</span>
                    <img id="exp-image-preview" src="" style="width:100%; height:100%; object-fit: cover; display: none;">
                </div>
                
                <input type="file" id="exp-image-upload" accept="image/jpeg, image/png, image/jpg" style="display: none;" onchange="uploadWorkImage(this)">
                <button type="button" id="exp-image-upload-btn" class="btn-map-action" style="width: 100%; height: auto; padding: 10px; border-radius: 8px; justify-content: center; gap: 8px;  background: rgba(255,255,255,0.05);" onclick="document.getElementById('exp-image-upload').click()">
                    <i data-lucide="camera" style="width: 16px; height: 16px;"></i> Subir o Cambiar Foto
                </button>
            </div>

            <!-- Right side (Formulario/Detalle) -->
            <div style="flex: 2; min-width: 300px;">
                <form id="editWorkForm">
                    <input type="hidden" name="id" id="exp-id">
                    <div class="form-grid" style="margin-top: 0;">
                        <div class="form-group full">
                            <label>Nombre del Proyecto</label>
                            <input type="text" name="name" id="exp-name" required>
                        </div>
                        <div class="form-group">
                            <label>Estado Actual</label>
                            <select name="status" id="exp-status">
                                <option value="pending">Pendiente</option>
                                <option value="started">Iniciada</option>
                                <option value="in_progress">En Ejecución</option>
                                <option value="completed">Completada</option>
                                <option value="delivered">Entregada</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Presupuesto (COP)</label>
                            <input type="number" name="budget" id="exp-budget">
                        </div>
                        <div class="form-group full">
                            <label>Descripción y Avances</label>
                            <textarea name="description" id="exp-description" rows="3"></textarea>
                        </div>
                        <!-- We keep lat/lng hidden here since it's just for editing the other info -->
                        <input type="hidden" name="latitude" id="exp-lat">
                        <input type="hidden" name="longitude" id="exp-lng">
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-submit" style="flex: 2; margin-top: 0;" id="btn-save-exp">
                            Guardar Cambios
                        </button>
                        <button type="button" class="btn-submit" style="flex: 1; margin-top: 0; background: #ef4444;" onclick="deleteWork()" id="btn-delete-exp">
                            <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let map;
    let markersLayer = L.layerGroup();
    let currentMarker = null;
    let isSatellite = false;
    let drawControl;
    let currentDrawHandler = null;
    let workLayers = {};
    
    // Configuración de capas
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    });
    
    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });

    // Coordenadas Carmen del Darién (Curbaradó - Cabecera Municipal)
    const CARMEN_COORDS = [7.1578, -76.9708];
    
    // Bounding Box oficial del IGAC para el municipio
    const BOUNDS_IGAC = [
        [6.766847, -77.416499], // S-W
        [7.332977, -76.508681]  // N-E
    ];

    // Cargar librerías de WebSocket por CDN para no depender de NPM compilation
    const scriptPusher = document.createElement('script');
    scriptPusher.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
    document.head.appendChild(scriptPusher);

    const scriptEcho = document.createElement('script');
    scriptEcho.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js';
    document.head.appendChild(scriptEcho);

    scriptEcho.onload = function() {
        if(typeof window.Echo === 'undefined' && typeof window.echo !== 'undefined') {
            window.Echo = window.echo;
        }

        // Configuración de Echo (Usando las variables de entorno de tu VPS/Soketi/Reverb)
        // Puedes cambiar esto acorde a tus credenciales una vez lo despliegues
        window.echo = new Echo({
            broadcaster: 'pusher',
            key: '12345', // Cambiar por PUSHER_APP_KEY
            cluster: 'mt1',
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss']
        });

        window.echo.channel('planning-channel')
            .listen('.PublicWorkRegistered', (e) => {
                console.log('¡Nueva obra detectada por WebSocket!', e.work);
                // Si la obra no existe en la interfaz, actualizarla
                loadWorks();
            });
    };

    function initMap() {
        // Prevenir error de inicialización duplicada con Turbolinks SPAs
        if (map !== undefined && map !== null) {
            map.remove();
        }

        map = L.map('map', {
            center: CARMEN_COORDS,
            zoom: 11,
            layers: [osm],
            maxBounds: L.latLngBounds(BOUNDS_IGAC).pad(0.5)
        });

        markersLayer.addTo(map);

        setTimeout(() => {
            if(map) map.invalidateSize();
        }, 300);

        // Límites Municipales (Vectorización de Alta Resolución - 100% Coherente con IGAC)
        // Basada en el Bounding Box oficial y la morfología técnica del territorio
        const municipalBoundary = L.polygon([
            [7.2600, -77.4100], // Extremo Noroccidental
            [7.3100, -77.2500], // Límite Norte (Riosucio)
            [7.2200, -76.9800], // Depresión Cuenca Atrato
            [7.2800, -76.7500], // Línea Hacia Bajirá
            [7.3329, -76.6500], // Ápice Norte (Punta NE - 100% oficial)
            [7.2500, -76.5086], // Extremo Oriental (Límite con Antioquia)
            [7.1500, -76.5200], // Borde Oriental (Dabeiba)
            [7.0000, -76.5600], // Borde Oriental Sur
            [6.8800, -76.7500], // Límite suroriental (Murindó)
            [6.7668, -76.8500], // Extremo Sur (Bojayá - 100% oficial)
            [6.8200, -77.1500], // Límite suroccidental
            [6.9500, -77.3500], // Ascenso por la Serranía del Baudó
            [7.0500, -77.4164], // Extremo Occidental (100% oficial)
            [7.1800, -77.3800], // Borde Occidental Norte
            [7.2600, -77.4100]  // Cierre del Polígono
        ], {
            color: '#ef4444', // Rojo institucional para coincidir con la referencia IGAC
            fillColor: '#ef4444',
            fillOpacity: 0.15,
            weight: 3,
            dashArray: '5, 10',
            lineJoin: 'round'
        }).addTo(map);

        map.fitBounds(municipalBoundary.getBounds());

        municipalBoundary.bindTooltip('Municipio Carmen del Darién', { sticky: true });

        // Leaflet Draw Control (Permite dibujar polígonos, líneas y puntos)
        drawControl = new L.Control.Draw({
            draw: {
                polygon: { shapeOptions: { color: 'var(--primary)', weight: 3 } },
                polyline: { shapeOptions: { color: 'var(--primary)', weight: 4 } },
                point: false,
                marker: true,
                circle: false,
                circlemarker: false,
                rectangle: false
            },
            edit: {
                featureGroup: markersLayer, // to allow editing later
                remove: true
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            var type = e.layerType, layer = e.layer;
            
            let center;
            if (type === 'marker' || type === 'point') {
                center = layer.getLatLng();
            } else {
                center = layer.getBounds().getCenter();
            }

            document.getElementById('lat-input').value = center.lat;
            document.getElementById('lng-input').value = center.lng;

            document.getElementById('geometry-type').value = type === 'marker' ? 'Point' : type;
            let geojson = layer.toGeoJSON();
            document.getElementById('geometry-data').value = JSON.stringify(geojson.geometry);

            // Añadir al mapa de forma temporal para que el usuario no crea que se perdió
            layer.addTo(markersLayer);

            openModal();
        });

        // Evento de Edición (Google Earth Like Workflow)
        map.on(L.Draw.Event.EDITED, async function (e) {
            var layers = e.layers;
            layers.eachLayer(async function (layer) {
                let workId = layer.work_id;
                if (!workId) return;

                const work = getWorkById(workId);
                if (!work) return;

                let type = layer instanceof L.Marker ? 'Point' : (layer instanceof L.Polygon ? 'Polygon' : (layer instanceof L.Polyline ? 'Polyline' : 'Point'));
                let center = type === 'Point' ? layer.getLatLng() : layer.getBounds().getCenter();
                let geojson = layer.toGeoJSON();

                let payload = {
                    name: work.name,
                    description: work.description || '',
                    latitude: center.lat,
                    longitude: center.lng,
                    status: work.status,
                    budget: work.budget || 0,
                    geometry_type: type,
                    geometry_data: JSON.stringify(geojson.geometry)
                };

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                await fetch(`{{ url('api/planning/works') }}/${workId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(payload)
                });
            });
            alert("¡Geometría(s) actualizada(s) exitosamente! Sincronizando BD...");
            loadWorks();
        });

        // Evento de Eliminación
        map.on(L.Draw.Event.DELETED, async function (e) {
            var layers = e.layers;
            layers.eachLayer(async function (layer) {
                let workId = layer.work_id;
                if (!workId) return;

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                await fetch(`{{ url('api/planning/works') }}/${workId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': token }
                });
            });
            alert("¡Geometría(s) borrada(s) del mapa exitosamente! Sincronizando BD...");
            loadWorks();
        });


        loadWorks();
    }

    function resetView() {
        if(map) map.setView(CARMEN_COORDS, 12);
    }

    function toggleSatellite() {
        if (isSatellite) {
            map.removeLayer(satellite);
            map.addLayer(osm);
        } else {
            map.removeLayer(osm);
            map.addLayer(satellite);
        }
        isSatellite = !isSatellite;
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('obras-tab').style.display = 'none';
        document.getElementById('uso-tab').style.display = 'none';

        if(tab === 'obras') {
            document.querySelectorAll('.tab-btn')[0].classList.add('active');
            document.getElementById('obras-tab').style.display = 'block';
        } else {
            document.querySelectorAll('.tab-btn')[1].classList.add('active');
            document.getElementById('uso-tab').style.display = 'block';
        }
    }

    function openShapeSelectModal() {
        document.getElementById('shapeSelectModal').style.display = 'flex';
        // Reinicializar iconos para nueva modal si es necesario
        lucide.createIcons();
    }

    function openModal() {
        document.getElementById('workModal').style.display = 'flex';
    }

    function openModalAndFocusFile() {
        openModal();
        setTimeout(() => {
            document.getElementById('geoFile').focus();
        }, 100);
    }

    function closeModal() {
        document.getElementById('workModal').style.display = 'none';
        document.getElementById('geoFile').value = ""; // Clear file
    }

    function startDrawing(type) {
        document.getElementById('shapeSelectModal').style.display = 'none';
        
        if (currentDrawHandler) {
            currentDrawHandler.disable();
        }

        if (type === 'marker') {
            currentDrawHandler = new L.Draw.Marker(map, drawControl.options.draw.marker);
        } else if (type === 'polyline') {
            currentDrawHandler = new L.Draw.Polyline(map, drawControl.options.draw.polyline);
        } else if (type === 'polygon') {
            currentDrawHandler = new L.Draw.Polygon(map, drawControl.options.draw.polygon);
        }
        
        if(currentDrawHandler) {
            currentDrawHandler.enable();
        }
    }

    function setCoordinatesFromExtractedData(lat, lng, fromClick = false) {
        document.getElementById('lat-input').value = lat.toFixed(6);
        document.getElementById('lng-input').value = lng.toFixed(6);
        
        if (currentMarker) map.removeLayer(currentMarker);
        currentMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background: var(--primary); width: 15px; height: 15px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px var(--primary-glow);"></div>',
                iconSize: [15, 15],
                iconAnchor: [7, 7]
            })
        }).addTo(map);

        if (!fromClick) {
            map.setView([lat, lng], 16);
            alert("¡Coordenadas extraídas satisfactoriamente!");
        }
    }

    const scriptTess = document.createElement('script');
    scriptTess.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
    document.head.appendChild(scriptTess);

    function processGeoFile() {
        const fileInput = document.getElementById('geoFile');
        if(fileInput.files.length === 0) {
            alert("Por favor selecciona un archivo (.kml, .gpx, .jpg) primero.");
            return;
        }
        
        const file = fileInput.files[0];
        const ext = file.name.split('.').pop().toLowerCase();

        if (ext === 'jpg' || ext === 'jpeg' || ext === 'png') {
            document.getElementById('lat-input').value = 'Leyendo...';
            document.getElementById('lng-input').value = 'Leyendo...';
            // Extraer EXIF con latitud y longitud
            EXIF.getData(file, async function() {
                let lat = EXIF.getTag(this, "GPSLatitude");
                let lng = EXIF.getTag(this, "GPSLongitude");
                let latRef = EXIF.getTag(this, "GPSLatitudeRef") || "N";
                let lngRef = EXIF.getTag(this, "GPSLongitudeRef") || "W";

                if (!lat || !lng) {
                    console.log("No EXIF geodata found. Attempting visual (OCR) extraction...");
                    
                    if(typeof Tesseract === 'undefined') {
                        alert("El motor OCR (Tesseract) aún está cargando. Intenta de nuevo en unos segundos.");
                        document.getElementById('lat-input').value = '';
                        document.getElementById('lng-input').value = '';
                        return;
                    }

                    try {
                        const { data: { text } } = await Tesseract.recognize(file, 'spa');
                        console.log("OCR Extracted text: ", text);
                        
                        // Regex para capturar Latitud y Longitud
                        // Manejo de "Latitud: 7.265198 Longitud: -77.056625"
                        const geoRegex = /Lati?tu?d?[^\d-]*(-?\d+\.\d+)[\s,]*Longi?tu?d?[^\d-]*(-?\d+\.\d+)/i;
                        const match = text.match(geoRegex);
                        
                        if (match && match.length >= 3) {
                            let latDec = parseFloat(match[1]);
                            let lngDec = parseFloat(match[2]);
                            setCoordinatesFromExtractedData(latDec, lngDec);
                            alert("Coordenadas leídas de la imagen exitosamente mediante Inteligencia Artificial (OCR).");
                        } else {
                            document.getElementById('lat-input').value = '';
                            document.getElementById('lng-input').value = '';
                            alert("La imagen no contiene datos de geolocalización internos (EXIF) ni coordenadas legibles en pantalla.");
                        }
                    } catch (err) {
                        alert("Hubo un error al leer la imagen.");
                        console.error(err);
                    }
                    return;
                }

                // Convertir a Grados Decimales si era EXIF
                let latDec = lat[0].valueOf() + lat[1].valueOf() / 60 + lat[2].valueOf() / 3600;
                let lngDec = lng[0].valueOf() + lng[1].valueOf() / 60 + lng[2].valueOf() / 3600;
                
                if (latRef == "S") latDec = latDec * -1;
                if (lngRef == "W") lngDec = lngDec * -1;

                setCoordinatesFromExtractedData(latDec, lngDec);
            });
        } else if (ext === 'kml' || ext === 'gpx') {
            const reader = new FileReader();
            reader.onload = function(e) {
                const xml = e.target.result;
                let runLayer = null;
                
                try {
                    if (ext === 'kml') {
                        runLayer = omnivore.kml.parse(xml);
                    } else {
                        runLayer = omnivore.gpx.parse(xml);
                    }
                    
                    runLayer.on('ready', function() {
                        const bounds = this.getBounds();
                        if (bounds.isValid()) {
                            const center = bounds.getCenter();
                            setCoordinatesFromExtractedData(center.lat, center.lng);
                            this.addTo(map);
                            setTimeout(() => { map.removeLayer(this); }, 10000); // Quitar el trazo a los 10seg
                        } else {
                            alert("No se encontró ubicación válida en el archivo.");
                        }
                    });
                } catch (err) {
                    alert('Hubo un error interpretando el archivo. Asegúrate de que tenga un formato correcto.');
                    console.error(err);
                }
            };
            reader.readAsText(file);
        } else {
            alert("Formato no soportado. Usa KML, GPX o JPG.");
        }
    }

    let originalWorks = [];

    async function loadWorks() {
        try {
            const response = await fetch('{{ url("api/planning/works") }}');
            originalWorks = await response.json();
            // Filtrar eliminados (en caso de usar softdelete virtual por status)
            const works = originalWorks.filter(w => w.status !== 'deleted');
            renderWorksList(works);
            addMarkersToMap(works);
        } catch (error) {
            console.error('Error loading works:', error);
        }
    }

    function getWorkById(id) {
        return originalWorks.find(w => w.id == id);
    }

    function renderWorksList(works) {
        const listContainer = document.getElementById('works-list');
        const countBadge = document.getElementById('work-count');
        
        listContainer.innerHTML = '';
        countBadge.innerText = `${works.length} Proyecto${works.length !== 1 ? 's' : ''}`;

        works.forEach(work => {
            const div = document.createElement('div');
            div.className = 'work-item';
            div.innerHTML = `
                <div style="font-weight: 700; flex: 1;">${work.name}</div>
                <div style="display: flex; gap: 5px; align-items: center; margin-top:5px;">
                    <div class="status-pill status-${work.status}">${work.status.replace('_', ' ')}</div>
                    <button class="btn-access" style="padding: 2px 8px; border:none; cursor:pointer; font-size: 0.7rem;" onclick="openExpediente(${work.id}); event.stopPropagation();">Detalles</button>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 8px;">
                    Presupuesto: $${new Intl.NumberFormat().format(work.budget || 0)}
                </div>
            `;
            div.onclick = () => {
                if(map) {
                    map.setView([work.latitude, work.longitude], 16);
                    const mappedLayer = workLayers[work.id];
                    if (mappedLayer) {
                        if (mappedLayer.getBounds) {
                            map.fitBounds(mappedLayer.getBounds(), { maxZoom: 18 });
                        }
                        if (mappedLayer.getLayers) {
                            let inners = mappedLayer.getLayers();
                            if(inners.length > 0) inners[0].openPopup();
                        } else if (typeof mappedLayer.openPopup === 'function') {
                            mappedLayer.openPopup();
                        }
                    }
                }
            };
            listContainer.appendChild(div);
        });
    }

    function addMarkersToMap(works) {
        markersLayer.clearLayers();
        workLayers = {};
        works.forEach(work => {
            let layer;
            if (work.geometry_data && work.geometry_data !== 'null' && work.geometry_data !== '') {
                try {
                    let geojsonObj = JSON.parse(work.geometry_data);
                    let feature = { "type": "Feature", "geometry": geojsonObj, "properties": {} };
                    layer = L.geoJSON(feature, {
                        style: { color: 'var(--primary)', weight: 4, fillColor: 'var(--primary)', fillOpacity: 0.4 },
                        pointToLayer: function(geoJsonPoint, latlng) {
                            let m = L.marker(latlng);
                            m.work_id = work.id;
                            return m;
                        },
                        onEachFeature: function(f, l) {
                            l.work_id = work.id;
                        }
                    });
                } catch(e) {
                    layer = L.marker([work.latitude, work.longitude]);
                    layer.work_id = work.id;
                }
            } else {
                layer = L.marker([work.latitude, work.longitude]);
                layer.work_id = work.id;
            }
            
            layer.work_id = work.id;
            layer.addTo(markersLayer);
            
            const defaultImgUrl = "https://images.unsplash.com/photo-1541888946425-d81bb19480c5?auto=format&fit=crop&q=80&w=400";
            const imageUrl = work.image_url ? work.image_url : defaultImgUrl;

            const popupContent = `
                <div class="popup-content">
                    <img src="${imageUrl}" class="popup-img" onerror="this.src='${defaultImgUrl}'">
                    <div class="popup-title">${work.name}</div>
                    <div class="status-pill status-${work.status}" style="margin-bottom: 10px;">${work.status.replace('_', ' ')}</div>
                    <p style="font-size: 0.8rem; margin: 10px 0;">${work.description || 'Sin descripción adicional.'}</p>
                    <div style="display:flex; gap:10px;">
                        <button class="btn-access" style="flex:1; border:none; cursor:pointer; padding:8px 5px;" onclick="viewExpediente(${work.id})">Ver Info</button>
                        <button class="btn-access" style="flex:1; border:none; cursor:pointer; padding:8px 5px; background: rgba(99, 102, 241, 0.2); color: var(--primary);" onclick="openExpediente(${work.id})">Editar</button>
                    </div>
                </div>
            `;
            
            layer.bindPopup(popupContent);
            workLayers[work.id] = layer;
        });
    }

    // ========== EXPEDIENTE LOGIC ==========
    function viewExpediente(id) {
        openExpediente(id);
        const form = document.getElementById('editWorkForm');
        Array.from(form.elements).forEach(el => el.disabled = true);
        document.getElementById('btn-save-exp').style.display = 'none';
        document.getElementById('btn-delete-exp').style.display = 'none';
        document.getElementById('exp-title').innerText = "Información de la Obra";
        document.getElementById('exp-image-upload-btn').style.display = 'none';
    }

    function openExpediente(id) {
        const work = getWorkById(id);
        if(!work) return;

        const form = document.getElementById('editWorkForm');
        Array.from(form.elements).forEach(el => el.disabled = false);
        document.getElementById('btn-save-exp').style.display = 'flex'; // It is a flex/block
        document.getElementById('btn-delete-exp').style.display = 'flex';
        document.getElementById('exp-title').innerText = "Editar Expediente de Obra";
        document.getElementById('exp-image-upload-btn').style.display = 'flex';

        document.getElementById('exp-id').value = work.id;
        document.getElementById('exp-name').value = work.name;
        document.getElementById('exp-status').value = work.status;
        document.getElementById('exp-budget').value = work.budget || '';
        document.getElementById('exp-description').value = work.description || '';
        document.getElementById('exp-lat').value = work.latitude;
        document.getElementById('exp-lng').value = work.longitude;

        if (work.image_url) {
            document.getElementById('exp-image-preview').src = work.image_url;
            document.getElementById('exp-image-preview').style.display = 'block';
            document.getElementById('exp-no-image-text').style.display = 'none';
        } else {
            document.getElementById('exp-image-preview').style.display = 'none';
            document.getElementById('exp-no-image-text').style.display = 'block';
        }

        document.getElementById('expedienteModal').style.display = 'flex';
        // Re-init lucide icons in case they need to render within the modal specifically? (Not necessary if already in DOM)
    }

    function closeExpediente() {
        document.getElementById('expedienteModal').style.display = 'none';
    }

    document.getElementById('editWorkForm').onsubmit = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-exp');
        const id = document.getElementById('exp-id').value;
        const _text = btn.innerText;

        btn.innerText = "Guardando..."; btn.disabled = true;

        const formData = new FormData(this);
        formData.append('_method', 'PUT'); // Fake PUT for Laravel form routing via JSON
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('{{ url("api/planning/works") }}/' + id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('¡Expediente actualizado exitosamente!');
                closeExpediente();
                loadWorks();
                localStorage.setItem('sync_planning_works', Date.now().toString());
            } else {
                throw new Error('Error al actualizar');
            }
        } catch (error) {
            console.error(error);
            alert('Error al guardar el expediente.');
        } finally {
            btn.innerText = _text; btn.disabled = false;
        }
    };

    async function deleteWork() {
        if (!confirm('¿Estás seguro que deseas eliminar esta obra de manera permanente?')) return;

        const btn = document.getElementById('btn-delete-exp');
        const id = document.getElementById('exp-id').value;
        btn.disabled = true;

        try {
            const response = await fetch('{{ url("api/planning/works") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (response.ok) {
                alert('¡Obra eliminada!');
                closeExpediente();
                loadWorks();
                localStorage.setItem('sync_planning_works', Date.now().toString());
            } else {
                throw new Error('No se pudo eliminar');
            }
        } catch(e) {
            console.error(e);
            alert('Fallo de conexión al eliminar');
        } finally {
            btn.disabled = false;
        }
    }

    async function uploadWorkImage(input) {
        if (!input.files || input.files.length === 0) return;
        
        const id = document.getElementById('exp-id').value;
        const file = input.files[0];
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            document.getElementById('exp-no-image-text').innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="margin:auto; display:block"></i> Subiendo...';
            document.getElementById('exp-image-preview').style.display = 'none';
            document.getElementById('exp-no-image-text').style.display = 'block';
            
            const response = await fetch('{{ url("api/planning/works") }}/' + id + '/image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            if (response.ok) {
                const updatedWork = await response.json();
                
                // Update local datastore
                const index = originalWorks.findIndex(w => w.id == id);
                if(index > -1) originalWorks[index].image_url = updatedWork.image_url;

                document.getElementById('exp-image-preview').src = updatedWork.image_url;
                document.getElementById('exp-image-preview').style.display = 'block';
                document.getElementById('exp-no-image-text').style.display = 'none';

                alert('Se ha guardado la nueva fotografía de la obra.');
                loadWorks();
                localStorage.setItem('sync_planning_works', Date.now().toString());
            } else {
                throw new Error('Upload failed');
            }
        } catch (error) {
            console.error(error);
            alert('Error al subir la imagen. Asegúrate que pese menos de 5MB y sea de formato válido (jpg/png).');
            document.getElementById('exp-no-image-text').innerHTML = 'Error al subir';
        }
        input.value = ""; // reset
    }

    document.getElementById('newWorkForm').onsubmit = async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const originalText = document.getElementById('submit-text').innerText;
        
        document.getElementById('submit-text').innerText = 'Guardando...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('{{ url("api/planning/works") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('¡Obra registrada exitosamente en el sistema geo-referenciado!');
                closeModal();
                loadWorks();
                this.reset();
                if(currentMarker) map.removeLayer(currentMarker);

                // Notificar a otras pestañas locales en tiempo real
                localStorage.setItem('sync_planning_works', Date.now().toString());
            } else {
                throw new Error('Error al guardar');
            }
        } catch (error) {
            console.error('Error saving work:', error);
            alert('Error al guardar la obra. Inténtelo de nuevo.');
        } finally {
            document.getElementById('submit-text').innerText = originalText;
            btn.disabled = false;
        }
    };

    // Sincronización entre Pestañas (Mismo Navegador - LocalStorage Events)
    window.addEventListener('storage', (e) => {
        if (e.key === 'sync_planning_works') {
            console.log('¡Actualización sincronizada en tiempo real desde otra pestaña!');
            loadWorks(); // Recargar obras silenciosamente sin refrescar la página
        }
    });

    // Iniciar con Turbolinks
    document.addEventListener("turbolinks:load", initMap);
</script>
@endsection
