<?php
// ====================================================================
// ARCHIVO: pages/programa.php - VERSIÓN COMPLETA Y FUNCIONAL
// ====================================================================

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/app.php';

App::init();
App::requireLogin();

// Obtener el nombre de la empresa desde la configuración
$company_name = defined('APP_NAME') ? APP_NAME : 'Travel Agency';
try {
    require_once dirname(__DIR__) . '/config/config_functions.php';
    ConfigManager::init();
    $company_name = ConfigManager::getCompanyName();
} catch(Exception $e) {
    // Usar valor por defecto si hay error
}

// Verificar si es edición o creación nueva
$programa_id = $_GET['id'] ?? null;
$is_editing = false;
$programa_data = null;
$personalizacion_data = null;

if ($programa_id) {
    try {
        $db = Database::getInstance();
        
        // Obtener datos del programa
        $programa_data = $db->fetch(
            "SELECT * FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
            [$programa_id, $_SESSION['user_id']]
        );
        
        if ($programa_data) {
            $is_editing = true;
            
            // Obtener personalización
            $personalizacion_data = $db->fetch(
                "SELECT * FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$programa_id]
            );
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al cargar el programa: ' . $e->getMessage();
        App::redirect('/itinerarios');
    }
}

// Configurar valores por defecto
$default_values = [
    'traveler_name' => $programa_data['nombre_viajero'] ?? '',
    'traveler_lastname' => $programa_data['apellido_viajero'] ?? '',
    'destination' => $programa_data['destino'] ?? '',
    'arrival_date' => $programa_data['fecha_llegada'] ?? '',
    'departure_date' => $programa_data['fecha_salida'] ?? '',
    'passengers' => $programa_data['numero_pasajeros'] ?? 1,
    'accompaniment' => $programa_data['acompanamiento'] ?? 'sin-acompanamiento',
    'program_title' => $personalizacion_data['titulo_programa'] ?? '',
    'language' => $personalizacion_data['idioma_predeterminado'] ?? 'es',
    'request_id' => $programa_data['id_solicitud'] ?? '',
    'cover_image' => $personalizacion_data['foto_portada'] ?? ''
];

$page_title = $is_editing ? 'Editar Programa' : 'Nuevo Programa';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $company_name ?></title>
    
    <!-- CSS Framework y estilos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .top-nav {
            background-color: #2d5a4a;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .top-nav .logo {
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            border-bottom: 2px solid white;
            padding-bottom: 2px;
        }
        
        .top-nav .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .top-nav .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            border-bottom: 1px solid transparent;
            padding-bottom: 1px;
        }
        
        .top-nav .nav-links a:hover {
            border-bottom-color: white;
        }
        
        .top-nav .user-avatar {
            width: 32px;
            height: 32px;
            background-color: #4a7c59;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .tab-navigation {
            background-color: white;
            margin-top: 60px;
            padding: 0;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 60px;
            z-index: 999;
        }
        
        .tab-nav {
            display: flex;
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .tab-item {
            padding: 16px 24px;
            border-bottom: 3px solid transparent;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab-item.active {
            color: #2d5a4a;
            border-bottom-color: #2d5a4a;
        }
        
        .tab-item:hover:not(.active) {
            color: #2d5a4a;
            background-color: #f8f9fa;
        }
        
        /* Container principal - MÁS ANCHO */
        .main-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 20px;
            display: flex;
            gap: 40px;
        }
        
        .form-section {
            flex: 1;
            max-width: 1200px;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 32px;
            overflow: hidden;
        }
        
        .section-header {
            padding: 32px 40px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-body {
            padding: 40px;
        }
        
        .form-row {
            display: flex;
            gap: 32px;
            margin-bottom: 32px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 28px;
        }
        
        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 1px solid #d0d7de;
            border-radius: 8px;
            font-size: 16px;
            background-color: white;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-height: 56px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2d5a4a;
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }
        
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #f6f8fa;
            color: #656d76;
        }
        
        .upload-area {
            border: 2px dashed #d0d7de;
            border-radius: 8px;
            padding: 60px;
            text-align: center;
            background-color: #f6f8fa;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .upload-area:hover {
            border-color: #2d5a4a;
            background-color: #f0f4f2;
        }
        
        .upload-area.has-image {
            padding: 0;
            border: 1px solid #d0d7de;
            background: none;
            min-height: 300px;
        }
        
        .upload-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 6px;
            object-fit: cover;
        }
        
        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: 6px;
        }
        
        .upload-area:hover .upload-overlay {
            opacity: 1;
        }
        
        .upload-icon {
            font-size: 36px;
            color: #8b949e;
            margin-bottom: 16px;
        }
        
        .upload-text {
            font-size: 18px;
            color: #656d76;
            margin: 8px 0;
        }
        
        .required-asterisk {
            color: #d73a49;
            margin-left: 4px;
        }
        
        .sidebar {
            width: 380px;
            flex-shrink: 0;
        }
        
        .action-buttons {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px;
            position: sticky;
            top: 140px;
        }
        
        .btn-primary {
            background-color: #2d5a4a;
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 16px;
            min-height: 56px;
        }
        
        .btn-primary:hover:not(:disabled) {
            background-color: #1f3e32;
        }
        
        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background-color: white;
            color: #656d76;
            border: 1px solid #d0d7de;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 56px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            line-height: 1.2;
        }
        
        .btn-secondary:hover {
            background-color: #f6f8fa;
            border-color: #8b949e;
            color: #656d76;
            text-decoration: none;
        }
        
        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 20px 24px;
            border-radius: 8px;
            z-index: 9999;
            max-width: 450px;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 500;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #d4f6d4;
            color: #1a7f37;
            border: 1px solid #a4daa4;
        }
        
        .notification.error {
            background: #ffedef;
            color: #d1242f;
            border: 1px solid #f5c2c7;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2d5a4a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .id-field {
            background-color: #f6f8fa !important;
            color: #656d76 !important;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 15px;
        }
        
        .expand-icon {
            transition: transform 0.3s ease;
            font-size: 18px;
        }
        
        .section-card.collapsed .expand-icon {
            transform: rotate(-90deg);
        }
        
        .language-select {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTQiIHZpZXdCb3g9IjAgMCAyMCAxNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjE0IiByeD0iMiIgZmlsbD0iI0ZGRkZGRiIvPgo8cmVjdCB3aWR0aD0iMjAiIGhlaWdodD0iNC42NjY2NyIgcng9IjIiIGZpbGw9IiNEOTI2MjYiLz4KPHJlY3QgeT0iOS4zMzMzNCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjQuNjY2NjciIGZpbGw9IiNEOTI2MjYiLz4KPC9zdmc+');
            background-repeat: no-repeat;
            background-position: 16px center;
            background-size: 20px 14px;
            padding-left: 48px;
        }
        
        .char-counter {
            font-size: 14px;
            color: #8b949e;
            text-align: right;
            margin-top: 8px;
        }
        
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 20px 10px;
                gap: 20px;
            }
            
            .sidebar {
                width: 100%;
                order: -1;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .tab-nav {
                padding: 0 10px;
                overflow-x: auto;
            }
            
            .tab-item {
                padding: 12px 16px;
                white-space: nowrap;
            }
            
            .section-header {
                padding: 20px;
            }
            
            .section-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <a href="<?= APP_URL ?>/dashboard" class="logo">Trip Planner</a>
        <div class="nav-links">
            <a href="#">Compartir una idea</a>
            <a href="#">Centro de ayuda</a>
            <div class="user-avatar">
                <?= strtoupper(substr($default_values['traveler_name'] ?: 'U', 0, 1)) ?>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <div class="tab-nav">
            <a href="#" class="tab-item active">Mi programa</a>
            <a href="#" class="tab-item">Día a día</a>
            <a href="#" class="tab-item">Precio</a>
            <a href="<?= APP_URL ?>/biblioteca" class="tab-item">
                <i class="fas fa-book"></i> Biblioteca
            </a>
            <a href="#" class="tab-item">
                <i class="fas fa-eye"></i> Vista previa
            </a>
            <a href="#" class="tab-item">
                <i class="fas fa-share"></i> Compartirlo con el viajero
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Form Section -->
        <div class="form-section">
            <form id="programa-form" method="POST" enctype="multipart/form-data" novalidate>
                
                <!-- Campos ocultos -->
                <?php if ($is_editing): ?>
                    <input type="hidden" id="programa-id-hidden" name="programa_id" value="<?= $programa_id ?>">
                <?php endif; ?>
                
                <!-- Sección: Solicitud del viajero -->
                <div class="section-card">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-title">
                            <i class="fas fa-ellipsis-h"></i>
                            Solicitud del viajero
                        </div>
                        <i class="fas fa-chevron-up expand-icon"></i>
                    </div>
                    <div class="section-body">
                        <div class="form-group">
                            <label class="form-label">ID de la solicitud</label>
                            <input 
                                type="text" 
                                id="request-id" 
                                name="request_id"
                                class="form-control id-field" 
                                value="<?= htmlspecialchars($default_values['request_id']) ?>"
                                readonly
                                placeholder="Se generará automáticamente"
                            >
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="traveler-name" class="form-label">
                                    Nombre del viajero<span class="required-asterisk">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="traveler-name" 
                                    name="traveler_name"
                                    class="form-control" 
                                    value="<?= htmlspecialchars($default_values['traveler_name']) ?>"
                                    required
                                    autocomplete="given-name"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="traveler-lastname" class="form-label">
                                    Apellido del viajero<span class="required-asterisk">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="traveler-lastname" 
                                    name="traveler_lastname"
                                    class="form-control" 
                                    value="<?= htmlspecialchars($default_values['traveler_lastname']) ?>"
                                    required
                                    autocomplete="family-name"
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="destination" class="form-label">
                                Destino<span class="required-asterisk">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="destination" 
                                name="destination"
                                class="form-control" 
                                value="<?= htmlspecialchars($default_values['destination']) ?>"
                                placeholder="Ej: Thailand"
                                required
                            >
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="arrival-date" class="form-label">
                                    Fecha de llegada<span class="required-asterisk">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="arrival-date" 
                                    name="arrival_date"
                                    class="form-control" 
                                    value="<?= $default_values['arrival_date'] ?>"
                                    min="<?= date('Y-m-d') ?>"
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="departure-date" class="form-label">
                                    Fecha de salida<span class="required-asterisk">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="departure-date" 
                                    name="departure_date"
                                    class="form-control" 
                                    value="<?= $default_values['departure_date'] ?>"
                                    min="<?= date('Y-m-d') ?>"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passengers" class="form-label">
                                    Número de viajeros<span class="required-asterisk">*</span>
                                </label>
                                <select 
                                    id="passengers" 
                                    name="passengers"
                                    class="form-control" 
                                    required
                                >
                                    <?php for ($i = 1; $i <= 20; $i++): ?>
                                        <option value="<?= $i ?>" <?= $default_values['passengers'] == $i ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="accompaniment" class="form-label">
                                    Acompañamiento solicitado
                                </label>
                                <select 
                                    id="accompaniment" 
                                    name="accompaniment"
                                    class="form-control"
                                >
                                    <option value="sin-acompanamiento" <?= $default_values['accompaniment'] == 'sin-acompanamiento' ? 'selected' : '' ?>>
                                        Sin acompañamiento
                                    </option>
                                    <option value="guide" <?= $default_values['accompaniment'] == 'guide' ? 'selected' : '' ?>>
                                        guide
                                    </option>
                                    <option value="pareja" <?= $default_values['accompaniment'] == 'pareja' ? 'selected' : '' ?>>
                                        En pareja
                                    </option>
                                    <option value="familia" <?= $default_values['accompaniment'] == 'familia' ? 'selected' : '' ?>>
                                        Familia
                                    </option>
                                    <option value="amigos" <?= $default_values['accompaniment'] == 'amigos' ? 'selected' : '' ?>>
                                        Grupo de amigos
                                    </option>
                                    <option value="negocios" <?= $default_values['accompaniment'] == 'negocios' ? 'selected' : '' ?>>
                                        Viaje de negocios
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Personalización -->
                <div class="section-card">
                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-title">
                            <i class="fas fa-ellipsis-h"></i>
                            Personalización
                        </div>
                        <i class="fas fa-chevron-up expand-icon"></i>
                    </div>
                    <div class="section-body">
                        <div class="form-group">
                            <label for="program-title" class="form-label">
                                Título del programa
                            </label>
                            <input 
                                type="text" 
                                id="program-title" 
                                name="program_title"
                                class="form-control" 
                                value="<?= htmlspecialchars($default_values['program_title']) ?>"
                                placeholder="Ejemplo: Descubrir Tailandia en familia durante 15 días"
                                maxlength="200"
                                oninput="updateCharCount(this, 'title-counter', 200)"
                            >
                            <div id="title-counter" class="char-counter">0/200</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="agent-traveler" class="form-label">
                                Apellido del viajero
                            </label>
                            <input 
                                type="text" 
                                id="agent-traveler" 
                                name="agent_traveler"
                                class="form-control" 
                                value="<?= htmlspecialchars($default_values['traveler_lastname']) ?>"
                                readonly
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="budget-language" class="form-label">
                                Idioma del presupuesto
                            </label>
                            <select 
                                id="budget-language" 
                                name="budget_language"
                                class="form-control language-select"
                            >
                                <option value="es" <?= $default_values['language'] == 'es' ? 'selected' : '' ?>>
                                    Español
                                </option>
                                <option value="en" <?= $default_values['language'] == 'en' ? 'selected' : '' ?>>
                                    English
                                </option>
                                <option value="fr" <?= $default_values['language'] == 'fr' ? 'selected' : '' ?>>
                                    Français
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="departure-date-display" class="form-label">
                                Fecha de llegada
                            </label>
                            <input 
                                type="text" 
                                id="departure-date-display" 
                                name="departure_date_display"
                                class="form-control" 
                                value="<?= $default_values['arrival_date'] ? date('d M Y', strtotime($default_values['arrival_date'])) : '' ?>"
                                readonly
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Foto de portada</label>
                            <div 
                                id="cover-upload" 
                                class="upload-area <?= !empty($default_values['cover_image']) ? 'has-image' : '' ?>"
                                onclick="document.getElementById('cover-input').click()"
                            >
                                <?php if (!empty($default_values['cover_image'])): ?>
                                    <img 
                                        id="cover-preview" 
                                        src="<?= htmlspecialchars($default_values['cover_image']) ?>" 
                                        alt="Portada"
                                        class="upload-preview"
                                    >
                                    <div class="upload-overlay">
                                        <div>
                                            <i class="fas fa-edit fa-2x"></i>
                                            <p class="mt-2 mb-0">Cambiar imagen</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <i class="fas fa-camera upload-icon"></i>
                                    <div class="upload-text">Arrastra y suelta una foto aquí</div>
                                    <div class="upload-text" style="font-size: 12px; color: #8b949e;">
                                        o haz clic para seleccionar
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <input 
                                type="file" 
                                id="cover-input" 
                                name="cover_image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                style="display: none;"
                            >
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="action-buttons">
                <button 
                    type="submit" 
                    id="save-btn" 
                    class="btn-primary"
                    onclick="guardarPrograma()"
                >
                    <i class="fas fa-save"></i>
                    <?= $is_editing ? 'Actualizar Programa' : 'Guardar Programa' ?>
                </button>
                
                <a href="<?= APP_URL ?>/itinerarios" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Mis Programas
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // ====================================================================
        // JAVASCRIPT COMPLETAMENTE FUNCIONAL PARA PROGRAMA.PHP
        // ====================================================================
        
        console.log('🚀 Programa.php script iniciando...');
        
        // Variables globales
        let isEditing = <?= $is_editing ? 'true' : 'false' ?>;
        let originalData = {};
        
        // Inicialización cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM cargado, inicializando componentes...');
            
            initializeForm();
            setupImageUpload();
            setupDateValidation();
            setupCharCounters();
            
            // Guardar datos originales para detectar cambios
            saveOriginalData();
            
            console.log('✅ Todos los componentes inicializados');
            console.log('✅ Modo de edición:', isEditing);
        });

        // ============================================================
        // FUNCIONES DE INICIALIZACIÓN
        // ============================================================
        
        function initializeForm() {
            const form = document.getElementById('programa-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('📝 Formulario enviado via event listener');
                    guardarPrograma();
                });
                
                console.log('✅ Formulario configurado');
            } else {
                console.error('❌ Formulario no encontrado');
            }
        }
        
        function setupImageUpload() {
            const input = document.getElementById('cover-input');
            const uploadArea = document.getElementById('cover-upload');
            
            if (input && uploadArea) {
                // Cambio de archivo
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        console.log('📷 Archivo seleccionado:', file.name, 'Size:', file.size);
                        
                        // Validar archivo
                        if (!validateImageFile(file)) {
                            input.value = ''; // Limpiar input si no es válido
                            return;
                        }
                        
                        // Mostrar preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            showImagePreview(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // Drag & Drop
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadArea.style.borderColor = '#2d5a4a';
                    uploadArea.style.backgroundColor = '#f0f4f2';
                });
                
                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    uploadArea.style.borderColor = '#d0d7de';
                    uploadArea.style.backgroundColor = '#f6f8fa';
                });
                
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadArea.style.borderColor = '#d0d7de';
                    uploadArea.style.backgroundColor = '#f6f8fa';
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        input.files = files;
                        input.dispatchEvent(new Event('change'));
                    }
                });
                
                console.log('✅ Upload de imagen configurado');
            }
        }
        
        function setupDateValidation() {
            const arrivalDate = document.getElementById('arrival-date');
            const departureDate = document.getElementById('departure-date');
            const displayDate = document.getElementById('departure-date-display');
            
            if (arrivalDate && departureDate) {
                arrivalDate.addEventListener('change', function() {
                    if (arrivalDate.value) {
                        departureDate.min = arrivalDate.value;
                        
                        // Actualizar campo de visualización
                        if (displayDate) {
                            const date = new Date(arrivalDate.value);
                            displayDate.value = date.toLocaleDateString('es-ES', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric'
                            });
                        }
                        
                        // Actualizar apellido del viajero en personalización
                        const travelerLastname = document.getElementById('traveler-lastname');
                        const agentTraveler = document.getElementById('agent-traveler');
                        if (travelerLastname && agentTraveler) {
                            agentTraveler.value = travelerLastname.value;
                        }
                        
                        // Si la fecha de salida es anterior, ajustarla
                        if (departureDate.value && departureDate.value < arrivalDate.value) {
                            departureDate.value = arrivalDate.value;
                        }
                    }
                });
                
                // Sincronizar apellido del viajero
                const travelerLastname = document.getElementById('traveler-lastname');
                const agentTraveler = document.getElementById('agent-traveler');
                if (travelerLastname && agentTraveler) {
                    travelerLastname.addEventListener('input', function() {
                        agentTraveler.value = travelerLastname.value;
                    });
                }
                
                console.log('✅ Validación de fechas configurada');
            }
        }
        
        function setupCharCounters() {
            const titleInput = document.getElementById('program-title');
            if (titleInput) {
                // Actualizar contador inicial
                updateCharCount(titleInput, 'title-counter', 200);
                console.log('✅ Contadores de caracteres configurados');
            }
        }
        
        function saveOriginalData() {
            const form = document.getElementById('programa-form');
            if (form) {
                const formData = new FormData(form);
                originalData = {};
                for (let [key, value] of formData.entries()) {
                    originalData[key] = value;
                }
                console.log('💾 Datos originales guardados:', originalData);
            }
        }

        // ============================================================
        // FUNCIONES DE INTERFAZ
        // ============================================================
        
        function toggleSection(header) {
            const card = header.parentElement;
            const body = card.querySelector('.section-body');
            const icon = header.querySelector('.expand-icon');
            
            if (card.classList.contains('collapsed')) {
                card.classList.remove('collapsed');
                body.style.display = 'block';
                icon.style.transform = 'rotate(0deg)';
            } else {
                card.classList.add('collapsed');
                body.style.display = 'none';
                icon.style.transform = 'rotate(-90deg)';
            }
        }
        
        function updateCharCount(input, counterId, maxLength) {
            const counter = document.getElementById(counterId);
            if (counter) {
                const currentLength = input.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.style.color = '#d73a49';
                } else if (currentLength > maxLength * 0.7) {
                    counter.style.color = '#fb8500';
                } else {
                    counter.style.color = '#8b949e';
                }
            }
        }
        
        function showImagePreview(src) {
            const uploadArea = document.getElementById('cover-upload');
            
            uploadArea.innerHTML = `
                <img id="cover-preview" src="${src}" alt="Portada" class="upload-preview">
                <div class="upload-overlay">
                    <div>
                        <i class="fas fa-edit fa-2x"></i>
                        <p class="mt-2 mb-0">Cambiar imagen</p>
                    </div>
                </div>
            `;
            
            uploadArea.classList.add('has-image');
            console.log('✅ Preview de imagen mostrado');
        }
        
        function showNotification(message, type = 'info') {
            // Eliminar notificaciones anteriores
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span>${message}</span>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" style="
                        background: none; border: none; color: inherit; font-size: 18px; 
                        cursor: pointer; padding: 0; margin-left: 10px;
                    ">×</button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
        
        function setLoadingState(loading) {
            const overlay = document.getElementById('loading-overlay');
            const saveBtn = document.getElementById('save-btn');
            
            if (loading) {
                overlay.classList.add('show');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            } else {
                overlay.classList.remove('show');
                saveBtn.disabled = false;
                saveBtn.innerHTML = isEditing ? 
                    '<i class="fas fa-save"></i> Actualizar Programa' : 
                    '<i class="fas fa-save"></i> Guardar Programa';
            }
        }

        // ============================================================
        // FUNCIONES DE VALIDACIÓN
        // ============================================================
        
        function validateForm() {
            console.log('🔍 Validando formulario...');
            
            const requiredFields = [
                { id: 'traveler-name', name: 'Nombre del viajero' },
                { id: 'traveler-lastname', name: 'Apellido del viajero' },
                { id: 'destination', name: 'Destino' },
                { id: 'arrival-date', name: 'Fecha de llegada' },
                { id: 'departure-date', name: 'Fecha de salida' },
                { id: 'passengers', name: 'Número de pasajeros' }
            ];
            
            for (let field of requiredFields) {
                const element = document.getElementById(field.id);
                if (!element || !element.value.trim()) {
                    showNotification(`❌ El campo "${field.name}" es obligatorio`, 'error');
                    element?.focus();
                    return false;
                }
            }
            
            // Validar fechas
            const arrivalDate = new Date(document.getElementById('arrival-date').value);
            const departureDate = new Date(document.getElementById('departure-date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (arrivalDate < today) {
                showNotification('❌ La fecha de llegada no puede ser anterior a hoy', 'error');
                document.getElementById('arrival-date').focus();
                return false;
            }
            
            if (departureDate <= arrivalDate) {
                showNotification('❌ La fecha de salida debe ser posterior a la fecha de llegada', 'error');
                document.getElementById('departure-date').focus();
                return false;
            }
            
            console.log('✅ Formulario válido');
            return true;
        }
        
        function validateImageFile(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                showNotification('❌ Tipo de archivo no permitido. Use JPG, PNG, GIF o WebP', 'error');
                return false;
            }
            
            if (file.size > maxSize) {
                showNotification('❌ El archivo es demasiado grande. Máximo 5MB', 'error');
                return false;
            }
            
            return true;
        }

        // ============================================================
        // FUNCIÓN PRINCIPAL DE GUARDADO - COMPLETAMENTE FUNCIONAL
        // ============================================================
        
        async function guardarPrograma() {
            console.log('=== 🚀 INICIANDO GUARDADO DE PROGRAMA ===');
            
            if (!validateForm()) {
                console.log('❌ Validación del formulario falló');
                return;
            }

            setLoadingState(true);

            try {
                // Crear FormData con todos los campos
                const formData = new FormData();
                formData.append('action', 'save_programa');
                
                // Datos principales del programa
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
                
                // Imagen de portada (si está seleccionada)
                const coverInput = document.getElementById('cover-input');
                if (coverInput && coverInput.files && coverInput.files.length > 0) {
                    formData.append('cover_image', coverInput.files[0]);
                    console.log('📷 Imagen agregada:', coverInput.files[0].name);
                } else {
                    console.log('⚠️ No hay imagen seleccionada');
                }
                
                // ID del programa si es edición
                const programaIdElement = document.getElementById('programa-id-hidden');
                if (programaIdElement && programaIdElement.value) {
                    formData.append('programa_id', programaIdElement.value);
                    console.log('✏️ Editando programa ID:', programaIdElement.value);
                }

                // Debug: Mostrar contenido del FormData
                console.log('📋 Contenido del FormData:');
                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        console.log(`  ${key}: [FILE] ${value.name} (${value.size} bytes)`);
                    } else {
                        console.log(`  ${key}: ${value}`);
                    }
                }

                // Enviar datos a la API
                console.log('🌐 Enviando datos a la API...');
                const response = await fetch('<?= APP_URL ?>/programa/api', {
                    method: 'POST',
                    body: formData
                });

                console.log('📡 Respuesta recibida, status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Error HTTP:', response.status, errorText);
                    throw new Error(`Error HTTP: ${response.status} - ${errorText}`);
                }

                const result = await response.json();
                console.log('📋 Resultado de la API:', result);

                if (result.success) {
                    showNotification('✅ ' + result.message, 'success');
                    
                    // Si es un programa nuevo, configurar para edición
                    if (result.id && !isEditing) {
                        let hiddenInput = document.getElementById('programa-id-hidden');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'programa_id';
                            hiddenInput.id = 'programa-id-hidden';
                            document.getElementById('programa-form').appendChild(hiddenInput);
                        }
                        hiddenInput.value = result.id;
                        
                        // Actualizar URL sin recargar la página
                        if (history.pushState) {
                            const newUrl = `<?= APP_URL ?>/programa?id=${result.id}`;
                            window.history.pushState({path: newUrl}, '', newUrl);
                        }
                        
                        // Mostrar ID de solicitud si se generó
                        if (result.request_id) {
                            document.getElementById('request-id').value = result.request_id;
                            setTimeout(() => {
                                showNotification(`📋 ID de solicitud generado: ${result.request_id}`, 'success');
                            }, 1500);
                        }
                        
                        isEditing = true;
                        
                        // Actualizar título de la página
                        document.title = 'Editar Programa - <?= $company_name ?>';
                    }
                    
                    // Actualizar datos originales
                    saveOriginalData();
                    
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

        // ============================================================
        // FUNCIONES DE DEBUG Y UTILIDADES
        // ============================================================
        
        function debugFormState() {
            console.log('=== 🔍 DEBUG DEL FORMULARIO ===');
            
            const form = document.getElementById('programa-form');
            console.log('Formulario:', form);
            console.log('Enctype:', form?.enctype);
            console.log('Method:', form?.method);
            
            const inputs = form?.querySelectorAll('input, select, textarea');
            console.log('Campos encontrados:', inputs?.length);
            
            inputs?.forEach(input => {
                console.log(`${input.name || input.id}: "${input.value}"`);
            });
            
            const coverInput = document.getElementById('cover-input');
            console.log('Archivo seleccionado:', coverInput?.files?.[0]?.name || 'ninguno');
            
            console.log('Estado de edición:', isEditing);
            console.log('Datos originales:', originalData);
            
            console.log('=== FIN DEBUG ===');
        }
        
        function hasUnsavedChanges() {
            const form = document.getElementById('programa-form');
            if (!form) return false;
            
            const currentData = new FormData(form);
            for (let [key, value] of currentData.entries()) {
                if (originalData[key] !== value) {
                    return true;
                }
            }
            return false;
        }
        
        // Advertir sobre cambios no guardados al salir
        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
        
        // Hacer funciones disponibles globalmente para debug
        window.debugFormState = debugFormState;
        window.guardarPrograma = guardarPrograma;
        window.toggleSection = toggleSection;
        window.updateCharCount = updateCharCount;
        
        console.log('✅ Script de programa.php cargado completamente');
        console.log('💡 Funciones de debug disponibles:');
        console.log('   - debugFormState() - Inspeccionar estado del formulario');
        console.log('   - guardarPrograma() - Forzar guardado manual');
        
    </script>
</body>
</html>