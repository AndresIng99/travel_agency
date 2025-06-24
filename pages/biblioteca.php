<?php 
// =====================================
// ARCHIVO: pages/biblioteca.php - Biblioteca con Colores Dinámicos
// =====================================

App::requireLogin();

// Incluir ConfigManager
require_once 'config/config_functions.php';

$user = App::getUser(); 

// Obtener configuración de colores según el rol del usuario
ConfigManager::init();
$userColors = ConfigManager::getColorsForRole($user['role']);
$companyName = ConfigManager::getCompanyName();
$logo = ConfigManager::getLogo();
$defaultLanguage = ConfigManager::getDefaultLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $defaultLanguage ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - <?= htmlspecialchars($companyName) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-color: <?= $userColors['primary'] ?>;
            --secondary-color: <?= $userColors['secondary'] ?>;
            --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Header con colores dinámicos */
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Google Translate */
        #google_translate_element {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
        }

        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0px !important; }

        /* Main Content */
        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Tabs Container */
        .tabs-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .tabs-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #4a5568;
        }

        .tab-btn.active {
            background: var(--primary-gradient);
            color: white;
        }

        .tab-btn:hover:not(.active) {
            background: #f7fafc;
        }

        /* Search and Filters */
        .filters-section {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .filter-select {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .add-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .item-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
        }

        .item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .item-card {
            position: relative;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-color: var(--primary-color);
        }

        .card-image {
            width: 100%;
            height: 200px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .card-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            font-size: 13px;
            font-weight: 500;
        }

        .card-actions {
            padding: 15px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .action-btn.edit {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .action-btn.edit:hover {
            background: var(--primary-color);
            color: white;
        }

        .action-btn.delete {
            color: #e53e3e;
            border-color: #e53e3e;
        }

        .action-btn.delete:hover {
            background: #e53e3e;
            color: white;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
        }

        .modal-title {
            font-size: 24px;
            color: #2d3748;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #718096;
            padding: 5px;
        }

        .close-btn:hover {
            color: var(--primary-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 500;
            color: #4a5568;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .image-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .image-upload:hover {
            border-color: var(--primary-color);
        }

        .image-upload input {
            display: none;
        }

        /* Map Container */
        .map-container {
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Location suggestions */
        .location-suggestions {
            animation: slideDown 0.2s ease-out;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.2s ease;
            font-size: 14px;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background-color: #f7fafc !important;
        }

        /* Loading indicator */
        .location-loading {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .tabs-nav {
                flex-wrap: wrap;
            }

            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                min-width: auto;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10px;
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .images-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="<?= APP_URL ?>/dashboard" class="back-btn">← Volver</a>
            <h2>📚 Biblioteca de Recursos</h2>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div id="google_translate_element"></div>
            <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="tabs-container">
            <!-- Tabs Navigation -->
            <div class="tabs-nav">
                <button class="tab-btn active" data-tab="dias">📅 Días</button>
                <button class="tab-btn" data-tab="alojamientos">🏨 Alojamientos</button>
                <button class="tab-btn" data-tab="actividades">🎯 Actividades</button>
                <button class="tab-btn" data-tab="transportes">🚗 Transportes</button>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <input type="text" class="search-input" placeholder="Buscar recursos..." id="searchInput">
                <select class="filter-select" id="languageFilter">
                    <option value="">Todos los idiomas</option>
                    <option value="es">Español</option>
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="pt">Português</option>
                </select>
                <button class="add-btn" onclick="openModal('create')">➕ Agregar Nuevo</button>
            </div>

            <!-- Content Grid -->
            <div class="content-grid" id="contentGrid">
                <!-- El contenido se carga dinámicamente aquí -->
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-state-icon">📂</div>
                <h3>No hay recursos disponibles</h3>
                <p>Comienza agregando tu primer recurso haciendo clic en "Agregar Nuevo"</p>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar -->
    <div class="modal" id="resourceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Agregar Nuevo Día</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form id="resourceForm">
                <input type="hidden" id="resourceId">
                <input type="hidden" id="resourceType">

                <!-- Formulario común -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select id="idioma" name="idioma" required>
                            <option value="es">Español</option>
                            <option value="en">English</option>
                            <option value="fr">Français</option>
                            <option value="pt">Português</option>
                        </select>
                    </div>
                </div>

                <!-- Campos específicos se cargan dinámicamente -->
                <div id="specificFields"></div>

                <!-- Mapa para ubicación -->
                <div class="form-group" id="mapSection">
                    <label>Seleccionar Ubicación en el Mapa</label>
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Configuración global - SIN API KEYS
        const APP_URL = '<?= APP_URL ?>';
        const DEFAULT_LANGUAGE = '<?= $defaultLanguage ?>';
        
        let currentTab = 'dias';
        let map = null;
        let currentMarker = null;
        let resources = {
            dias: [],
            alojamientos: [],
            actividades: [],
            transportes: []
        };

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            loadResources();
            setupSearch();
            initializeGoogleTranslate();
        });

        // ============================================= 
        // NUEVA FUNCIÓN DE MAPA CON OPENSTREETMAP
        // ============================================= 

        // Inicializar mapa con OpenStreetMap (GRATIS)
        function initializeMap() {
            const mapContainer = document.getElementById('map');
            
            try {
                // Limpiar contenedor
                mapContainer.innerHTML = '';
                
                // Crear mapa con OpenStreetMap
                map = L.map('map').setView([4.7110, -74.0721], 10); // Bogotá por defecto

                // Agregar capa gratuita de OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18,
                    minZoom: 2
                }).addTo(map);

                // Control de zoom
                L.control.zoom({
                    position: 'topright'
                }).addTo(map);

                // Click en el mapa para seleccionar ubicación
                map.on('click', function(e) {
                    const coords = e.latlng;
                    
                    // Remover marcador anterior
                    if (currentMarker) {
                        map.removeLayer(currentMarker);
                    }
                    
                    // Agregar nuevo marcador (azul como el tema)
                    currentMarker = L.marker([coords.lat, coords.lng], {
                        draggable: true
                    }).addTo(map);

                    // Popup informativo
                    currentMarker.bindPopup(`
                        <div style="text-align: center;">
                            <strong>📍 Ubicación Seleccionada</strong><br>
                            <small>Lat: ${coords.lat.toFixed(6)}<br>
                            Lng: ${coords.lng.toFixed(6)}</small>
                        </div>
                    `).openPopup();
                    
                    // Geocodificación gratuita
                    reverseGeocodeOSM(coords.lat, coords.lng);
                    
                    // Event listener para arrastrar marcador
                    currentMarker.on('dragend', function(e) {
                        const newCoords = e.target.getLatLng();
                        reverseGeocodeOSM(newCoords.lat, newCoords.lng);
                        
                        // Actualizar popup
                        currentMarker.setPopupContent(`
                            <div style="text-align: center;">
                                <strong>📍 Ubicación Actualizada</strong><br>
                                <small>Lat: ${newCoords.lat.toFixed(6)}<br>
                                Lng: ${newCoords.lng.toFixed(6)}</small>
                            </div>
                        `);
                    });
                });

                // Evento cuando el mapa se carga
                map.whenReady(function() {
                    console.log('✅ Mapa OpenStreetMap cargado - 100% GRATIS');
                    
                    // Mensaje de bienvenida
                    setTimeout(() => {
                        if (!currentMarker) {
                            L.popup()
                                .setLatLng([4.7110, -74.0721])
                                .setContent(`
                                    <div style="text-align: center;">
                                        <strong>🗺️ Mapa Interactivo</strong><br>
                                        <small>Haz clic en cualquier lugar para seleccionar ubicación</small>
                                    </div>
                                `)
                                .openOn(map);
                        }
                    }, 1000);
                });

                // Redimensionar mapa cuando se abre el modal
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);

            } catch (error) {
                console.error('Error cargando mapa:', error);
                initializeMapFallback();
            }
        }

        // Función de respaldo si falla el mapa
        function initializeMapFallback() {
            const mapContainer = document.getElementById('map');
            mapContainer.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; border-radius: 10px; padding: 20px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📍</div>
                    <h3 style="margin-bottom: 15px;">Seleccionar Ubicación</h3>
                    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; width: 100%; max-width: 300px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                            <input type="number" id="manual-lat" placeholder="Latitud" step="any" style="padding: 10px; border: none; border-radius: 5px; text-align: center;">
                            <input type="number" id="manual-lng" placeholder="Longitud" step="any" style="padding: 10px; border: none; border-radius: 5px; text-align: center;">
                        </div>
                        <button onclick="useCurrentLocation()" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; padding: 10px 20px; border-radius: 25px; cursor: pointer; margin-right: 10px;">📱 Mi Ubicación</button>
                        <button onclick="searchLocationPrompt()" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; padding: 10px 20px; border-radius: 25px; cursor: pointer;">🔍 Buscar</button>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                const latInput = document.getElementById('manual-lat');
                const lngInput = document.getElementById('manual-lng');
                
                if (latInput && lngInput) {
                    latInput.addEventListener('change', updateLocationFromCoords);
                    lngInput.addEventListener('change', updateLocationFromCoords);
                }
            }, 100);
        }

        // ============================================= 
        // GEOCODIFICACIÓN GRATUITA CON NOMINATIM
        // ============================================= 

        function reverseGeocodeOSM(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        const ubicacionField = document.getElementById('ubicacion');
                        if (ubicacionField) {
                            ubicacionField.value = data.display_name;
                        }
                        
                        // Guardar coordenadas en campos ocultos
                        updateCoordinateFields(lat, lng);
                        
                        console.log('📍 Ubicación encontrada:', data.display_name);
                    } else {
                        // Si no hay resultado, usar coordenadas
                        const ubicacionField = document.getElementById('ubicacion');
                        if (ubicacionField) {
                            ubicacionField.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        }
                        updateCoordinateFields(lat, lng);
                    }
                })
                .catch(error => {
                    console.warn('Geocodificación no disponible:', error);
                    const ubicacionField = document.getElementById('ubicacion');
                    if (ubicacionField) {
                        ubicacionField.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    }
                    updateCoordinateFields(lat, lng);
                });
        }

        // ============================================= 
        // FUNCIONES AUXILIARES
        // ============================================= 

        function updateCoordinateFields(lat, lng) {
            // Buscar campos de latitud y longitud en el formulario
            const latField = document.getElementById('latitud') || document.querySelector('input[name="latitud"]');
            const lngField = document.getElementById('longitud') || document.querySelector('input[name="longitud"]');
            
            if (latField) latField.value = lat;
            if (lngField) lngField.value = lng;

            // Para transportes, también actualizar campos específicos si es el campo activo
            const currentInput = document.activeElement;
            if (currentInput && currentInput.name === 'lugar_salida') {
                const latSalidaField = document.getElementById('lat_salida');
                const lngSalidaField = document.getElementById('lng_salida');
                if (latSalidaField) latSalidaField.value = lat;
                if (lngSalidaField) lngSalidaField.value = lng;
            } else if (currentInput && currentInput.name === 'lugar_llegada') {
                const latLlegadaField = document.getElementById('lat_llegada');
                const lngLlegadaField = document.getElementById('lng_llegada');
                if (latLlegadaField) latLlegadaField.value = lat;
                if (lngLlegadaField) lngLlegadaField.value = lng;
            }
        }

        function updateLocationFromCoords() {
            const latInput = document.getElementById('manual-lat');
            const lngInput = document.getElementById('manual-lng');
            
            if (latInput && lngInput) {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    reverseGeocodeOSM(lat, lng);
                }
            }
        }

        function searchLocationOSM(query) {
            if (!query || query.length < 3) return;
            
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&accept-language=es`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        if (map) {
                            // Centrar mapa en el resultado
                            map.setView([lat, lng], 15);
                            
                            // Agregar/mover marcador
                            if (currentMarker) {
                                map.removeLayer(currentMarker);
                            }
                            
                            currentMarker = L.marker([lat, lng], {
                                draggable: true
                            }).addTo(map);
                            
                            currentMarker.bindPopup(`
                                <div style="text-align: center;">
                                    <strong>🔍 ${result.display_name}</strong><br>
                                    <small>Lat: ${lat.toFixed(6)}<br>
                                    Lng: ${lng.toFixed(6)}</small>
                                </div>
                            `).openPopup();
                        }
                        
                        // Actualizar campos
                        const ubicacionField = document.getElementById('ubicacion');
                        if (ubicacionField) {
                            ubicacionField.value = result.display_name;
                        }
                        
                        updateCoordinateFields(lat, lng);
                        console.log('🔍 Búsqueda exitosa:', result.display_name);
                    } else {
                        alert('No se encontraron resultados para: ' + query);
                    }
                })
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    alert('Error en la búsqueda. Verifica tu conexión a internet.');
                });
        }

        function useCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    if (map) {
                        map.setView([lat, lng], 15);
                        
                        if (currentMarker) {
                            map.removeLayer(currentMarker);
                        }
                        
                        currentMarker = L.marker([lat, lng], {
                            draggable: true
                        }).addTo(map);
                        
                        currentMarker.bindPopup(`
                            <div style="text-align: center;">
                                <strong>📱 Tu Ubicación Actual</strong><br>
                                <small>Lat: ${lat.toFixed(6)}<br>
                                Lng: ${lng.toFixed(6)}</small>
                            </div>
                        `).openPopup();
                    } else {
                        // Para modo fallback
                        const latInput = document.getElementById('manual-lat');
                        const lngInput = document.getElementById('manual-lng');
                        if (latInput && lngInput) {
                            latInput.value = lat.toFixed(6);
                            lngInput.value = lng.toFixed(6);
                        }
                    }
                    
                    reverseGeocodeOSM(lat, lng);
                }, function(error) {
                    alert('No se pudo obtener la ubicación: ' + error.message);
                });
            } else {
                alert('La geolocalización no es compatible con este navegador');
            }
        }

        function searchLocationPrompt() {
            const query = prompt('Ingresa el nombre del lugar que quieres buscar:\n(Ejemplo: "Torre Eiffel, París" o "Medellín, Colombia")');
            if (query && query.trim()) {
                searchLocationOSM(query.trim());
            }
        }

        // Configuración de tabs
        function initializeTabs() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Actualizar tabs activos
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Cambiar contenido
                    currentTab = this.dataset.tab;
                    loadResources();
                });
            });
        }

        // Cargar recursos según el tab activo
        function loadResources() {
            // Simular datos de ejemplo hasta que tengamos la API
            const sampleData = {
                dias: [
                    {
                        id: 1,
                        titulo: 'Día en París',
                        descripcion: 'Recorrido completo por los principales monumentos de París',
                        ubicacion: 'París, Francia',
                        idioma: 'es'
                    },
                    {
                        id: 2,
                        titulo: 'Day in Rome',
                        descripcion: 'Visit to Colosseum, Roman Forum and Vatican',
                        ubicacion: 'Rome, Italy',
                        idioma: 'en'
                    }
                ],
                alojamientos: [
                    {
                        id: 1,
                        nombre: 'Hotel París Centro',
                        descripcion: 'Hotel 4 estrellas en el centro de París',
                        ubicacion: 'París, Francia',
                        tipo: 'hotel',
                        categoria: 4,
                        idioma: 'es'
                    }
                ],
                actividades: [
                    {
                        id: 1,
                        nombre: 'Tour Eiffel',
                        descripcion: 'Visita guiada a la Torre Eiffel con subida incluida',
                        ubicacion: 'París, Francia',
                        idioma: 'es'
                    }
                ],
                transportes: [
                    {
                        id: 1,
                        titulo: 'Vuelo París-Roma',
                        descripcion: 'Vuelo directo París CDG a Roma FCO',
                        lugar_salida: 'París CDG',
                        lugar_llegada: 'Roma FCO',
                        medio: 'avion',
                        duracion: '2h 15min',
                        idioma: 'es'
                    }
                ]
            };

            resources[currentTab] = sampleData[currentTab] || [];
            renderResources();
        }

        // Renderizar recursos en el grid
        function renderResources() {
            const grid = document.getElementById('contentGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (resources[currentTab].length === 0) {
                grid.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            grid.style.display = 'grid';
            emptyState.style.display = 'none';
            
            grid.innerHTML = resources[currentTab].map(item => {
                return createResourceCard(item);
            }).join('');
        }

        // Crear card de recurso
        function createResourceCard(item) {
            const icons = {
                dias: '📅',
                alojamientos: '🏨',
                actividades: '🎯',
                transportes: '🚗'
            };

            const title = item.titulo || item.nombre || item.titulo;
            const location = item.ubicacion || `${item.lugar_salida} → ${item.lugar_llegada}` || '';
            
            return `
                <div class="item-card" onclick="viewResource(${item.id})">
                    <div class="card-image">
                        ${item.imagen1 ? `<img src="${item.imagen1}" alt="${title}">` : icons[currentTab]}
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">${title}</h3>
                        <p class="card-description">${item.descripcion}</p>
                        <div class="card-location">📍 ${location}</div>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn edit" onclick="event.stopPropagation(); editResource(${item.id})">
                            ✏️ Editar
                        </button>
                        <button class="action-btn delete" onclick="event.stopPropagation(); deleteResource(${item.id})">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            `;
        }

        // Funciones del modal
        function openModal(mode, id = null) {
            const modal = document.getElementById('resourceModal');
            const title = document.getElementById('modalTitle');
            
            // Configurar título
            const titles = {
                dias: mode === 'create' ? 'Agregar Nuevo Día' : 'Editar Día',
                alojamientos: mode === 'create' ? 'Agregar Nuevo Alojamiento' : 'Editar Alojamiento',
                actividades: mode === 'create' ? 'Agregar Nueva Actividad' : 'Editar Actividad',
                transportes: mode === 'create' ? 'Agregar Nuevo Transporte' : 'Editar Transporte'
            };
            
            title.textContent = titles[currentTab];
            document.getElementById('resourceType').value = currentTab;
            document.getElementById('resourceId').value = id || '';
            
            // Cargar campos específicos
            loadSpecificFields();
            
            // Mostrar modal
            modal.classList.add('show');
            
            // Inicializar mapa después de mostrar modal
            setTimeout(() => {
                initializeMap();
            }, 200);

            setTimeout(() => {
                setupLocationAutocomplete();
            }, 300);
            
            // Si es edición, cargar datos
            if (mode === 'edit' && id) {
                loadResourceData(id);
            }
        }

        function closeModal() {
            const modal = document.getElementById('resourceModal');
            modal.classList.remove('show');
            
            // Limpiar formulario
            document.getElementById('resourceForm').reset();
            
            // Destruir mapa
            if (map) {
                map.remove();
                map = null;
                currentMarker = null;
            }
        }

        // Cargar campos específicos según el tipo
        function loadSpecificFields() {
            const container = document.getElementById('specificFields');
            let fieldsHTML = '';
            
            switch(currentTab) {
                case 'dias':
                    fieldsHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="titulo">Título de la Jornada</label>
                                <input type="text" id="titulo" name="titulo" required placeholder="Ej: Día en París">
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" required placeholder="Ciudad, País">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" required placeholder="Describe las actividades del día..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imágenes (máximo 3)</label>
                            <div class="images-grid">
                                <div class="image-upload" onclick="document.getElementById('imagen1').click()">
                                    <input type="file" id="imagen1" name="imagen1" accept="image/*">
                                    <div>📷 Imagen 1</div>
                                </div>
                                <div class="image-upload" onclick="document.getElementById('imagen2').click()">
                                    <input type="file" id="imagen2" name="imagen2" accept="image/*">
                                    <div>📷 Imagen 2</div>
                                </div>
                                <div class="image-upload" onclick="document.getElementById('imagen3').click()">
                                    <input type="file" id="imagen3" name="imagen3" accept="image/*">
                                    <div>📷 Imagen 3</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="latitud" name="latitud">
                        <input type="hidden" id="longitud" name="longitud">
                    `;
                    break;
                    
                case 'alojamientos':
                    fieldsHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre">Nombre del Alojamiento</label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Hotel París Centro">
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" required placeholder="Dirección completa">
                            </div>
                            <div class="form-group">
                                <label for="tipo">Tipo de Alojamiento</label>
                                <select id="tipo" name="tipo" required onchange="updateCategoryField()">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="camping">Camping</option>
                                    <option value="casa_huespedes">Casa de Huéspedes</option>
                                    <option value="crucero">Crucero</option>
                                    <option value="lodge">Lodge</option>
                                    <option value="atipico">Atípico</option>
                                    <option value="campamento">Campamento</option>
                                    <option value="camping_car">Camping Car</option>
                                    <option value="tren">Tren</option>
                                </select>
                            </div>
                            <div class="form-group" id="categoryGroup" style="display: none;">
                                <label for="categoria">Categoría (Estrellas)</label>
                                <select id="categoria" name="categoria">
                                    <option value="">Sin categoría</option>
                                    <option value="1">⭐ 1 Estrella</option>
                                    <option value="2">⭐⭐ 2 Estrellas</option>
                                    <option value="3">⭐⭐⭐ 3 Estrellas</option>
                                    <option value="4">⭐⭐⭐⭐ 4 Estrellas</option>
                                    <option value="5">⭐⭐⭐⭐⭐ 5 Estrellas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sitio_web">Sitio Web (Opcional)</label>
                                <input type="url" id="sitio_web" name="sitio_web" placeholder="https://...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" required placeholder="Describe el alojamiento..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imagen Representativa</label>
                            <div class="image-upload" onclick="document.getElementById('imagen').click()">
                                <input type="file" id="imagen" name="imagen" accept="image/*">
                                <div>📷 Subir Imagen</div>
                            </div>
                        </div>
                        <input type="hidden" id="latitud" name="latitud">
                        <input type="hidden" id="longitud" name="longitud">
                    `;
                    break;
                    
                case 'actividades':
                    fieldsHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre">Nombre de la Actividad</label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Tour Eiffel">
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" required placeholder="Lugar donde se realiza">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" required placeholder="Describe la actividad..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imágenes (máximo 3)</label>
                            <div class="images-grid">
                                <div class="image-upload" onclick="document.getElementById('imagen1').click()">
                                    <input type="file" id="imagen1" name="imagen1" accept="image/*">
                                    <div>📷 Imagen 1</div>
                                </div>
                                <div class="image-upload" onclick="document.getElementById('imagen2').click()">
                                    <input type="file" id="imagen2" name="imagen2" accept="image/*">
                                    <div>📷 Imagen 2</div>
                                </div>
                                <div class="image-upload" onclick="document.getElementById('imagen3').click()">
                                    <input type="file" id="imagen3" name="imagen3" accept="image/*">
                                    <div>📷 Imagen 3</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="latitud" name="latitud">
                        <input type="hidden" id="longitud" name="longitud">
                    `;
                    break;
                    
                case 'transportes':
                    fieldsHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="medio">Medio de Transporte</label>
                                <select id="medio" name="medio" required>
                                    <option value="">Seleccionar medio</option>
                                    <option value="bus">🚌 Bus</option>
                                    <option value="avion">✈️ Avión</option>
                                    <option value="coche">🚗 Coche</option>
                                    <option value="barco">🚢 Barco</option>
                                    <option value="tren">🚂 Tren</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="titulo">Título del Transporte</label>
                                <input type="text" id="titulo" name="titulo" required placeholder="Ej: Vuelo París-Roma">
                            </div>
                            <div class="form-group">
                                <label for="lugar_salida">Lugar de Salida</label>
                                <input type="text" id="lugar_salida" name="lugar_salida" required placeholder="Ciudad/Aeropuerto de salida">
                            </div>
                            <div class="form-group">
                                <label for="lugar_llegada">Lugar de Llegada</label>
                                <input type="text" id="lugar_llegada" name="lugar_llegada" required placeholder="Ciudad/Aeropuerto de llegada">
                            </div>
                            <div class="form-group">
                                <label for="duracion">Duración</label>
                                <input type="text" id="duracion" name="duracion" placeholder="Ej: 2 horas 30 minutos">
                            </div>
                            <div class="form-group">
                                <label for="distancia_km">Distancia (km)</label>
                                <input type="number" id="distancia_km" name="distancia_km" step="0.01" placeholder="Distancia en kilómetros">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" placeholder="Detalles adicionales del transporte..."></textarea>
                        </div>
                        <input type="hidden" id="lat_salida" name="lat_salida">
                        <input type="hidden" id="lng_salida" name="lng_salida">
                        <input type="hidden" id="lat_llegada" name="lat_llegada">
                        <input type="hidden" id="lng_llegada" name="lng_llegada">
                    `;
                    break;
            }
            
            container.innerHTML = fieldsHTML;
            setTimeout(() => {
                setupTransportLocationFields();
            }, 100);
        }
        // Función para configurar autocompletado bidireccional
function setupLocationAutocomplete() {
    const ubicacionField = document.getElementById('ubicacion');
    if (!ubicacionField) return;

    let searchTimeout;
    let suggestionsList = null;

    // Event listener para cuando el usuario escribe
    ubicacionField.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Limpiar timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Remover sugerencias anteriores
        removeSuggestions();

        // Si la consulta es muy corta, no buscar
        if (query.length < 3) {
            return;
        }

        // Buscar después de 500ms de pausa en escritura
        searchTimeout = setTimeout(() => {
            searchAndShowSuggestions(query, ubicacionField);
        }, 500);
    });

    // Event listener para cuando pierde el foco
    ubicacionField.addEventListener('blur', function() {
        // Remover sugerencias después de un pequeño delay
        // para permitir clicks en las sugerencias
        setTimeout(() => {
            removeSuggestions();
        }, 200);
    });

    // Event listener para teclas especiales
    ubicacionField.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            removeSuggestions();
        }
    });
}
function setupTransportLocationFields() {
    // Configurar autocompletado para lugar de salida
    const salidaField = document.getElementById('lugar_salida');
    if (salidaField) {
        setupFieldAutocomplete(salidaField, 'salida');
    }

    // Configurar autocompletado para lugar de llegada
    const llegadaField = document.getElementById('lugar_llegada');
    if (llegadaField) {
        setupFieldAutocomplete(llegadaField, 'llegada');
    }
}

function setupFieldAutocomplete(field, type) {
    let searchTimeout;

    field.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        removeSuggestions();

        if (query.length < 3) {
            return;
        }

        searchTimeout = setTimeout(() => {
            searchAndShowFieldSuggestions(query, field, type);
        }, 500);
    });

    field.addEventListener('blur', function() {
        setTimeout(() => {
            removeSuggestions();
        }, 200);
    });
}

function searchAndShowFieldSuggestions(query, inputField, type) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&accept-language=es`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                showFieldSuggestions(data, inputField, type);
            }
        })
        .catch(error => {
            console.warn('Error en búsqueda:', error);
        });
}

