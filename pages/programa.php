<?php
// =====================================
// ARCHIVO: pages/mi_programa.php - ITINERARIO COMPLETO
// =====================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::requireLogin();
ConfigManager::init();

$user = App::getUser();
$config = ConfigManager::get();
$userColors = ConfigManager::getColorsForRole($user['role']);
$companyName = ConfigManager::getCompanyName();
?>
<!DOCTYPE html>
<html lang="<?= $config['default_language'] ?? 'es' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Itinerario - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: <?= $userColors['primary'] ?>;
            --secondary-color: <?= $userColors['secondary'] ?>;
            --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            
            /* Convertir hex a RGB para transparencias */
            --primary-rgb: <?php 
                $hex = $userColors['primary'];
                $hex = ltrim($hex, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                echo "$r, $g, $b";
            ?>;
        }

        body {
            background-color: #f8fafc;
            color: #2d3748;
            line-height: 1.6;
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
            position: sticky;
            top: 0;
            z-index: 100;
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
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .header-title {
            font-size: 24px;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Layout principal */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Navegación por pestañas */
        .tab-navigation {
            display: flex;
            background: white;
            border-radius: 12px;
            padding: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            background: none;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .tab-btn.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.3);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        /* Contenido de pestañas */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Estilos comunes para tarjetas */
        .card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary-color);
        }

        /* Formularios */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Botones */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.4);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Upload de imagen */
        .image-upload {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafbfc;
        }

        .image-upload:hover {
            border-color: var(--primary-color);
            background: rgba(var(--primary-rgb), 0.05);
        }

        .image-upload.has-image {
            padding: 0;
            border: none;
            background: none;
        }

        .upload-placeholder {
            color: #64748b;
        }

        .upload-placeholder i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .uploaded-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 12px;
        }

        /* Lista de días */
        .days-container {
            margin-top: 24px;
        }

        .day-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .day-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.1);
        }

        .day-header {
            background: #f8fafc;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .day-header.active {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        .day-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .day-number {
            background: var(--primary-gradient);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .day-content {
            padding: 20px;
            display: none;
        }

        .day-content.active {
            display: block;
        }

        /* Servicios dentro del día */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .service-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            background: #fafbfc;
        }

        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .service-type {
            font-weight: 500;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remove-service {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }

        .remove-service:hover {
            background: #fee2e2;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        .close-modal:hover {
            color: #ef4444;
        }

        /* Biblioteca de elementos */
        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .library-item {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .library-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.2);
        }

        .library-item.selected {
            border-color: var(--primary-color);
            background: rgba(var(--primary-rgb), 0.05);
        }

        .library-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .library-item-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .library-item-location {
            color: #64748b;
            font-size: 12px;
        }

        /* Precios */
        .price-section {
            margin-bottom: 30px;
        }

        .price-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .price-row {
            display: grid;
            grid-template-columns: 150px 150px 1fr 150px auto;
            gap: 16px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .price-row:last-child {
            border-bottom: none;
        }

        /* Vista previa */
        .preview-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            max-width: 800px;
            margin: 0 auto;
        }

        .preview-header {
            position: relative;
            height: 300px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .preview-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
        }

        .preview-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .preview-company {
            font-size: 18px;
            opacity: 0.9;
        }

        .preview-content {
            padding: 40px;
        }

        .preview-summary {
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 30px;
        }

        .discover-btn {
            background: var(--primary-gradient);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .discover-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(var(--primary-rgb), 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 20px 15px;
            }

            .tab-navigation {
                flex-direction: column;
                gap: 4px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                padding: 12px 20px;
            }

            .header-title {
                font-size: 18px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .price-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-content.active {
            animation: fadeIn 0.5s ease;
        }

        /* Checkbox personalizado */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0;
        }

        .custom-checkbox {
            position: relative;
            width: 20px;
            height: 20px;
        }

        .custom-checkbox input {
            opacity: 0;
            position: absolute;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="<?= APP_URL ?>/itinerarios" class="back-btn">
                <i class="fas fa-arrow-left"></i> Ver Itinerarios
            </a>
            <h1 class="header-title">Mi Itinerario</h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($user['name']) ?></span>
            </div>
        </div>
    </div>

    <div class="main-container">
        <!-- Navegación por pestañas -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('mi-programa')">
                <i class="fas fa-user-edit"></i> Mi Programa
            </button>
            <button class="tab-btn" onclick="showTab('dia-a-dia')">
                <i class="fas fa-calendar-day"></i> Día a Día
            </button>
            <button class="tab-btn" onclick="showTab('precio')">
                <i class="fas fa-dollar-sign"></i> Precio
            </button>
            <button class="tab-btn" onclick="showTab('vista-previa')">
                <i class="fas fa-eye"></i> Vista Previa
            </button>
        </div>

        <!-- TAB: MI PROGRAMA -->
        <div id="mi-programa" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información General
                    </h2>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">ID de Solicitud</label>
                        <input type="text" class="form-input" id="request-id" value="REQ-<?= date('Y') ?>-<?= rand(1000, 9999) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Viajero</label>
                        <input type="text" class="form-input" id="traveler-name" placeholder="Ingrese el nombre">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido del Viajero</label>
                        <input type="text" class="form-input" id="traveler-lastname" placeholder="Ingrese el apellido">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destino</label>
                        <input type="text" class="form-input" id="destination" placeholder="Ciudad o país de destino">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Llegada</label>
                        <input type="date" class="form-input" id="arrival-date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Salida</label>
                        <input type="date" class="form-input" id="departure-date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de Pasajeros</label>
                        <input type="number" class="form-input" id="passengers" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Acompañamiento Solicitado</label>
                        <select class="form-select" id="accompaniment">
                            <option value="sin-acompanamiento">Sin acompañamiento</option>
                            <option value="guia-local">Guía local</option>
                            <option value="guia-especializado">Guía especializado</option>
                            <option value="acompanamiento-completo">Acompañamiento completo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-palette"></i>
                        Personalización
                    </h2>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Título del Programa</label>
                        <input type="text" class="form-input" id="program-title" placeholder="Ej: Escapada Romántica a París">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Idioma del Presupuesto</label>
                        <select class="form-select" id="budget-language">
                            <option value="es">Español</option>
                            <option value="en">English</option>
                            <option value="fr">Français</option>
                            <option value="de">Deutsch</option>
                            <option value="it">Italiano</option>
                            <option value="pt">Português</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Foto de Portada</label>
                        <div class="image-upload" id="cover-upload" onclick="document.getElementById('cover-input').click()">
                            <input type="file" id="cover-input" accept="image/*" style="display: none;" onchange="handleImageUpload(this, 'cover-upload')">
                            <div class="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Haga clic para subir una imagen de portada</p>
                                <small>JPG, PNG o WebP (máx. 5MB)</small>
                            </div>
                        </div>
                        <!-- Controles de imagen cuando hay una imagen cargada -->
                        <div id="image-controls" style="display: none; margin-top: 15px; text-align: center; gap: 10px;">
                            <button type="button" class="btn btn-secondary" onclick="changeImage()" style="padding: 8px 16px;">
                                <i class="fas fa-edit"></i> Cambiar Imagen
                            </button>
                            <button type="button" class="btn btn-danger" onclick="removeImage()" style="padding: 8px 16px;">
                                <i class="fas fa-trash"></i> Eliminar Imagen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón Guardar Mi Programa -->
            <div style="text-align: right; margin-top: 30px;">
                <button class="btn btn-primary" onclick="saveMiPrograma()" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> Guardar Mi Programa
                </button>
            </div>
        </div>

        <!-- TAB: DÍA A DÍA -->
        <div id="dia-a-dia" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Planificación Día a Día
                    </h2>
                    <button class="btn btn-primary" onclick="openLibraryModal('dias')">
                        <i class="fas fa-plus"></i> Añadir un Día
                    </button>
                </div>
                
                <div class="days-container" id="days-container">
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        <i class="fas fa-calendar-plus" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p>No hay días agregados aún</p>
                        <p style="font-size: 14px; margin-top: 8px;">Haga clic en "Añadir un Día" para comenzar</p>
                    </div>
                </div>
            </div>
            
            <!-- Botón Guardar Día a Día -->
            <div style="text-align: right; margin-top: 30px;">
                <button class="btn btn-primary" onclick="saveDiaADia()" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> Guardar Día a Día
                </button>
            </div>
        </div>

        <!-- TAB: PRECIO -->
        <div id="precio" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Precio del Viaje
                    </h2>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Moneda</label>
                        <select class="form-select" id="currency">
                            <option value="COP">Pesos Colombianos (COP)</option>
                            <option value="USD">Dólares Americanos (USD)</option>
                            <option value="EUR">Euros (EUR)</option>
                            <option value="GBP">Libras Esterlinas (GBP)</option>
                            <option value="CAD">Dólares Canadienses (CAD)</option>
                            <option value="AUD">Dólares Australianos (AUD)</option>
                            <option value="JPY">Yenes Japoneses (JPY)</option>
                            <option value="CHF">Francos Suizos (CHF)</option>
                            <option value="MXN">Pesos Mexicanos (MXN)</option>
                            <option value="BRL">Reales Brasileños (BRL)</option>
                        </select>
                    </div>
                </div>

                <div class="price-section">
                    <div class="price-header">
                        <h3>Precios por Tipo de Pasajero</h3>
                    </div>
                    
                    <div id="price-rows-container">
                        <div class="price-row">
                            <div class="form-group">
                                <select class="form-select passenger-type">
                                    <option value="adulto">Adulto</option>
                                    <option value="adolescente">Adolescente</option>
                                    <option value="nino">Niño</option>
                                    <option value="bebe">Bebé</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-input price-detail" placeholder="Detalle del precio">
                            </div>
                            <div class="form-group">
                                <input type="number" class="form-input price-amount" placeholder="0.00" step="0.01">
                            </div>
                            <button class="btn btn-secondary" onclick="addPriceRow()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Detalle del Precio
                    </h2>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Noches Incluidas</label>
                        <input type="number" class="form-input" id="nights-included" min="0" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">El Precio Incluye</label>
                    <textarea class="form-textarea" id="price-includes" placeholder="Describa qué incluye el precio del viaje..." rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">El Precio NO Incluye</label>
                    <textarea class="form-textarea" id="price-excludes" placeholder="Describa qué NO incluye el precio del viaje..." rows="4"></textarea>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-file-contract"></i>
                        Condiciones Generales
                    </h2>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Términos y Condiciones</label>
                    <textarea class="form-textarea" id="general-conditions" rows="6">
• Las reservas están sujetas a disponibilidad
• Se requiere un depósito del 30% para confirmar la reserva
• El saldo restante debe pagarse 15 días antes del viaje
• Las cancelaciones realizadas con más de 30 días de antelación tendrán una penalización del 10%
• Las cancelaciones realizadas entre 15-30 días tendrán una penalización del 50%
• Las cancelaciones realizadas con menos de 15 días no tendrán reembolso
• Los precios están sujetos a cambios sin previo aviso
• Es responsabilidad del viajero verificar la documentación requerida
                    </textarea>
                </div>

                <div class="checkbox-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" id="mobility-reduced">
                        <span class="checkmark"></span>
                    </label>
                    <label for="mobility-reduced">Este viaje es apto para personas de movilidad reducida</label>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-passport"></i>
                        Pasaportes y Seguros
                    </h2>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Pasaportes y Visados</label>
                    <textarea class="form-textarea" id="passport-info" rows="4">
Es responsabilidad del viajero verificar la validez de su pasaporte y obtener los visados necesarios para el destino. Se recomienda que el pasaporte tenga una validez mínima de 6 meses desde la fecha de regreso. Para información específica sobre requisitos de visado, consulte con el consulado correspondiente.
                    </textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Seguros de Viaje</label>
                    <textarea class="form-textarea" id="insurance-info" rows="4">
Se recomienda encarecidamente contratar un seguro de viaje que cubra gastos médicos, cancelación de viaje, pérdida de equipaje y repatriación. El seguro de viaje no está incluido en el precio del paquete a menos que se especifique lo contrario. Consulte con su agente de viajes sobre las opciones disponibles.
                    </textarea>
                </div>
            </div>
            
            <!-- Botón Guardar Precio -->
            <div style="text-align: right; margin-top: 30px;">
                <button class="btn btn-primary" onclick="savePrecio()" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> Guardar Precio
                </button>
            </div>
        </div>

        <!-- TAB: VISTA PREVIA -->
        <div id="vista-previa" class="tab-content">
            <div class="preview-container">
                <div class="preview-header">
                    <img id="preview-cover-img" class="preview-cover" style="display: none;" alt="Portada">
                    <div class="preview-overlay">
                        <h1 class="preview-title" id="preview-title">Título del Programa</h1>
                        <p class="preview-company"><?= htmlspecialchars($companyName) ?></p>
                    </div>
                </div>
                <div class="preview-content">
                    <div class="preview-summary" id="preview-summary">
                        Un viaje único diseñado especialmente para usted. Descubra experiencias inolvidables y cree recuerdos que durarán toda la vida.
                    </div>
                    <button class="discover-btn" onclick="generateFullProgram()">
                        <i class="fas fa-eye"></i> Descubrir Todo el Programa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar de biblioteca -->
    <div class="modal" id="library-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Seleccionar de Biblioteca</h3>
                <button class="close-modal" onclick="closeLibraryModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modal-content-area">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para agregar servicios -->
    <div class="modal" id="services-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Agregar Servicios al Día</h3>
                <button class="close-modal" onclick="closeServicesModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Servicio</label>
                <select class="form-select" id="service-type" onchange="loadServiceLibrary()">
                    <option value="">Seleccione un tipo</option>
                    <option value="actividad">Actividad</option>
                    <option value="transporte">Transporte</option>
                    <option value="alojamiento">Alojamiento</option>
                </select>
            </div>
            <div id="service-library-content">
                <!-- Se carga dinámicamente según el tipo seleccionado -->
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentDayEdit = null;
        let selectedLibraryItems = [];
        let dayCounter = 0;
        let priceRowCounter = 1;
        let currentCoverImage = null; // Para almacenar la imagen de portada actual

        // Funciones de navegación por pestañas
        function showTab(tabName) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar pestaña seleccionada
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Actualizar vista previa si es necesario
            if (tabName === 'vista-previa') {
                updatePreview();
            }
        }

        // Manejo de imágenes mejorado
        function handleImageUpload(input, containerId) {
            const file = input.files[0];
            if (file) {
                // Validar tamaño del archivo (máx 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                    input.value = '';
                    return;
                }

                // Validar tipo de archivo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipo de archivo no permitido. Solo se permiten JPG, PNG y WebP.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById(containerId);
                    const imageUrl = e.target.result;
                    
                    // Crear elemento de imagen con controles de edición
                    container.innerHTML = `
                        <div style="position: relative;">
                            <img src="${imageUrl}" class="uploaded-image" alt="Imagen subida" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 12px;">
                            <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 5px;">
                                <button type="button" onclick="changeImage()" class="btn" style="background: rgba(0,0,0,0.7); color: white; padding: 5px 8px; border-radius: 4px; border: none; cursor: pointer;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" onclick="removeImage()" class="btn" style="background: rgba(220,38,38,0.8); color: white; padding: 5px 8px; border-radius: 4px; border: none; cursor: pointer;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    container.classList.add('has-image');
                    
                    // Almacenar la imagen actual
                    currentCoverImage = {
                        file: file,
                        dataUrl: imageUrl,
                        name: file.name
                    };
                    
                    // Actualizar vista previa si es la imagen de portada
                    if (containerId === 'cover-upload') {
                        updatePreviewCover(imageUrl);
                    }

                    showNotification('Imagen cargada exitosamente', 'success');
                };
                reader.readAsDataURL(file);
            }
        }

        function changeImage() {
            document.getElementById('cover-input').click();
        }

        function removeImage() {
            if (confirm('¿Está seguro de que desea eliminar esta imagen?')) {
                const container = document.getElementById('cover-upload');
                container.innerHTML = `
                    <input type="file" id="cover-input" accept="image/*" style="display: none;" onchange="handleImageUpload(this, 'cover-upload')">
                    <div class="upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Haga clic para subir una imagen de portada</p>
                        <small>JPG, PNG o WebP (máx. 5MB)</small>
                    </div>
                `;
                container.classList.remove('has-image');
                currentCoverImage = null;
                
                // Limpiar vista previa
                const previewImg = document.getElementById('preview-cover-img');
                previewImg.style.display = 'none';
                previewImg.src = '';
                
                showNotification('Imagen eliminada', 'success');
            }
        }

        // Funciones del modal de biblioteca
        function openLibraryModal(type) {
            const modal = document.getElementById('library-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content-area');
            
            modal.classList.add('active');
            
            switch(type) {
                case 'dias':
                    modalTitle.textContent = 'Seleccionar Día de la Biblioteca';
                    loadDaysLibrary(modalContent);
                    break;
                case 'actividades':
                    modalTitle.textContent = 'Seleccionar Actividad';
                    loadActivitiesLibrary(modalContent);
                    break;
                case 'transporte':
                    modalTitle.textContent = 'Seleccionar Transporte';
                    loadTransportLibrary(modalContent);
                    break;
                case 'alojamiento':
                    modalTitle.textContent = 'Seleccionar Alojamiento';
                    loadAccommodationLibrary(modalContent);
                    break;
            }
        }

        function closeLibraryModal() {
            document.getElementById('library-modal').classList.remove('active');
            selectedLibraryItems = [];
        }

        // Cargar bibliotecas (simulado - en producción vendría del backend)
        function loadDaysLibrary(container) {
            const mockDays = [
                { id: 1, title: 'Día de Llegada a París', description: 'Recepción en el aeropuerto y traslado al hotel', location: 'París, Francia', image: 'https://images.unsplash.com/photo-1502602898536-47ad22581b52?w=300' },
                { id: 2, title: 'Tour por el Louvre', description: 'Visita guiada por el museo más famoso del mundo', location: 'Museo del Louvre', image: 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=300' },
                { id: 3, title: 'Crucero por el Sena', description: 'Paseo en barco con cena incluida', location: 'Río Sena', image: 'https://images.unsplash.com/photo-1502602898536-47ad22581b52?w=300' }
            ];
            
            renderLibraryItems(container, mockDays, 'day');
        }

        function loadActivitiesLibrary(container) {
            const mockActivities = [
                { id: 1, title: 'Tour Gastronómico', location: 'Centro Histórico', image: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=300' },
                { id: 2, title: 'Clase de Cocina Local', location: 'Escuela Culinaria', image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=300' },
                { id: 3, title: 'Senderismo en Montaña', location: 'Parque Nacional', image: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=300' }
            ];
            
            renderLibraryItems(container, mockActivities, 'activity');
        }

        function loadTransportLibrary(container) {
            const mockTransport = [
                { id: 1, title: 'Transfer Aeropuerto VIP', location: 'Aeropuerto Internacional', image: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=300' },
                { id: 2, title: 'Tren de Alta Velocidad', location: 'Estación Central', image: 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=300' },
                { id: 3, title: 'Tour en Bus Panorámico', location: 'Ciudad', image: 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=300' }
            ];
            
            renderLibraryItems(container, mockTransport, 'transport');
        }

        function loadAccommodationLibrary(container) {
            const mockAccommodation = [
                { id: 1, title: 'Hotel Boutique Centro', location: 'Centro Histórico', image: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300' },
                { id: 2, title: 'Resort Todo Incluido', location: 'Costa del Mar', image: 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=300' },
                { id: 3, title: 'Cabaña en las Montañas', location: 'Sierra Nevada', image: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=300' }
            ];
            
            renderLibraryItems(container, mockAccommodation, 'accommodation');
        }

        function renderLibraryItems(container, items, type) {
            container.innerHTML = `
                <div class="library-grid">
                    ${items.map(item => `
                        <div class="library-item" onclick="selectLibraryItem(${item.id}, '${type}', this)">
                            <img src="${item.image}" alt="${item.title}">
                            <div class="library-item-title">${item.title}</div>
                            <div class="library-item-location">${item.location}</div>
                        </div>
                    `).join('')}
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-secondary" onclick="closeLibraryModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="addSelectedItems('${type}')" style="margin-left: 10px;">
                        <i class="fas fa-plus"></i> Agregar Seleccionados
                    </button>
                </div>
            `;
        }

        function selectLibraryItem(id, type, element) {
            element.classList.toggle('selected');
            
            const index = selectedLibraryItems.findIndex(item => item.id === id && item.type === type);
            if (index > -1) {
                selectedLibraryItems.splice(index, 1);
            } else {
                selectedLibraryItems.push({ id, type, element });
            }
        }

        function addSelectedItems(type) {
            if (selectedLibraryItems.length === 0) {
                alert('Por favor seleccione al menos un elemento');
                return;
            }

            if (type === 'day') {
                selectedLibraryItems.forEach(item => {
                    addDayFromLibrary(item);
                });
            } else {
                // Para servicios, agregar al día actual en edición
                if (currentDayEdit) {
                    selectedLibraryItems.forEach(item => {
                        addServiceToDay(currentDayEdit, item);
                    });
                }
            }

            closeLibraryModal();
        }

        // Funciones para manejar días
        function addDayFromLibrary(libraryItem) {
            dayCounter++;
            const daysContainer = document.getElementById('days-container');
            
            // Remover mensaje de "no hay días"
            if (daysContainer.children.length === 1 && daysContainer.children[0].style.textAlign === 'center') {
                daysContainer.innerHTML = '';
            }

            const dayElement = document.createElement('div');
            dayElement.className = 'day-item';
            dayElement.id = `day-${dayCounter}`;
            dayElement.innerHTML = `
                <div class="day-header" onclick="toggleDay(${dayCounter})">
                    <div class="day-title">
                        <div class="day-number">${dayCounter}</div>
                        <span>Día ${dayCounter} - ${libraryItem.element.querySelector('.library-item-title').textContent}</span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button class="btn btn-secondary" onclick="event.stopPropagation(); editDay(${dayCounter})" style="padding: 6px 12px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" onclick="event.stopPropagation(); removeDay(${dayCounter})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                        <i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
                    </div>
                </div>
                <div class="day-content" id="day-content-${dayCounter}">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Título de la Jornada</label>
                            <input type="text" class="form-input" value="${libraryItem.element.querySelector('.library-item-title').textContent}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-input" value="${libraryItem.element.querySelector('.library-item-location').textContent}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-textarea" rows="3">Descripción detallada del día ${dayCounter}</textarea>
                    </div>
                    
                    <div style="margin: 20px 0;">
                        <h4 style="margin-bottom: 12px; color: var(--primary-color);">
                            <i class="fas fa-utensils"></i> Comidas
                        </h4>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <label class="checkbox-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="meal-${dayCounter}" value="desayuno">
                                    <span class="checkmark"></span>
                                </div>
                                Desayuno
                            </label>
                            <label class="checkbox-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="meal-${dayCounter}" value="almuerzo">
                                    <span class="checkmark"></span>
                                </div>
                                Almuerzo
                            </label>
                            <label class="checkbox-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="meal-${dayCounter}" value="cena">
                                    <span class="checkmark"></span>
                                </div>
                                Cena
                            </label>
                            <label class="checkbox-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="meal-${dayCounter}" value="no-incluidas">
                                    <span class="checkmark"></span>
                                </div>
                                Comidas no incluidas
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin: 20px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="color: var(--primary-color);">
                                <i class="fas fa-concierge-bell"></i> Servicios
                            </h4>
                            <button class="btn btn-primary" onclick="openServicesModal(${dayCounter})" style="padding: 6px 12px;">
                                <i class="fas fa-plus"></i> Agregar Servicio
                            </button>
                        </div>
                        <div class="services-grid" id="services-${dayCounter}">
                            <!-- Los servicios se agregarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>
            `;
            
            daysContainer.appendChild(dayElement);
        }

        function toggleDay(dayId) {
            const content = document.getElementById(`day-content-${dayId}`);
            const header = content.previousElementSibling;
            const icon = header.querySelector('.fas.fa-chevron-down');
            
            if (content.classList.contains('active')) {
                content.classList.remove('active');
                header.classList.remove('active');
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.classList.add('active');
                header.classList.add('active');
                icon.style.transform = 'rotate(180deg)';
            }
        }

        function editDay(dayId) {
            currentDayEdit = dayId;
            // Lógica adicional para editar día si es necesario
        }

        function removeDay(dayId) {
            if (confirm('¿Está seguro de que desea eliminar este día?')) {
                document.getElementById(`day-${dayId}`).remove();
                
                // Si no quedan días, mostrar mensaje
                const daysContainer = document.getElementById('days-container');
                if (daysContainer.children.length === 0) {
                    daysContainer.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #64748b;">
                            <i class="fas fa-calendar-plus" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>No hay días agregados aún</p>
                            <p style="font-size: 14px; margin-top: 8px;">Haga clic en "Añadir un Día" para comenzar</p>
                        </div>
                    `;
                }
            }
        }

        // Funciones para servicios
        function openServicesModal(dayId) {
            currentDayEdit = dayId;
            document.getElementById('services-modal').classList.add('active');
        }

        function closeServicesModal() {
            document.getElementById('services-modal').classList.remove('active');
            document.getElementById('service-type').value = '';
            document.getElementById('service-library-content').innerHTML = '';
        }

        function loadServiceLibrary() {
            const serviceType = document.getElementById('service-type').value;
            const container = document.getElementById('service-library-content');
            
            if (!serviceType) {
                container.innerHTML = '';
                return;
            }

            switch(serviceType) {
                case 'actividad':
                    loadActivitiesLibrary(container);
                    break;
                case 'transporte':
                    loadTransportLibrary(container);
                    break;
                case 'alojamiento':
                    loadAccommodationLibrary(container);
                    break;
            }
        }

        function addServiceToDay(dayId, serviceItem) {
            const servicesContainer = document.getElementById(`services-${dayId}`);
            const serviceType = document.getElementById('service-type').value;
            
            const serviceElement = document.createElement('div');
            serviceElement.className = 'service-card';
            serviceElement.innerHTML = `
                <div class="service-header">
                    <div class="service-type">
                        <i class="fas fa-${getServiceIcon(serviceType)}"></i>
                        ${capitalizeFirst(serviceType)}
                    </div>
                    <button class="remove-service" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="font-weight: 500; margin-bottom: 4px;">
                    ${serviceItem.element.querySelector('.library-item-title').textContent}
                </div>
                <div style="color: #64748b; font-size: 12px;">
                    ${serviceItem.element.querySelector('.library-item-location').textContent}
                </div>
            `;
            
            servicesContainer.appendChild(serviceElement);
        }

        function getServiceIcon(serviceType) {
            const icons = {
                'actividad': 'map-marker-alt',
                'transporte': 'car',
                'alojamiento': 'bed'
            };
            return icons[serviceType] || 'concierge-bell';
        }

        function capitalizeFirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Funciones para precios
        function addPriceRow() {
            priceRowCounter++;
            const container = document.getElementById('price-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'price-row';
            newRow.innerHTML = `
                <div class="form-group">
                    <select class="form-select passenger-type">
                        <option value="adulto">Adulto</option>
                        <option value="adolescente">Adolescente</option>
                        <option value="nino">Niño</option>
                        <option value="bebe">Bebé</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-input price-detail" placeholder="Detalle del precio">
                </div>
                <div class="form-group">
                    <input type="number" class="form-input price-amount" placeholder="0.00" step="0.01">
                </div>
                <button class="btn btn-danger" onclick="this.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newRow);
        }

        // Funciones para vista previa
        function updatePreview() {
            const title = document.getElementById('program-title').value || 'Título del Programa';
            const destination = document.getElementById('destination').value;
            const arrivalDate = document.getElementById('arrival-date').value;
            const departureDate = document.getElementById('departure-date').value;
            const passengers = document.getElementById('passengers').value;

            document.getElementById('preview-title').textContent = title;
            
            let summary = `Un viaje único diseñado especialmente para usted.`;
            
            if (destination) {
                summary += ` Descubra las maravillas de ${destination}`;
            }
            
            if (arrivalDate && departureDate) {
                const arrival = new Date(arrivalDate);
                const departure = new Date(departureDate);
                const days = Math.ceil((departure - arrival) / (1000 * 60 * 60 * 24));
                summary += ` durante ${days} ${days === 1 ? 'día' : 'días'} inolvidables`;
            }
            
            if (passengers && passengers > 1) {
                summary += ` para ${passengers} personas`;
            }
            
            summary += '. Cree recuerdos que durarán toda la vida.';
            
            document.getElementById('preview-summary').textContent = summary;
        }

        function updatePreviewCover(imageSrc) {
            const previewImg = document.getElementById('preview-cover-img');
            previewImg.src = imageSrc;
            previewImg.style.display = 'block';
        }

        function generateFullProgram() {
            // Recopilar todos los datos del formulario
            const programData = {
                general: {
                    requestId: document.getElementById('request-id').value,
                    travelerName: document.getElementById('traveler-name').value,
                    travelerLastname: document.getElementById('traveler-lastname').value,
                    destination: document.getElementById('destination').value,
                    arrivalDate: document.getElementById('arrival-date').value,
                    departureDate: document.getElementById('departure-date').value,
                    passengers: document.getElementById('passengers').value,
                    accompaniment: document.getElementById('accompaniment').value
                },
                customization: {
                    programTitle: document.getElementById('program-title').value,
                    budgetLanguage: document.getElementById('budget-language').value
                },
                pricing: {
                    currency: document.getElementById('currency').value,
                    nightsIncluded: document.getElementById('nights-included').value,
                    priceIncludes: document.getElementById('price-includes').value,
                    priceExcludes: document.getElementById('price-excludes').value,
                    generalConditions: document.getElementById('general-conditions').value,
                    mobilityReduced: document.getElementById('mobility-reduced').checked,
                    passportInfo: document.getElementById('passport-info').value,
                    insuranceInfo: document.getElementById('insurance-info').value
                }
            };
            
            // En una implementación real, aquí se enviarían los datos al backend
            console.log('Programa generado:', programData);
            alert('¡Programa generado exitosamente! Se ha guardado la información.');
        }

        // Event listeners para actualizar vista previa en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar vista previa cuando cambien los campos relevantes
            const previewFields = ['program-title', 'destination', 'arrival-date', 'departure-date', 'passengers'];
            previewFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', updatePreview);
                    field.addEventListener('change', updatePreview);
                }
            });

            // Inicializar vista previa
            updatePreview();
        });

        // Funciones de validación
        function validateForm() {
            const requiredFields = [
                'traveler-name',
                'traveler-lastname', 
                'destination',
                'arrival-date',
                'departure-date',
                'passengers'
            ];

            let isValid = true;
            const errors = [];

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    isValid = false;
                    errors.push(`El campo ${field.previousElementSibling.textContent} es obligatorio`);
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });

            // Validar fechas
            const arrivalDate = new Date(document.getElementById('arrival-date').value);
            const departureDate = new Date(document.getElementById('departure-date').value);
            
            if (arrivalDate && departureDate && arrivalDate >= departureDate) {
                isValid = false;
                errors.push('La fecha de salida debe ser posterior a la fecha de llegada');
            }

            if (!isValid) {
                alert('Por favor corrija los siguientes errores:\n\n' + errors.join('\n'));
            }

            return isValid;
        }

        // Funciones para guardar en base de datos

        // Guardar sección Mi Programa
        async function saveMiPrograma() {
            const data = {
                section: 'mi_programa',
                data: {
                    request_id: document.getElementById('request-id').value,
                    traveler_name: document.getElementById('traveler-name').value,
                    traveler_lastname: document.getElementById('traveler-lastname').value,
                    destination: document.getElementById('destination').value,
                    arrival_date: document.getElementById('arrival-date').value,
                    departure_date: document.getElementById('departure-date').value,
                    passengers: document.getElementById('passengers').value,
                    accompaniment: document.getElementById('accompaniment').value,
                    program_title: document.getElementById('program-title').value,
                    budget_language: document.getElementById('budget-language').value,
                    cover_image: currentCoverImage ? {
                        name: currentCoverImage.name,
                        data: currentCoverImage.dataUrl
                    } : null
                }
            };

            try {
                showLoadingButton('saveMiPrograma', true);
                
                const response = await fetch('/programa/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification('Mi Programa guardado exitosamente', 'success');
                } else {
                    throw new Error(result.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al guardar Mi Programa: ' + error.message, 'error');
            } finally {
                showLoadingButton('saveMiPrograma', false);
            }
        }

        // Guardar sección Día a Día
        async function saveDiaADia() {
            const daysData = getDaysData();
            
            const data = {
                section: 'dia_a_dia',
                data: {
                    days: daysData,
                    total_days: daysData.length
                }
            };

            try {
                showLoadingButton('saveDiaADia', true);
                
                const response = await fetch('/programa/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification('Día a Día guardado exitosamente', 'success');
                } else {
                    throw new Error(result.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al guardar Día a Día: ' + error.message, 'error');
            } finally {
                showLoadingButton('saveDiaADia', false);
            }
        }

        // Guardar sección Precio
        async function savePrecio() {
            const pricingData = getPricingData();
            
            const data = {
                section: 'precio',
                data: pricingData
            };

            try {
                showLoadingButton('savePrecio', true);
                
                const response = await fetch('/programa/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification('Precio guardado exitosamente', 'success');
                } else {
                    throw new Error(result.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al guardar Precio: ' + error.message, 'error');
            } finally {
                showLoadingButton('savePrecio', false);
            }
        }

        // Función helper para mostrar estado de carga en botones
        function showLoadingButton(functionName, isLoading) {
            const buttons = {
                'saveMiPrograma': 'Guardar Mi Programa',
                'saveDiaADia': 'Guardar Día a Día', 
                'savePrecio': 'Guardar Precio'
            };

            // Buscar el botón por el onclick
            const button = document.querySelector(`button[onclick="${functionName}()"]`);
            if (button) {
                if (isLoading) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                } else {
                    button.disabled = false;
                    button.innerHTML = `<i class="fas fa-save"></i> ${buttons[functionName]}`;
                }
            }
        }
            if (!validateForm()) {
                return;
            }

            const progressData = {
                timestamp: new Date().toISOString(),
                general: {
                    requestId: document.getElementById('request-id').value,
                    travelerName: document.getElementById('traveler-name').value,
                    travelerLastname: document.getElementById('traveler-lastname').value,
                    destination: document.getElementById('destination').value,
                    arrivalDate: document.getElementById('arrival-date').value,
                    departureDate: document.getElementById('departure-date').value,
                    passengers: document.getElementById('passengers').value,
                    accompaniment: document.getElementById('accompaniment').value
                },
                customization: {
                    programTitle: document.getElementById('program-title').value,
                    budgetLanguage: document.getElementById('budget-language').value
                },
                days: getDaysData(),
                pricing: getPricingData()
            };

            // Guardar en localStorage como respaldo
            localStorage.setItem('programa_draft', JSON.stringify(progressData));
            
            // En producción, enviar al backend
            console.log('Progreso guardado:', progressData);
            
            // Mostrar confirmación
            showNotification('Progreso guardado exitosamente', 'success');
        }

        function getDaysData() {
            const days = [];
            const dayElements = document.querySelectorAll('.day-item');
            
            dayElements.forEach((dayElement, index) => {
                const dayContent = dayElement.querySelector('.day-content');
                const titleInput = dayContent.querySelector('input[type="text"]');
                const locationInput = dayContent.querySelectorAll('input[type="text"]')[1];
                const descriptionTextarea = dayContent.querySelector('textarea');
                const mealCheckboxes = dayContent.querySelectorAll('input[type="checkbox"]');
                const services = dayContent.querySelectorAll('.service-card');

                const dayData = {
                    id: index + 1,
                    title: titleInput ? titleInput.value : '',
                    location: locationInput ? locationInput.value : '',
                    description: descriptionTextarea ? descriptionTextarea.value : '',
                    meals: [],
                    services: []
                };

                // Recopilar comidas seleccionadas
                mealCheckboxes.forEach(checkbox => {
                    if (checkbox.checked && checkbox.name.includes('meal-')) {
                        dayData.meals.push(checkbox.value);
                    }
                });

                // Recopilar servicios
                services.forEach(service => {
                    const serviceType = service.querySelector('.service-type').textContent.trim();
                    const serviceTitle = service.querySelector('div[style*="font-weight: 500"]').textContent;
                    const serviceLocation = service.querySelector('div[style*="color: #64748b"]').textContent;
                    
                    dayData.services.push({
                        type: serviceType.toLowerCase(),
                        title: serviceTitle,
                        location: serviceLocation
                    });
                });

                days.push(dayData);
            });

            return days;
        }

        function getPricingData() {
            const priceRows = document.querySelectorAll('.price-row');
            const prices = [];

            priceRows.forEach(row => {
                const passengerType = row.querySelector('.passenger-type');
                const priceDetail = row.querySelector('.price-detail');
                const priceAmount = row.querySelector('.price-amount');

                if (passengerType && priceDetail && priceAmount && priceAmount.value) {
                    prices.push({
                        passengerType: passengerType.value,
                        detail: priceDetail.value,
                        amount: parseFloat(priceAmount.value)
                    });
                }
            });

            return {
                currency: document.getElementById('currency').value,
                prices: prices,
                nightsIncluded: document.getElementById('nights-included').value,
                priceIncludes: document.getElementById('price-includes').value,
                priceExcludes: document.getElementById('price-excludes').value,
                generalConditions: document.getElementById('general-conditions').value,
                mobilityReduced: document.getElementById('mobility-reduced').checked,
                passportInfo: document.getElementById('passport-info').value,
                insuranceInfo: document.getElementById('insurance-info').value
            };
        }

        // Función para cargar progreso guardado
        function loadProgress() {
            const savedData = localStorage.getItem('programa_draft');
            if (savedData) {
                const data = JSON.parse(savedData);
                
                if (confirm('Se encontró un borrador guardado. ¿Desea cargarlo?')) {
                    // Cargar datos generales
                    if (data.general) {
                        Object.keys(data.general).forEach(key => {
                            const element = document.getElementById(key.replace(/([A-Z])/g, '-$1').toLowerCase());
                            if (element) {
                                element.value = data.general[key];
                            }
                        });
                    }

                    // Cargar personalización
                    if (data.customization) {
                        Object.keys(data.customization).forEach(key => {
                            const element = document.getElementById(key.replace(/([A-Z])/g, '-$1').toLowerCase());
                            if (element) {
                                element.value = data.customization[key];
                            }
                        });
                    }

                    // Cargar datos de precios
                    if (data.pricing) {
                        Object.keys(data.pricing).forEach(key => {
                            const element = document.getElementById(key.replace(/([A-Z])/g, '-$1').toLowerCase());
                            if (element) {
                                if (element.type === 'checkbox') {
                                    element.checked = data.pricing[key];
                                } else {
                                    element.value = data.pricing[key];
                                }
                            }
                        });
                    }

                    showNotification('Borrador cargado exitosamente', 'success');
                }
            }
        }

        // Sistema de notificaciones
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                animation: slideIn 0.3s ease;
                max-width: 300px;
            `;

            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: 'var(--primary-color)'
            };

            notification.style.background = colors[type] || colors.info;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Agregar estilos para animaciones de notificación
        const notificationStyles = document.createElement('style');
        notificationStyles.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(notificationStyles);

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadProgress();
            
            // Guardar progreso automáticamente cada 2 minutos
            setInterval(saveProgress, 120000);
            
            // Guardar progreso antes de salir de la página
            window.addEventListener('beforeunload', function(e) {
                saveProgress();
            });
        });

        // Función para exportar datos (opcional)
        function exportProgram() {
            if (!validateForm()) {
                return;
            }

            const programData = {
                general: getDaysData(),
                pricing: getPricingData(),
                exportDate: new Date().toISOString()
            };

            const dataStr = JSON.stringify(programData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `programa_${document.getElementById('request-id').value}.json`;
            link.click();
            
            showNotification('Programa exportado exitosamente', 'success');
        }

        // Función para imprimir vista previa
        function printPreview() {
            updatePreview();
            
            const printWindow = window.open('', '_blank');
            const previewContent = document.querySelector('.preview-container').outerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Vista Previa - ${document.getElementById('program-title').value}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .preview-container { max-width: 800px; margin: 0 auto; }
                        .preview-header { height: 300px; position: relative; }
                        .preview-cover { width: 100%; height: 100%; object-fit: cover; }
                        .preview-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                         background: rgba(0,0,0,0.4); display: flex; flex-direction: column; 
                                         justify-content: center; align-items: center; color: white; text-align: center; }
                        .preview-title { font-size: 32px; font-weight: bold; margin-bottom: 16px; }
                        .preview-company { font-size: 18px; }
                        .preview-content { padding: 40px; }
                        .discover-btn { display: none; }
                        @media print { body { padding: 0; } }
                    </style>
                </head>
                <body>${previewContent}</body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        // Atajos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl + S para guardar
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveProgress();
            }
            
            // Ctrl + P para imprimir vista previa
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printPreview();
            }
            
            // Escape para cerrar modales
            if (e.key === 'Escape') {
                closeLibraryModal();
                closeServicesModal();
            }
        });

        // Funciones adicionales para mejorar UX
        function duplicateDay(dayId) {
            const dayElement = document.getElementById(`day-${dayId}`);
            if (dayElement) {
                const clonedDay = dayElement.cloneNode(true);
                dayCounter++;
                
                // Actualizar IDs y referencias
                clonedDay.id = `day-${dayCounter}`;
                clonedDay.querySelector('.day-number').textContent = dayCounter;
                clonedDay.querySelector('.day-title span').textContent = 
                    clonedDay.querySelector('.day-title span').textContent.replace(/Día \d+/, `Día ${dayCounter}`);
                
                // Actualizar event handlers
                const header = clonedDay.querySelector('.day-header');
                header.setAttribute('onclick', `toggleDay(${dayCounter})`);
                
                const editBtn = clonedDay.querySelector('.btn-secondary');
                editBtn.setAttribute('onclick', `event.stopPropagation(); editDay(${dayCounter})`);
                
                const removeBtn = clonedDay.querySelector('.btn-danger');
                removeBtn.setAttribute('onclick', `event.stopPropagation(); removeDay(${dayCounter})`);
                
                const content = clonedDay.querySelector('.day-content');
                content.id = `day-content-${dayCounter}`;
                content.classList.remove('active');
                
                const servicesContainer = clonedDay.querySelector('.services-grid');
                servicesContainer.id = `services-${dayCounter}`;
                
                // Insertar después del día original
                dayElement.parentNode.insertBefore(clonedDay, dayElement.nextSibling);
                
                showNotification('Día duplicado exitosamente', 'success');
            }
        }

        function reorderDays() {
            // Implementar funcionalidad de reordenamiento drag & drop
            // Esto requeriría una librería como SortableJS para mejor UX
            showNotification('Función de reordenamiento en desarrollo', 'info');
        }

        // Función para validar campos en tiempo real
        function setupRealTimeValidation() {
            const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.style.borderColor = '#ef4444';
                        this.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    } else {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.style.borderColor === 'rgb(239, 68, 68)') {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    }
                });
            });
        }

        // Inicializar validación en tiempo real
        document.addEventListener('DOMContentLoaded', setupRealTimeValidation);
    </script>
</body>
</html>