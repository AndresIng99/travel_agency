<?php
// =====================================
// ARCHIVO: pages/programa.php - SOLO SECCIÓN "MI PROGRAMA"
// =====================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::requireLogin();
ConfigManager::init();

$user = App::getUser();
$config = ConfigManager::get();
$userColors = ConfigManager::getColorsForRole($user['role']);
$companyName = ConfigManager::getCompanyName();

// Obtener ID del programa si se está editando
$programa_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= $config['default_language'] ?? 'es' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Programa - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/global.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: <?= $userColors['primary'] ?>;
            --secondary-color: <?= $userColors['secondary'] ?>;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
            --text-color: #333;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
            --error-color: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 1rem;
        }
        
        /* Secciones colapsables */
        .collapsible-section {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            background: #fafafa;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .section-header:hover {
            background: #f0f0f0;
        }
        
        .section-header.collapsed {
            border-bottom: none;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-color);
        }
        
        .section-dots {
            display: flex;
            gap: 4px;
        }
        
        .dot {
            width: 6px;
            height: 6px;
            background: #999;
            border-radius: 50%;
        }
        
        .section-content {
            padding: 24px;
            display: block;
        }
        
        .section-content.collapsed {
            display: none;
        }
        
        /* Formularios */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 0.95rem;
        }
        
        .form-input, .form-select, .form-textarea {
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .readonly-field {
            background: #f8f9fa;
            color: #666;
            font-weight: 500;
            border: 2px solid #e9ecef;
        }
        
        /* Subida de imágenes */
        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .image-upload:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
        }
        
        .image-upload.has-image {
            border-color: var(--primary-color);
            background: #f0f9ff;
            padding: 20px;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 16px;
        }
        
        .upload-text {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 8px;
        }
        
        .upload-hint {
            font-size: 0.9rem;
            color: #999;
        }
        
        /* Botones */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: #4b5563;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #f0f0f0;
        }
        
        /* Notificaciones */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 8px;
            font-weight: 600;
            z-index: 1000;
            max-width: 400px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .notification.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .section-header {
                padding: 16px 20px;
            }
            
            .section-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header de la página -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-map-marked-alt"></i>
                Mi Programa
            </h1>
            <p class="page-subtitle">Configuración básica del programa de viaje</p>
        </div>

        <form id="programa-form" enctype="multipart/form-data">
            <!-- Campo oculto para ID si estamos editando -->
            <?php if ($programa_id): ?>
                <input type="hidden" id="programa-id" value="<?= htmlspecialchars($programa_id) ?>">
            <?php endif; ?>

            <!-- Sección: Solicitud del viajero -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-title">
                        <div class="section-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                        <span>Solicitud del viajero</span>
                    </div>
                    <i class="fas fa-chevron-up"></i>
                </div>
                <div class="section-content">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">ID de la solicitud</label>
                            <input type="text" class="form-input readonly-field" id="request-id" readonly placeholder="Se generará automáticamente" style="font-weight: 600; color: var(--primary-color);">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nombre del viajero</label>
                            <input type="text" class="form-input" id="traveler-name" placeholder="Nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Apellido del viajero</label>
                            <input type="text" class="form-input" id="traveler-lastname" placeholder="Apellido" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Destino</label>
                            <input type="text" class="form-input" id="destination" placeholder="Ej: París, Francia" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Fecha de llegada</label>
                            <input type="date" class="form-input" id="arrival-date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Fecha de salida</label>
                            <input type="date" class="form-input" id="departure-date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Número de pasajeros</label>
                            <input type="number" class="form-input" id="passengers" min="1" max="50" value="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Acompañamiento</label>
                            <select class="form-select" id="accompaniment">
                                <option value="sin-acompanamiento">Sin acompañamiento</option>
                                <option value="guia-local">Guía local</option>
                                <option value="guia-especializado">Guía especializado</option>
                                <option value="acompanamiento-completo">Acompañamiento completo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Personalización -->
            <div class="collapsible-section">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-title">
                        <div class="section-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                        <span>Personalización</span>
                    </div>
                    <i class="fas fa-chevron-up"></i>
                </div>
                <div class="section-content">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Título del programa</label>
                            <input type="text" class="form-input" id="program-title" placeholder="Ej: Escapada Romántica a París">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Idioma del presupuesto</label>
                            <select class="form-select" id="budget-language">
                                <option value="es">🇪🇸 Español</option>
                                <option value="en">🇺🇸 English</option>
                                <option value="fr">🇫🇷 Français</option>
                                <option value="de">🇩🇪 Deutsch</option>
                                <option value="it">🇮🇹 Italiano</option>
                                <option value="pt">🇵🇹 Português</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Foto de portada</label>
                            <div class="image-upload" id="cover-upload" onclick="document.getElementById('cover-input').click()">
                                <input type="file" id="cover-input" accept="image/*" style="display: none;">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <p class="upload-text">Haga clic para subir una imagen de portada</p>
                                    <small class="upload-hint">JPG, PNG o WebP (máx. 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones del formulario -->
            <div class="form-actions">
                <a href="<?= APP_URL ?>/programas" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
                <button type="button" class="btn btn-primary" id="save-btn" onclick="savePrograma()">
                    <i class="fas fa-save"></i>
                    Guardar Programa
                </button>
            </div>
        </form>
    </div>

    <!-- Container de notificaciones -->
    <div id="notification-container"></div>

    <script>
        // Variables globales
        let currentCoverImage = null;
        let isEditing = false;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            setupEventListeners();
        });

        function initializeForm() {
            // Verificar si estamos editando
            const programaId = document.getElementById('programa-id');
            if (programaId && programaId.value) {
                isEditing = true;
                loadProgramaData(programaId.value);
            }
            
            // Establecer fecha mínima como hoy
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('arrival-date').min = today;
            document.getElementById('departure-date').min = today;
        }

        function setupEventListeners() {
            // Event listener para imagen de portada
            const coverInput = document.getElementById('cover-input');
            if (coverInput) {
                coverInput.addEventListener('change', function(e) {
                    handleImageUpload(e.target);
                });
                console.log("Event listener para imagen de portada configurado");
            } else {
                console.warn("Elemento cover-input no encontrado");
            }

            // Event listener para fechas
            const arrivalDateInput = document.getElementById('arrival-date');
            if (arrivalDateInput) {
                arrivalDateInput.addEventListener('change', function() {
                    updateDepartureMinDate();
                });
                console.log("Event listener para fecha de llegada configurado");
            } else {
                console.warn("Elemento arrival-date no encontrado");
            }

            // Prevenir envío del formulario con Enter
            const form = document.getElementById('programa-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    savePrograma();
                });
                console.log("Event listener para formulario configurado");
            } else {
                console.warn("Elemento programa-form no encontrado");
            }
        }

        // Funciones de UI
        function toggleSection(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('i');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                header.classList.remove('collapsed');
                icon.className = 'fas fa-chevron-up';
            } else {
                content.classList.add('collapsed');
                header.classList.add('collapsed');
                icon.className = 'fas fa-chevron-down';
            }
        }

        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;

            console.log("Procesando imagen:", file.name, "Tamaño:", file.size);

            // Validar tamaño
            if (file.size > 5 * 1024 * 1024) {
                showNotification('La imagen es demasiado grande. Máximo 5MB.', 'error');
                input.value = ''; // Limpiar input
                return;
            }

            // Validar tipo
            if (!file.type.startsWith('image/')) {
                showNotification('Por favor seleccione una imagen válida.', 'error');
                input.value = ''; // Limpiar input
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                console.log("Imagen cargada correctamente en FileReader");
                
                // Guardar referencia de la imagen
                currentCoverImage = {
                    file: file,
                    dataUrl: e.target.result,
                    name: file.name
                };
                
                // Mostrar preview
                const uploadArea = document.getElementById('cover-upload');
                uploadArea.innerHTML = `
                    <img src="${e.target.result}" class="image-preview" alt="Preview">
                    <p style="margin-top: 12px; font-weight: 600;">${file.name}</p>
                    <small style="color: #666;">Clic para cambiar imagen</small>
                `;
                uploadArea.classList.add('has-image');
                
                console.log("Preview de imagen mostrado");
            };
            
            reader.onerror = function(e) {
                console.error("Error leyendo archivo:", e);
                showNotification('Error al cargar la imagen', 'error');
            };
            
            reader.readAsDataURL(file);
        }

        function updateDepartureMinDate() {
            const arrivalDate = document.getElementById('arrival-date').value;
            if (arrivalDate) {
                document.getElementById('departure-date').min = arrivalDate;
            }
        }

        // Función principal de guardado
        async function savePrograma() {
            try {
                console.log("=== INICIANDO GUARDADO DE PROGRAMA ===");
                
                // Validar campos requeridos
                if (!validateForm()) {
                    return;
                }

                // Mostrar loading
                setLoadingState(true);

                // Preparar datos del formulario
                const formData = new FormData();
                formData.append('action', isEditing ? 'update' : 'create');
                
                // Si estamos editando, incluir el ID
                if (isEditing) {
                    const programaIdField = document.getElementById('programa-id');
                    if (programaIdField && programaIdField.value) {
                        formData.append('programa_id', programaIdField.value);
                        console.log("Modo edición - Programa ID:", programaIdField.value);
                    }
                }
                
                // Función para obtener valor de elemento de forma segura
                function getElementValue(id, defaultValue = '') {
                    const element = document.getElementById(id);
                    if (!element) {
                        console.warn(`Elemento con ID '${id}' no encontrado`);
                        return defaultValue;
                    }
                    return element.value || defaultValue;
                }
                
                // Datos de la solicitud del viajero - con verificación segura
                const travelerName = getElementValue('traveler-name').trim();
                const travelerLastname = getElementValue('traveler-lastname').trim();
                const destination = getElementValue('destination').trim();
                const arrivalDate = getElementValue('arrival-date');
                const departureDate = getElementValue('departure-date');
                const passengers = getElementValue('passengers', '1');
                const accompaniment = getElementValue('accompaniment', 'sin-acompanamiento');
                
                formData.append('traveler_name', travelerName);
                formData.append('traveler_lastname', travelerLastname);
                formData.append('destination', destination);
                formData.append('arrival_date', arrivalDate);
                formData.append('departure_date', departureDate);
                formData.append('passengers', passengers);
                formData.append('accompaniment', accompaniment);
                
                console.log("Datos solicitud:", {
                    traveler_name: travelerName,
                    traveler_lastname: travelerLastname,
                    destination: destination,
                    arrival_date: arrivalDate,
                    departure_date: departureDate,
                    passengers: passengers,
                    accompaniment: accompaniment
                });
                
                // Datos de personalización - con verificación segura
                const programTitle = getElementValue('program-title').trim();
                const budgetLanguage = getElementValue('budget-language', 'es');
                
                formData.append('program_title', programTitle);
                formData.append('budget_language', budgetLanguage);
                
                console.log("Datos personalización:", {
                    program_title: programTitle,
                    budget_language: budgetLanguage
                });
                
                // Imagen de portada - verificar que existe el elemento
                const coverInput = document.getElementById('cover-input');
                if (coverInput && coverInput.files && coverInput.files.length > 0) {
                    formData.append('cover_image', coverInput.files[0]);
                    console.log("Imagen de portada añadida:", coverInput.files[0].name, "Tamaño:", coverInput.files[0].size);
                } else {
                    console.log("Sin imagen de portada seleccionada o elemento no encontrado");
                }

                // Log de todos los datos que se envían
                console.log("=== DATOS A ENVIAR ===");
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
                }

                // Enviar al servidor
                console.log("Enviando al servidor...");
                const response = await fetch('<?= APP_URL ?>/modules/programa/api.php', {
                    method: 'POST',
                    body: formData
                });

                console.log("Respuesta del servidor recibida");
                const result = await response.json();
                console.log("Resultado:", result);

                if (result.success) {
                    showNotification(
                        isEditing ? 'Programa actualizado exitosamente' : 'Programa creado exitosamente', 
                        'success'
                    );
                    
                    // Si era creación nueva, mostrar el ID generado
                    if (!isEditing && result.request_id) {
                        // Mostrar el ID generado en el campo inmediatamente
                        const requestIdField = document.getElementById('request-id');
                        requestIdField.value = result.request_id;
                        requestIdField.placeholder = '';
                        
                        // Resaltar el campo brevemente
                        requestIdField.style.background = '#e8f5e8';
                        requestIdField.style.borderColor = 'var(--primary-color)';
                        
                        setTimeout(() => {
                            requestIdField.style.background = '#f8f9fa';
                            requestIdField.style.borderColor = '#e9ecef';
                        }, 2000);
                        
                        // Actualizar el estado a edición
                        isEditing = true;
                        
                        // Agregar campo oculto con el ID del programa
                        if (result.id && !document.getElementById('programa-id')) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.id = 'programa-id';
                            hiddenInput.value = result.id;
                            document.getElementById('programa-form').appendChild(hiddenInput);
                        }
                        
                        // Actualizar botón
                        document.getElementById('save-btn').innerHTML = '<i class="fas fa-save"></i> Actualizar Programa';
                        
                        // Actualizar URL sin recargar la página
                        if (history.pushState) {
                            const newUrl = `<?= APP_URL ?>/programa?id=${result.id}`;
                            window.history.pushState({path: newUrl}, '', newUrl);
                        }
                        
                        // Mostrar notificación adicional con el ID
                        setTimeout(() => {
                            showNotification(`ID de solicitud generado: ${result.request_id}`, 'success');
                        }, 1000);
                    }
                } else {
                    throw new Error(result.error || 'Error al guardar el programa');
                }

            } catch (error) {
                console.error('Error completo:', error);
                showNotification('Error al guardar: ' + error.message, 'error');
            } finally {
                setLoadingState(false);
            }
        }

        function validateForm() {
            const requiredFields = [
                { id: 'traveler-name', label: 'Nombre del viajero' },
                { id: 'traveler-lastname', label: 'Apellido del viajero' },
                { id: 'destination', label: 'Destino' },
                { id: 'arrival-date', label: 'Fecha de llegada' },
                { id: 'departure-date', label: 'Fecha de salida' }
            ];

            for (const field of requiredFields) {
                const element = document.getElementById(field.id);
                if (!element) {
                    console.error(`Campo requerido no encontrado: ${field.id}`);
                    showNotification(`Error en formulario: Campo ${field.label} no encontrado`, 'error');
                    return false;
                }
                
                if (!element.value || !element.value.trim()) {
                    element.focus();
                    showNotification(`Por favor complete el campo: ${field.label}`, 'error');
                    return false;
                }
            }

            // Validar fechas
            const arrivalDateElement = document.getElementById('arrival-date');
            const departureDateElement = document.getElementById('departure-date');
            
            if (!arrivalDateElement || !departureDateElement) {
                showNotification('Error: Campos de fecha no encontrados', 'error');
                return false;
            }
            
            const arrivalDate = new Date(arrivalDateElement.value);
            const departureDate = new Date(departureDateElement.value);
            
            if (departureDate <= arrivalDate) {
                departureDateElement.focus();
                showNotification('La fecha de salida debe ser posterior a la fecha de llegada', 'error');
                return false;
            }

            // Validar número de pasajeros
            const passengersElement = document.getElementById('passengers');
            if (passengersElement) {
                const passengers = parseInt(passengersElement.value);
                if (isNaN(passengers) || passengers < 1 || passengers > 50) {
                    passengersElement.focus();
                    showNotification('El número de pasajeros debe estar entre 1 y 50', 'error');
                    return false;
                }
            }

            return true;
        }

        function setLoadingState(loading) {
            const saveBtn = document.getElementById('save-btn');
            if (loading) {
                saveBtn.disabled = true;
                saveBtn.classList.add('loading');
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            } else {
                saveBtn.disabled = false;
                saveBtn.classList.remove('loading');
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Programa';
            }
        }

        function showNotification(message, type = 'success') {
            const container = document.getElementById('notification-container');
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        async function loadProgramaData(programaId) {
            try {
                const response = await fetch(`<?= APP_URL ?>/modules/programa/api.php?action=get&id=${programaId}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    
                    // Llenar campos del formulario
                    document.getElementById('request-id').value = data.id_solicitud || '';
                    document.getElementById('traveler-name').value = data.nombre_viajero || '';
                    document.getElementById('traveler-lastname').value = data.apellido_viajero || '';
                    document.getElementById('destination').value = data.destino || '';
                    document.getElementById('arrival-date').value = data.fecha_llegada || '';
                    document.getElementById('departure-date').value = data.fecha_salida || '';
                    document.getElementById('passengers').value = data.numero_pasajeros || '1';
                    document.getElementById('accompaniment').value = data.acompanamiento || 'sin-acompanamiento';
                    document.getElementById('program-title').value = data.titulo_programa || '';
                    document.getElementById('budget-language').value = data.idioma_presupuesto || 'es';
                    
                    // Mostrar imagen de portada si existe
                    if (data.foto_portada) {
                        const uploadArea = document.getElementById('cover-upload');
                        uploadArea.innerHTML = `
                            <img src="${data.foto_portada}" class="image-preview" alt="Portada">
                            <p style="margin-top: 12px; font-weight: 600;">Imagen actual</p>
                            <small style="color: #666;">Clic para cambiar imagen</small>
                        `;
                        uploadArea.classList.add('has-image');
                    }

                    // Actualizar botón
                    document.getElementById('save-btn').innerHTML = '<i class="fas fa-save"></i> Actualizar Programa';
                }
            } catch (error) {
                console.error('Error cargando datos:', error);
                showNotification('Error cargando los datos del programa', 'error');
            }
        }
    </script>
</body>
</html>