function showFieldSuggestions(suggestions, inputField, type) {
    removeSuggestions();

    suggestionsList = document.createElement('div');
    suggestionsList.className = 'location-suggestions';
    suggestionsList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 10px 10px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    `;

    suggestions.forEach((suggestion) => {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'suggestion-item';
        suggestionItem.style.cssText = `
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.2s ease;
            font-size: 14px;
        `;

        suggestionItem.innerHTML = `
            <div style="font-weight: 500; color: #2d3748;">
                ${getLocationTitle(suggestion)}
            </div>
            <div style="font-size: 12px; color: #718096;">
                ${suggestion.display_name}
            </div>
        `;

        suggestionItem.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f7fafc';
        });

        suggestionItem.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });

        suggestionItem.addEventListener('click', function() {
            selectFieldLocation(suggestion, inputField, type);
        });

        suggestionsList.appendChild(suggestionItem);
    });

    const inputContainer = inputField.parentElement;
    inputContainer.style.position = 'relative';
    inputContainer.appendChild(suggestionsList);
}

function selectFieldLocation(suggestion, inputField, type) {
    const lat = parseFloat(suggestion.lat);
    const lng = parseFloat(suggestion.lon);

    // Actualizar campo
    inputField.value = suggestion.display_name;

    // Actualizar coordenadas específicas según el tipo
    if (type === 'salida') {
        const latField = document.getElementById('lat_salida');
        const lngField = document.getElementById('lng_salida');
        if (latField) latField.value = lat;
        if (lngField) lngField.value = lng;
    } else if (type === 'llegada') {
        const latField = document.getElementById('lat_llegada');
        const lngField = document.getElementById('lng_llegada');
        if (latField) latField.value = lat;
        if (lngField) lngField.value = lng;
    }

    removeSuggestions();
    console.log(`📍 ${type} seleccionada:`, suggestion.display_name);
}

// Buscar sugerencias y mostrarlas
function searchAndShowSuggestions(query, inputField) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&accept-language=es&addressdetails=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                showSuggestions(data, inputField);
            }
        })
        .catch(error => {
            console.warn('Error en búsqueda de sugerencias:', error);
        });
}

// Mostrar lista de sugerencias
function showSuggestions(suggestions, inputField) {
    // Remover sugerencias anteriores
    removeSuggestions();

    // Crear contenedor de sugerencias
    suggestionsList = document.createElement('div');
    suggestionsList.className = 'location-suggestions';
    suggestionsList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 10px 10px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    `;

    // Crear elementos de sugerencia
    suggestions.forEach((suggestion, index) => {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'suggestion-item';
        suggestionItem.style.cssText = `
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.2s ease;
            font-size: 14px;
            line-height: 1.4;
        `;

        // Contenido de la sugerencia
        suggestionItem.innerHTML = `
            <div style="font-weight: 500; color: #2d3748; margin-bottom: 2px;">
                ${getLocationTitle(suggestion)}
            </div>
            <div style="font-size: 12px; color: #718096;">
                ${suggestion.display_name}
            </div>
        `;

        // Event listeners para hover
        suggestionItem.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f7fafc';
        });

        suggestionItem.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });

        // Event listener para click
        suggestionItem.addEventListener('click', function() {
            selectLocation(suggestion, inputField);
        });

        suggestionsList.appendChild(suggestionItem);
    });

    // Posicionar relativo al input
    const inputContainer = inputField.parentElement;
    inputContainer.style.position = 'relative';
    inputContainer.appendChild(suggestionsList);
}

