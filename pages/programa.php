<?php
// =====================================
// ARCHIVO: pages/programa.php - VERSIÓN COMPLETA CORREGIDA
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
            transition: background-color 0.3s;
        }
        
        .section-header:hover {
            background: #f0f0f0;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .section-dots {
            display: flex;
            gap: 4px;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        .section-content {
            padding: 24px;
            transition: all 0.3s ease;
        }
        
        .section-content.collapsed {
            padding: 0;
            max-height: 0;
            overflow: hidden;
        }
        
        /* Formulario */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .form-input, .form-select {
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .readonly-field {
            background: #f8f9fa;
            cursor: not-allowed;
        }
        
        /* Upload de imagen CORREGIDO */
        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
            position: relative;
        }
        
        .image-upload:hover {
            border-color: var(--primary-color);
            background: #f0f8ff;
        }
        
        .image-upload.has-image {
            border-color: var(--success-color);
            background: #f0f8f0;
        }
        
        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .upload-text {
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }
        
        .upload-hint {
            color: #666;
            font-size: 0.9rem;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }
        
        /* Botones */
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            padding: 24px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Notificaciones */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: var(--success-color);
        }
        
        .notification.error {
            background: var(--error-color);
        }
        
        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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

        <!-- ✅ FORM CORREGIDO CON ENCTYPE -->
        <form id="programa-form" enctype="multipart/form-data" method="POST">
            <!-- Campo oculto para ID si estamos editando -->
            <?php if ($programa_id): ?>
                <input type="hidden" name="programa_id" id="programa-id-hidden" value="<?= htmlspecialchars($programa_id) ?>">
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
                            <input type="text" class="form-input" id="traveler-name" name="traveler_name" placeholder="Nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Apellido del viajero</label>
                            <input type="text" class="form-input" id="traveler-lastname" name="traveler_lastname" placeholder="Apellido" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Destino</label>
                            <input type="text" class="form-input" id="destination" name="destination" placeholder="Ej: París, Francia" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Fecha de llegada</label>
                            <input type="date" class="form-input" id="arrival-date" name="arrival_date" required onchange="updateDepartureMinDate()">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Fecha de salida</label>
                            <input type="date" class="form-input" id="departure-date" name="departure_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Número de pasajeros</label>
                            <input type="number" class="form-input" id="passengers" name="passengers" min="1" max="20" value="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Acompañamiento</label>
                            <select class="form-select" id="accompaniment" name="accompaniment">
                                <option value="sin-acompanamiento">Sin acompañamiento</option>
                                <option value="pareja">En pareja</option>
                                <option value="familia">En familia</option>
                                <option value="amigos">Con amigos</option>
                                <option value="grupo">En grupo</option>
                                <option value="corporativo">Viaje corporativo</option>
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
                            <input type="text" class="form-input" id="program-title" name="program_title" placeholder="Ej: Escapada Romántica a París">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Idioma del presupuesto</label>
                            <select class="form-select" id="budget-language" name="budget_language">
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
                                <!-- ✅ INPUT CORREGIDO CON NAME -->
                                <input type="file" id="cover-input" name="cover_image" accept="image/*" style="display: none;">
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

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= APP_URL ?>/itinerarios'">
                    <i class="fas fa-arrow-left"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="save-btn">
                    <i class="fas fa-save"></i>
                    Guardar Programa
                </button>
            </div>
        </form>
    </div>

    <!-- ✅ JAVASCRIPT COMPLETAMENTE CORREGIDO -->
    <script>
        // Variables globales
        let isEditing = false;
        let currentCoverImage = null;

        // Configurar fecha mínima
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando programa.php');
            
            // Configurar fecha mínima como hoy
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('arrival-date').min = today;
            document.getElementById('departure-date').min = today;
            
            // Configurar upload de imagen
            setupImageUpload();
            
            // Configurar formulario
            setupForm();
            
            // Verificar si hay ID en la URL para cargar datos
            const urlParams = new URLSearchParams(window.location.search);
            const programaId = urlParams.get('id') || urlParams.get('continuar');
            
            if (programaId) {
                isEditing = true;
                cargarDatosPrograma(programaId);
            }
            
            console.log('✅ Inicialización completada');
        });

        // ✅ CONFIGURAR UPLOAD DE IMAGEN CORREGIDO CON VERIFICACIONES
        function setupImageUpload() {
            console.log('📷 Configurando upload de imagen...');
            
            const coverInput = document.getElementById('cover-input');
            if (!coverInput) {
                console.error('❌ Error: Elemento cover-input no encontrado durante setup');
                return;
            }
            
            console.log('✅ Elemento cover-input encontrado:', coverInput);
            
            coverInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('📷 Archivo seleccionado:', file.name, 'Tamaño:', file.size);
                    
                    // Validar tipo de archivo
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        showNotification('❌ Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WebP', 'error');
                        e.target.value = '';
                        return;
                    }
                    
                    // Validar tamaño (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        showNotification('❌ El archivo es demasiado grande. Máximo 5MB permitido.', 'error');
                        e.target.value = '';
                        return;
                    }
                    
                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const uploadArea = document.getElementById('cover-upload');
                        if (uploadArea) {
                            uploadArea.innerHTML = `
                                <img src="${e.target.result}" class="image-preview" alt="Preview">
                                <p style="margin-top: 12px; font-weight: 600; color: var(--primary-color);">📷 ${file.name}</p>
                                <small style="color: #666;">Se subirá al guardar el programa</small>
                            `;
                            uploadArea.classList.add('has-image');
                            console.log('✅ Preview de imagen mostrado');
                        } else {
                            console.error('❌ Área de upload no encontrada para mostrar preview');
                        }
                    };
                    
                    reader.onerror = function(e) {
                        console.error('❌ Error leyendo archivo:', e);
                        showNotification('Error al cargar la imagen', 'error');
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    console.log('⚠️ No se seleccionó archivo');
                }
            });
            
            console.log('✅ Event listener configurado para upload de imagen');
        }

        // ✅ CONFIGURAR FORMULARIO CORREGIDO
        function setupForm() {
            const form = document.getElementById('programa-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('📝 Formulario enviado');
                    guardarPrograma();
                });
            }
        }

        // ✅ FUNCIÓN DE GUARDADO COMPLETAMENTE CORREGIDA
        async function guardarPrograma() {
            console.log('=== 🚀 INICIANDO GUARDADO DE PROGRAMA ===');
            
            if (!validateForm()) {
                return;
            }

            setLoadingState(true);

            try {
                // ✅ CREAR FORMDATA CORRECTAMENTE
                const formData = new FormData();
                formData.append('action', 'save_programa');
                
                // Datos principales
                formData.append('traveler_name', document.getElementById('traveler-name').value.trim());
                formData.append('traveler_lastname', document.getElementById('traveler-lastname').value.trim());
                formData.append('destination', document.getElementById('destination').value.trim());
                formData.append('arrival_date', document.getElementById('arrival-date').value);
                formData.append('departure_date', document.getElementById('departure-date').value);
                formData.append('passengers', document.getElementById('passengers').value);
                formData.append('accompaniment', document.getElementById('accompaniment').value);
                
                // Datos de personalización
                formData.append('program_title', document.getElementById('program-title').value.trim());
                formData.append('budget_language', document.getElementById('budget-language').value);
                
                // ✅ IMAGEN DE PORTADA - CORREGIDO PARA EVITAR ERROR NULL
                const coverInput = document.getElementById('cover-input');
                if (coverInput && coverInput.files && coverInput.files.length > 0) {
                    const coverFile = coverInput.files[0];
                    console.log('📷 Agregando imagen al FormData:', coverFile.name, 'Tamaño:', coverFile.size);
                    formData.append('cover_image', coverFile);
                } else {
                    console.log('⚠️ No hay imagen seleccionada o elemento no encontrado');
                    if (!coverInput) {
                        console.error('❌ Elemento cover-input no encontrado en el DOM');
                    }
                }
                
                // ID del programa si es edición
                const programaIdElement = document.getElementById('programa-id-hidden');
                const programaId = programaIdElement ? programaIdElement.value : null;
                if (programaId) {
                    formData.append('programa_id', programaId);
                    console.log('✏️ Editando programa ID:', programaId);
                }

                // ✅ DEBUG: Mostrar contenido del FormData
                console.log('📋 Contenido del FormData:');
                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        console.log(`  ${key}: [FILE] ${value.name} (${value.size} bytes)`);
                    } else {
                        console.log(`  ${key}: ${value}`);
                    }
                }

                // ✅ ENVIAR DATOS
                console.log('🌐 Enviando datos a la API...');
                const response = await fetch('<?= APP_URL ?>/programa/api', {
                    method: 'POST',
                    body: formData // ✅ NO agregar Content-Type header para FormData
                });

                console.log('📡 Respuesta recibida, status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const result = await response.json();
                console.log('📋 Resultado de la API:', result);

                if (result.success) {
                    showNotification('✅ ' + result.message, 'success');
                    
                    // Si es un programa nuevo, configurar para edición
                    if (result.id && !programaId) {
                        let hiddenInput = document.getElementById('programa-id-hidden');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'programa_id';
                            hiddenInput.id = 'programa-id-hidden';
                            document.getElementById('programa-form').appendChild(hiddenInput);
                        }
                        hiddenInput.value = result.id;
                        
                        // Actualizar botón y URL
                        document.getElementById('save-btn').innerHTML = '<i class="fas fa-save"></i> Actualizar Programa';
                        
                        if (history.pushState) {
                            const newUrl = `<?= APP_URL ?>/programa?id=${result.id}`;
                            window.history.pushState({path: newUrl}, '', newUrl);
                        }
                        
                        // Mostrar ID de solicitud
                        if (result.request_id) {
                            document.getElementById('request-id').value = result.request_id;
                            setTimeout(() => {
                                showNotification(`📋 ID de solicitud: ${result.request_id}`, 'success');
                            }, 1500);
                        }
                        
                        isEditing = true;
                    }
                } else {
                    throw new Error(result.error || 'Error desconocido al guardar');
                }

            } catch (error) {
                console.error('❌ Error completo:', error);
                showNotification('❌ Error al guardar: ' + error.message, 'error');
            } finally {
                setLoadingState(false);
                console.log('=== ✅ GUARDADO COMPLETADO ===');
            }
        }

        // ✅ FUNCIÓN PARA CARGAR DATOS AL EDITAR
        async function cargarDatosPrograma(id) {
            try {
                console.log('📖 Cargando programa ID:', id);
                
                const response = await fetch(`<?= APP_URL ?>/programa/api?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const data = result.data;
                    console.log('📋 Datos cargados:', data);
                    
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
                    document.getElementById('budget-language').value = data.idioma_predeterminado || 'es';
                    
                    // ✅ MOSTRAR IMAGEN EXISTENTE
                    if (data.foto_portada) {
                        const uploadArea = document.getElementById('cover-upload');
                        uploadArea.innerHTML = `
                            <img src="${data.foto_portada}" class="image-preview" alt="Portada actual">
                            <p style="margin-top: 12px; font-weight: 600; color: var(--success-color);">✅ Imagen actual</p>
                            <small style="color: #666;">Clic para cambiar imagen</small>
                        `;
                        uploadArea.classList.add('has-image');
                        console.log('🖼️ Imagen de portada cargada:', data.foto_portada);
                    }

                    // Actualizar botón
                    document.getElementById('save-btn').innerHTML = '<i class="fas fa-save"></i> Actualizar Programa';
                }
            } catch (error) {
                console.error('❌ Error cargando datos:', error);
                showNotification('Error cargando los datos del programa', 'error');
            }
        }

        // Funciones auxiliares
        function updateDepartureMinDate() {
            const arrivalDate = document.getElementById('arrival-date').value;
            if (arrivalDate) {
                document.getElementById('departure-date').min = arrivalDate;
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
                if (!element.value.trim()) {
                    showNotification(`❌ ${field.label} es requerido`, 'error');
                    element.focus();
                    return false;
                }
            }

            // Validar fechas
            const arrival = new Date(document.getElementById('arrival-date').value);
            const departure = new Date(document.getElementById('departure-date').value);

            if (arrival >= departure) {
                showNotification('❌ La fecha de salida debe ser posterior a la fecha de llegada', 'error');
                return false;
            }

            return true;
        }

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

        function setLoadingState(loading) {
            const form = document.getElementById('programa-form');
            const saveBtn = document.getElementById('save-btn');
            
            if (loading) {
                form.classList.add('loading');
                saveBtn.innerHTML = '<i class="fas fa-spinner spinner"></i> Guardando...';
            } else {
                form.classList.remove('loading');
                saveBtn.disabled = false;
                if (isEditing) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Programa';
                } else {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Programa';
                }
            }
        }

        function showNotification(message, type = 'success') {
            // Remover notificación existente
            const existing = document.querySelector('.notification');
            if (existing) {
                existing.remove();
            }
            
            // Crear nueva notificación
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        // ✅ FUNCIÓN DE DEBUG PARA VERIFICAR ESTADO - MEJORADA
        function debugFormState() {
            console.log('=== 🔍 DEBUG DEL FORMULARIO ===');
            
            // Verificar formulario
            const form = document.getElementById('programa-form');
            console.log('Form element:', form);
            if (form) {
                console.log('Form enctype:', form.enctype);
                console.log('Form method:', form.method);
            } else {
                console.error('❌ Formulario no encontrado!');
            }
            
            // Verificar input de imagen
            const coverInput = document.getElementById('cover-input');
            console.log('Cover input element:', coverInput);
            if (coverInput) {
                console.log('Cover input name:', coverInput.name);
                console.log('Cover input type:', coverInput.type);
                console.log('Cover input accept:', coverInput.accept);
                console.log('Cover input files:', coverInput.files);
                console.log('Cover input files length:', coverInput.files ? coverInput.files.length : 'N/A');
                
                if (coverInput.files && coverInput.files.length > 0) {
                    const file = coverInput.files[0];
                    console.log('Archivo seleccionado:');
                    console.log('  - Nombre:', file.name);
                    console.log('  - Tamaño:', file.size);
                    console.log('  - Tipo:', file.type);
                    console.log('  - Última modificación:', new Date(file.lastModified));
                } else {
                    console.log('⚠️ No hay archivos seleccionados');
                }
            } else {
                console.error('❌ Elemento cover-input no encontrado!');
            }
            
            // Verificar área de upload
            const uploadArea = document.getElementById('cover-upload');
            console.log('Upload area element:', uploadArea);
            if (uploadArea) {
                console.log('Upload area classes:', uploadArea.className);
                console.log('Upload area innerHTML length:', uploadArea.innerHTML.length);
            } else {
                console.error('❌ Área de upload no encontrada!');
            }
            
            // Verificar todos los elementos del formulario
            console.log('--- Verificando todos los elementos del formulario ---');
            const requiredElements = [
                'traveler-name', 'traveler-lastname', 'destination', 
                'arrival-date', 'departure-date', 'passengers', 
                'accompaniment', 'program-title', 'budget-language'
            ];
            
            requiredElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    console.log(`✅ ${id}: ${element.value || 'vacío'}`);
                } else {
                    console.error(`❌ ${id}: NO ENCONTRADO`);
                }
            });
            
            // Verificar FormData de prueba
            console.log('--- Test de FormData ---');
            try {
                const testFormData = new FormData();
                testFormData.append('test', 'value');
                
                if (coverInput && coverInput.files && coverInput.files.length > 0) {
                    testFormData.append('test_file', coverInput.files[0]);
                }
                
                console.log('FormData entries:');
                for (let [key, value] of testFormData.entries()) {
                    if (value instanceof File) {
                        console.log(`  ${key}: [FILE] ${value.name}`);
                    } else {
                        console.log(`  ${key}: ${value}`);
                    }
                }
            } catch (error) {
                console.error('Error creando FormData de prueba:', error);
            }
            
            console.log('=== 🔍 FIN DEBUG ===');
        }

        // ✅ FUNCIÓN PARA PROBAR CONECTIVIDAD CON LA API
        async function testAPI() {
            console.log('🧪 Probando conexión con la API...');
            
            try {
                const response = await fetch('<?= APP_URL ?>/programa/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=test'
                });
                
                console.log('API Response status:', response.status);
                console.log('API Response headers:', response.headers);
                
                const text = await response.text();
                console.log('API Response body:', text);
                
                try {
                    const json = JSON.parse(text);
                    console.log('API Response JSON:', json);
                } catch (e) {
                    console.log('Response is not valid JSON');
                }
                
            } catch (error) {
                console.error('Error testing API:', error);
            }
        }

        // ✅ FUNCIÓN PARA VERIFICAR CONFIGURACIÓN DEL SERVIDOR
        function checkServerConfig() {
            console.log('🔧 Verificando configuración del navegador...');
            
            // Verificar soporte para FormData
            console.log('FormData support:', typeof FormData !== 'undefined');
            
            // Verificar soporte para File API
            console.log('File API support:', typeof File !== 'undefined');
            
            // Verificar soporte para FileReader
            console.log('FileReader support:', typeof FileReader !== 'undefined');
            
            // Verificar fetch
            console.log('Fetch support:', typeof fetch !== 'undefined');
            
            // Información del navegador
            console.log('User agent:', navigator.userAgent);
            console.log('Platform:', navigator.platform);
            
            // Verificar tamaño máximo teórico de archivo
            const testInput = document.createElement('input');
            testInput.type = 'file';
            console.log('Input file support:', testInput.files !== undefined);
        }

        // Agregar funciones de debug al window para poder llamarlas desde consola
        window.debugFormState = debugFormState;
        window.testAPI = testAPI;
        window.checkServerConfig = checkServerConfig;

        // ✅ VERIFICACIÓN FINAL DEL DOM
        function verificarDOMCompleto() {
            console.log('🔍 Verificando DOM completo...');
            
            const elementosRequeridos = [
                'programa-form',
                'cover-input', 
                'cover-upload',
                'traveler-name',
                'traveler-lastname', 
                'destination',
                'arrival-date',
                'departure-date',
                'save-btn'
            ];
            
            let todosEncontrados = true;
            
            elementosRequeridos.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    console.log(`✅ ${id}: OK`);
                } else {
                    console.error(`❌ ${id}: NO ENCONTRADO`);
                    todosEncontrados = false;
                }
            });
            
            if (todosEncontrados) {
                console.log('✅ Todos los elementos requeridos están presentes');
            } else {
                console.error('❌ Faltan elementos en el DOM - el formulario no funcionará correctamente');
            }
            
            // Verificar estructura del formulario
            const form = document.getElementById('programa-form');
            if (form) {
                console.log('📋 Atributos del formulario:');
                console.log('  - enctype:', form.enctype || 'NO DEFINIDO');
                console.log('  - method:', form.method || 'NO DEFINIDO');
                console.log('  - action:', form.action || 'VACÍO (correcto)');
            }
            
            return todosEncontrados;
        }

        console.log('✅ Script de programa.php cargado completamente');
        console.log('💡 Funciones de debug disponibles:');
        console.log('   - debugFormState() - Inspeccionar estado del formulario');
        console.log('   - testAPI() - Probar conectividad con la API');
        console.log('   - checkServerConfig() - Verificar configuración del navegador');
        console.log('   - verificarDOMCompleto() - Verificar que todos los elementos estén presentes');
        
        // Ejecutar verificación automática después de un pequeño delay
        setTimeout(verificarDOMCompleto, 500);
    </script>
</body>
</html>