<?php
// =====================================
// ARCHIVO: pages/admin_config.php - Configuración del Sistema
// =====================================
?>
<?php 
App::requireRole('admin');
$user = App::getUser(); 

// Obtener configuración actual
try {
    $db = Database::getInstance();
    $config = $db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
    if (!$config) {
        // Crear configuración por defecto si no existe
        $db->insert('company_settings', [
            'company_name' => 'Travel Agency',
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'language' => 'es'
        ]);
        $config = $db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
    }
} catch(Exception $e) {
    $config = [
        'company_name' => 'Travel Agency',
        'logo_url' => '',
        'background_image' => '',
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'language' => 'es'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
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
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Configuration Sections */
        .config-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #e53e3e;
        }

        .section-title {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #e53e3e;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        /* Color Picker */
        .color-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .color-picker {
            width: 60px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            overflow: hidden;
        }

        .color-text {
            flex: 1;
            font-family: monospace;
            text-transform: uppercase;
        }

        /* Image Upload */
        .image-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .image-upload:hover {
            border-color: #e53e3e;
            background-color: #fef5f5;
        }

        .image-upload.dragover {
            border-color: #e53e3e;
            background-color: #fef5f5;
        }

        .image-upload input {
            display: none;
        }

        .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .upload-icon {
            font-size: 48px;
            color: #e53e3e;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }

        /* Preview Section */
        .preview-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .preview-header {
            padding: 20px 30px;
            border-radius: 10px;
            color: white;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .preview-company {
            font-size: 24px;
            font-weight: bold;
        }

        .preview-tagline {
            opacity: 0.9;
            margin-top: 5px;
        }

        /* Save Button */
        .save-section {
            text-align: center;
            margin: 30px 0;
        }

        .save-btn {
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3);
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 62, 62, 0.4);
        }

        .save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Success/Error Messages */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: 500;
            display: none;
        }

        .message.success {
            background: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }

        .message.error {
            background: #fed7d7;
            color: #e53e3e;
            border: 1px solid #feb2b2;
        }

        /* Advanced Settings */
        .advanced-toggle {
            background: #f7fafc;
            padding: 15px 20px;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .advanced-toggle:hover {
            background: #edf2f7;
        }

        .advanced-content {
            display: none;
            margin-top: 20px;
        }

        .advanced-content.show {
            display: block;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #e53e3e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .config-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="<?= APP_URL ?>/administrador" class="back-btn">← Usuarios</a>
            <h2>⚙️ Configuración del Sistema</h2>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div id="google_translate_element"></div>
            <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Preview Section -->
        <div class="preview-section">
            <h2 class="section-title">
                <span>👁️</span>
                Vista Previa
            </h2>
            <div class="preview-header" id="headerPreview" style="background: linear-gradient(135deg, <?= $config['primary_color'] ?> 0%, <?= $config['secondary_color'] ?> 100%);">
                <div class="preview-company" id="companyPreview"><?= htmlspecialchars($config['company_name']) ?></div>
                <div class="preview-tagline">Sistema de Gestión de Viajes</div>
            </div>
        </div>

        <!-- Messages -->
        <div id="successMessage" class="message success"></div>
        <div id="errorMessage" class="message error"></div>

        <!-- Configuration Form -->
        <form id="configForm">
            <!-- Basic Settings -->
            <div class="config-section">
                <h2 class="section-title">
                    <span>🏢</span>
                    Información de la Empresa
                </h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_name">Nombre de la Empresa</label>
                        <input type="text" id="company_name" name="company_name" 
                               value="<?= htmlspecialchars($config['company_name']) ?>" 
                               placeholder="Travel Agency" required>
                    </div>

                    <div class="form-group">
                        <label for="language">Idioma por Defecto</label>
                        <select id="language" name="language">
                            <option value="es" <?= $config['language'] === 'es' ? 'selected' : '' ?>>Español</option>
                            <option value="en" <?= $config['language'] === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="fr" <?= $config['language'] === 'fr' ? 'selected' : '' ?>>Français</option>
                            <option value="pt" <?= $config['language'] === 'pt' ? 'selected' : '' ?>>Português</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Visual Settings -->
            <div class="config-section">
                <h2 class="section-title">
                    <span>🎨</span>
                    Personalización Visual
                </h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="primary_color">Color Primario</label>
                        <div class="color-input">
                            <input type="color" id="primary_color" name="primary_color" 
                                   class="color-picker" value="<?= $config['primary_color'] ?>">
                            <input type="text" class="color-text" 
                                   value="<?= $config['primary_color'] ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="secondary_color">Color Secundario</label>
                        <div class="color-input">
                            <input type="color" id="secondary_color" name="secondary_color" 
                                   class="color-picker" value="<?= $config['secondary_color'] ?>">
                            <input type="text" class="color-text" 
                                   value="<?= $config['secondary_color'] ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 25px;">
                    <div class="form-group">
                        <label for="logo_url">Logo de la Empresa</label>
                        <div class="image-upload" onclick="document.getElementById('logoInput').click()">
                            <input type="file" id="logoInput" accept="image/*">
                            <div class="upload-content">
                                <div class="upload-icon">📷</div>
                                <div>
                                    <strong>Subir Logo</strong><br>
                                    <small>PNG, JPG o SVG (máx. 2MB)</small>
                                </div>
                            </div>
                            <?php if ($config['logo_url']): ?>
                            <img src="<?= htmlspecialchars($config['logo_url']) ?>" 
                                 class="image-preview" id="logoPreview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="logo_url" name="logo_url" value="<?= htmlspecialchars($config['logo_url'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="background_image">Imagen de Fondo</label>
                        <div class="image-upload" onclick="document.getElementById('backgroundInput').click()">
                            <input type="file" id="backgroundInput" accept="image/*">
                            <div class="upload-content">
                                <div class="upload-icon">🖼️</div>
                                <div>
                                    <strong>Subir Fondo</strong><br>
                                    <small>PNG, JPG (máx. 5MB)</small>
                                </div>
                            </div>
                            <?php if ($config['background_image']): ?>
                            <img src="<?= htmlspecialchars($config['background_image']) ?>" 
                                 class="image-preview" id="backgroundPreview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="background_image" name="background_image" value="<?= htmlspecialchars($config['background_image'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Advanced Settings Toggle -->
            <div class="advanced-toggle" onclick="toggleAdvanced()">
                <strong>⚡ Configuración Avanzada</strong>
                <span style="float: right;" id="advancedIcon">▼</span>
            </div>

            <div class="advanced-content" id="advancedContent">
                <div class="config-section">
                    <h2 class="section-title">
                        <span>🔧</span>
                        Configuraciones Técnicas
                    </h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="session_timeout">Tiempo de Sesión (minutos)</label>
                            <input type="number" id="session_timeout" name="session_timeout" 
                                   value="60" min="15" max="480" placeholder="60">
                            <small style="color: #718096;">Tiempo antes de cerrar sesión automáticamente</small>
                        </div>

                        <div class="form-group">
                            <label for="max_file_size">Tamaño Máximo de Archivo (MB)</label>
                            <input type="number" id="max_file_size" name="max_file_size" 
                                   value="10" min="1" max="100" placeholder="10">
                            <small style="color: #718096;">Límite para subida de imágenes</small>
                        </div>

                        <div class="form-group">
                            <label for="backup_frequency">Frecuencia de Respaldo</label>
                            <select id="backup_frequency" name="backup_frequency">
                                <option value="daily">Diario</option>
                                <option value="weekly" selected>Semanal</option>
                                <option value="monthly">Mensual</option>
                                <option value="never">Nunca</option>
                            </select>
                            <small style="color: #718096;">Respaldo automático de la base de datos</small>
                        </div>

                        <div class="form-group">
                            <label for="maintenance_mode">Modo Mantenimiento</label>
                            <select id="maintenance_mode" name="maintenance_mode">
                                <option value="0" selected>Desactivado</option>
                                <option value="1">Activado</option>
                            </select>
                            <small style="color: #718096;">Bloquea el acceso a usuarios no admin</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Section -->
            <div class="save-section">
                <button type="submit" class="save-btn" id="saveBtn">
                    💾 Guardar Configuración
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        let isLoading = false;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            initializeColorPickers();
            initializeImageUploads();
            initializeFormHandlers();
            initializeGoogleTranslate();
        });

        // Configurar color pickers
        function initializeColorPickers() {
            const primaryColor = document.getElementById('primary_color');
            const secondaryColor = document.getElementById('secondary_color');

            primaryColor.addEventListener('change', function() {
                this.nextElementSibling.value = this.value;
                updatePreview();
            });

            secondaryColor.addEventListener('change', function() {
                this.nextElementSibling.value = this.value;
                updatePreview();
            });

            // Actualizar preview cuando cambie el nombre
            document.getElementById('company_name').addEventListener('input', updatePreview);
        }

        // Actualizar vista previa
        function updatePreview() {
            const primary = document.getElementById('primary_color').value;
            const secondary = document.getElementById('secondary_color').value;
            const companyName = document.getElementById('company_name').value;

            document.getElementById('headerPreview').style.background = 
                `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`;
            document.getElementById('companyPreview').textContent = companyName || 'Travel Agency';
        }

        // Configurar subida de imágenes
        function initializeImageUploads() {
            setupImageUpload('logoInput', 'logo_url', 'logoPreview');
            setupImageUpload('backgroundInput', 'background_image', 'backgroundPreview');
        }

        function setupImageUpload(inputId, hiddenId, previewId) {
            const input = document.getElementById(inputId);
            const hiddenField = document.getElementById(hiddenId);
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validar archivo
                    if (file.size > 5 * 1024 * 1024) {
                        showMessage('El archivo es demasiado grande (máximo 5MB)', 'error');
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        showMessage('Solo se permiten archivos de imagen', 'error');
                        return;
                    }

                    // Subir archivo
                    uploadImage(file, hiddenId, previewId);
                }
            });

            // Drag and drop
            const uploadDiv = input.parentElement;
            
            uploadDiv.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadDiv.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadDiv.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }

        // Subir imagen al servidor
        async function uploadImage(file, hiddenFieldId, previewId) {
            try {
                const formData = new FormData();
                formData.append('action', 'upload_config_image');
                formData.append('image', file);

                const response = await fetch(`${APP_URL}/admin/api`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error al subir imagen');
                }

                // Actualizar campo oculto
                document.getElementById(hiddenFieldId).value = data.url;

                // Mostrar preview
                let preview = document.getElementById(previewId);
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = previewId;
                    preview.className = 'image-preview';
                    document.getElementById(hiddenFieldId).parentElement.appendChild(preview);
                }
                preview.src = data.url;

                showMessage('Imagen subida correctamente', 'success');

            } catch (error) {
                console.error('Error al subir imagen:', error);
                showMessage(`Error al subir imagen: ${error.message}`, 'error');
            }
        }

        // Configurar manejadores de formulario
        function initializeFormHandlers() {
            document.getElementById('configForm').addEventListener('submit', saveConfiguration);
        }

        // Guardar configuración
        async function saveConfiguration(e) {
            e.preventDefault();

            if (isLoading) return;

            try {
                isLoading = true;
                const saveBtn = document.getElementById('saveBtn');
                const spinner = document.getElementById('loadingSpinner');
                
                saveBtn.disabled = true;
                spinner.style.display = 'inline-block';

                const formData = new FormData(e.target);
                formData.append('action', 'save_config');

                const response = await fetch(`${APP_URL}/admin/api`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error al guardar configuración');
                }

                showMessage('Configuración guardada correctamente', 'success');

                // Actualizar el título de la página si cambió el nombre
                const newTitle = document.getElementById('company_name').value;
                document.title = `Configuración - ${newTitle}`;

            } catch (error) {
                console.error('Error al guardar configuración:', error);
                showMessage(`Error: ${error.message}`, 'error');
            } finally {
                isLoading = false;
                document.getElementById('saveBtn').disabled = false;
                document.getElementById('loadingSpinner').style.display = 'none';
            }
        }

        // Toggle configuración avanzada
        function toggleAdvanced() {
            const content = document.getElementById('advancedContent');
            const icon = document.getElementById('advancedIcon');
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                icon.textContent = '▼';
            } else {
                content.classList.add('show');
                icon.textContent = '▲';
            }
        }

        // Mostrar mensajes
        function showMessage(message, type) {
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');
            
            // Ocultar ambos mensajes
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';
            
            // Mostrar el mensaje correspondiente
            if (type === 'success') {
                successMsg.textContent = message;
                successMsg.style.display = 'block';
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 5000);
            } else {
                errorMsg.textContent = message;
                errorMsg.style.display = 'block';
                setTimeout(() => {
                    errorMsg.style.display = 'none';
                }, 7000);
            }
        }

        // Google Translate
        function initializeGoogleTranslate() {
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'es',
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
                const saved = sessionStorage.getItem('language') || localStorage.getItem('preferredLanguage');
                if (saved && saved !== 'es') {
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
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>