// Obtener título limpio para la ubicación
function getLocationTitle(suggestion) {
    // Extraer el nombre principal de la ubicación
    const parts = suggestion.display_name.split(',');
    if (parts.length > 0) {
        return parts[0].trim();
    }
    return suggestion.display_name;
}

// Seleccionar una ubicación de las sugerencias
function selectLocation(suggestion, inputField) {
    const lat = parseFloat(suggestion.lat);
    const lng = parseFloat(suggestion.lon);

    // Actualizar campo de ubicación
    inputField.value = suggestion.display_name;

    // Actualizar coordenadas
    updateCoordinateFields(lat, lng);

    // Actualizar mapa si existe
    if (map) {
        // Centrar mapa en la ubicación
        map.setView([lat, lng], 15);

        // Remover marcador anterior
        if (currentMarker) {
            map.removeLayer(currentMarker);
        }

        // Agregar nuevo marcador
        currentMarker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        // Popup informativo
        currentMarker.bindPopup(`
            <div style="text-align: center;">
                <strong>📍 ${getLocationTitle(suggestion)}</strong><br>
                <small>${suggestion.display_name}</small><br>
                <small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>
            </div>
        `).openPopup();

        // Event listener para arrastrar
        currentMarker.on('dragend', function(e) {
            const newCoords = e.target.getLatLng();
            reverseGeocodeOSM(newCoords.lat, newCoords.lng);
            
            currentMarker.setPopupContent(`
                <div style="text-align: center;">
                    <strong>📍 Ubicación Actualizada</strong><br>
                    <small>Lat: ${newCoords.lat.toFixed(6)}<br>
                    Lng: ${newCoords.lng.toFixed(6)}</small>
                </div>
            `);
        });
    }

    // Remover sugerencias
    removeSuggestions();

    console.log('📍 Ubicación seleccionada:', suggestion.display_name);
}

// Remover lista de sugerencias
function removeSuggestions() {
    if (suggestionsList) {
        suggestionsList.remove();
        suggestionsList = null;
    }
}

        // Actualizar campo de categoría según tipo de alojamiento
        function updateCategoryField() {
            const tipo = document.getElementById('tipo').value;
            const categoryGroup = document.getElementById('categoryGroup');
            
            // Tipos que requieren categoría (estrellas)
            const typesWithCategory = ['hotel', 'camping', 'casa_huespedes', 'crucero', 'lodge'];
            
            if (typesWithCategory.includes(tipo)) {
                categoryGroup.style.display = 'block';
                document.getElementById('categoria').required = true;
            } else {
                categoryGroup.style.display = 'none';
                document.getElementById('categoria').required = false;
                document.getElementById('categoria').value = '';
            }
        }

        // Configurar búsqueda en tiempo real
        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const languageFilter = document.getElementById('languageFilter');
            
            searchInput.addEventListener('input', filterResources);
            languageFilter.addEventListener('change', filterResources);
        }

        function filterResources() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const languageFilter = document.getElementById('languageFilter').value;
            
            // Filtrar recursos
            const filtered = resources[currentTab].filter(item => {
                const matchesSearch = !searchTerm || 
                    (item.titulo && item.titulo.toLowerCase().includes(searchTerm)) ||
                    (item.nombre && item.nombre.toLowerCase().includes(searchTerm)) ||
                    (item.descripcion && item.descripcion.toLowerCase().includes(searchTerm)) ||
                    (item.ubicacion && item.ubicacion.toLowerCase().includes(searchTerm));
                
                const matchesLanguage = !languageFilter || item.idioma === languageFilter;
                
                return matchesSearch && matchesLanguage;
            });
            
            // Renderizar resultados filtrados
            const grid = document.getElementById('contentGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (filtered.length === 0) {
                grid.style.display = 'none';
                emptyState.style.display = 'block';
                emptyState.innerHTML = `
                    <div class="empty-state-icon">🔍</div>
                    <h3>No se encontraron resultados</h3>
                    <p>Intenta con otros términos de búsqueda</p>
                `;
            } else {
                grid.style.display = 'grid';
                emptyState.style.display = 'none';
                grid.innerHTML = filtered.map(item => createResourceCard(item)).join('');
            }
        }

        // Funciones CRUD
        function viewResource(id) {
            alert(`Ver detalles del recurso ${id}`);
        }

        function editResource(id) {
            openModal('edit', id);
        }

        function deleteResource(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este recurso?')) {
                resources[currentTab] = resources[currentTab].filter(item => item.id !== id);
                renderResources();
                alert('Recurso eliminado correctamente');
            }
        }

        function loadResourceData(id) {
            const resource = resources[currentTab].find(item => item.id == id);
            if (resource) {
                Object.keys(resource).forEach(key => {
                    const field = document.getElementById(key);
                    if (field) {
                        field.value = resource[key];
                    }
                });
                
                if (currentTab === 'alojamientos') {
                    updateCategoryField();
                }
            }
        }

        // Submit del formulario
        document.getElementById('resourceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            const id = document.getElementById('resourceId').value;
            
            if (id) {
                const index = resources[currentTab].findIndex(item => item.id == id);
                if (index !== -1) {
                    resources[currentTab][index] = { ...resources[currentTab][index], ...data };
                }
                alert('Recurso actualizado correctamente');
            } else {
                data.id = Date.now();
                resources[currentTab].push(data);
                alert('Recurso creado correctamente');
            }
            
            closeModal();
            renderResources();
        });

        // Google Translate con idioma por defecto del sistema
        function initializeGoogleTranslate() {
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: DEFAULT_LANGUAGE,
                    includedLanguages: 'en,fr,pt,it,de,es',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false
                }, 'google_translate_element');

                setTimeout(loadSavedLanguage, 1000);
            }

            function saveLanguage(lang) {
                sessionStorage.setItem('language', lang);
                localStorage.setItem('preferredLanguage', lang);
            }

            function loadSavedLanguage() {
                const saved = sessionStorage.getItem('language') || 
                             localStorage.getItem('preferredLanguage') || 
                             DEFAULT_LANGUAGE;
                
                if (saved && saved !== DEFAULT_LANGUAGE) {
                    const select = document.querySelector('.goog-te-combo');
                    if (select) {
                        select.value = saved;
                        select.dispatchEvent(new Event('change'));
                    }
                }
            }

            if (!window.googleTranslateElementInit) {
                window.googleTranslateElementInit = googleTranslateElementInit;
                const script = document.createElement('script');
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.head.appendChild(script);
            }

            setTimeout(function() {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.addEventListener('change', function() {
                        if (this.value) saveLanguage(this.value);
                    });
                }
            }, 2000);
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('resourceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <!-- Script del sistema de autocompletado -->
    <script src="<?= APP_URL ?>/assets/js/location-autocomplete.js"></script>
    
    <script>
        // =====================================
        // INTEGRACIÓN CON EL SISTEMA EXISTENTE
        // =====================================
        
        // Modificar la función openModal existente
        (function() {
            const originalOpenModal = window.openModal;
            window.openModal = function(mode, id = null) {
                // Llamar función original
                originalOpenModal.call(this, mode, id);
                
                // Inicializar súper autocompletado
                setTimeout(() => {
                    if (window.superLocationAutocomplete) {
                        window.superLocationAutocomplete.initialize();
                        console.log('🌍 SUPER autocompletado inicializado en modal');
                    }
                }, 300);
            };
        })();
        
        // Modificar función closeModal existente  
        (function() {
            const originalCloseModal = window.closeModal;
            window.closeModal = function() {
                // Limpiar súper autocompletado
                if (window.superLocationAutocomplete) {
                    window.superLocationAutocomplete.removeSuggestions();
                }
                
                // Llamar función original
                originalCloseModal.call(this);
            };
        })();
        
        // Modificar función loadSpecificFields existente
        (function() {
            const originalLoadSpecificFields = window.loadSpecificFields;
            window.loadSpecificFields = function() {
                // Llamar función original
                originalLoadSpecificFields.call(this);
                
                // Inicializar súper autocompletado para nuevos campos
                setTimeout(() => {
                    if (window.superLocationAutocomplete) {
                        window.superLocationAutocomplete.initialize();
                        console.log('🗺️ Campos específicos configurados con SUPER autocompletado');
                    }
                }, 150);
            };
        })();
        
        // Inicialización automática cuando se detecten campos
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📚 Biblioteca con SÚPER autocompletado lista');
            
            // Verificar si ya hay campos presentes
            const existingFields = document.querySelectorAll('#ubicacion, #lugar_salida, #lugar_llegada');
            if (existingFields.length > 0) {
                setTimeout(() => {
                    initializeSuperLocationAutocomplete();
                }, 500);
            }
        });

        // Función para debugging desde consola del navegador
        window.debugBibliotecaAutocomplete = function() {
            console.log('🔍 DEBUG INFO:', {
                autocompleteLoaded: !!window.superLocationAutocomplete,
                debugInfo: window.superLocationAutocomplete ? window.superLocationAutocomplete.getDebugInfo() : null,
                fieldsFound: document.querySelectorAll('#ubicacion, #lugar_salida, #lugar_llegada').length
            });
        };
</script>

</body>
</html>