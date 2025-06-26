<?php
// ====================================================================
// ARCHIVO: pages/programa.php - REESTRUCTURADO CON PESTAÑAS
// ====================================================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::init();
App::requireLogin();

try {
    ConfigManager::init();
    $config = ConfigManager::get();
    $company_name = ConfigManager::getCompanyName();
} catch(Exception $e) {
    $config = [];
    $company_name = 'Travel Agency';
}

$user = App::getUser();
$is_editing = isset($_GET['id']) && !empty($_GET['id']);
$programa_id = $is_editing ? intval($_GET['id']) : null;

// Cargar datos si está editando
$form_data = [
    'traveler_name' => '',
    'traveler_lastname' => '',
    'destination' => '',
    'arrival_date' => '',
    'departure_date' => '',
    'passengers' => 1,
    'accompaniment' => 'sin-acompanamiento',
    'program_title' => '',
    'language' => 'es',
    'request_id' => '',
    'cover_image' => ''
];

if ($is_editing) {
    try {
        $db = Database::getInstance();
        $programa_data = $db->fetch(
            "SELECT * FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
            [$programa_id, $user['id']]
        );
        
        if (!$programa_data) {
            header('Location: ' . APP_URL . '/itinerarios');
            exit;
        }
        
        $personalizacion_data = $db->fetch(
            "SELECT * FROM programa_personalizacion WHERE solicitud_id = ?", 
            [$programa_id]
        );
        
        $form_data = [
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
    } catch(Exception $e) {
        error_log("Error cargando programa: " . $e->getMessage());
        header('Location: ' . APP_URL . '/itinerarios');
        exit;
    }
}

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
        
        /* Container principal */
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
        
        /* Pestañas de contenido */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
            margin-bottom: 24px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2d5a4a;
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #2d5a4a;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #234a3a;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #2d5a4a;
            border: 2px solid #2d5a4a;
        }
        
        .btn-outline:hover {
            background-color: #2d5a4a;
            color: white;
        }
        
        /* Estilos específicos para Día a día */
        .days-container {
            display: grid;
            gap: 30px;
        }
        
        .day-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .day-card:hover {
            border-color: #2d5a4a;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .day-header {
            background: linear-gradient(135deg, #2d5a4a 0%, #4a7c59 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .day-number {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            backdrop-filter: blur(10px);
        }
        
        .day-actions {
            display: flex;
            gap: 8px;
        }
        
        .day-content {
            padding: 25px;
        }
        
        .day-images {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            height: 200px;
        }
        
        .day-image {
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            position: relative;
        }
        
        .day-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .day-image:hover img {
            transform: scale(1.05);
        }
        
        .day-image.main {
            grid-row: span 2;
        }
        
        .day-info h4 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .day-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .day-meta {
            display: flex;
            gap: 20px;
            color: #888;
            font-size: 14px;
        }
        
        .day-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Estilos para servicios del día */
        .day-services {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        .services-header h5 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .service-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .service-btn {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
        }
        
        .service-btn:hover {
            background: #2d5a4a;
            color: white;
            border-color: #2d5a4a;
            transform: translateY(-2px);
        }
        
        .meals-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .meals-section h6 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .meals-options {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
        }
        
        .meal-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .meal-option input[type="radio"] {
            margin: 0;
        }
        
        .meal-details {
            margin-top: 10px;
        }
        
        .meal-checkboxes {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .meal-checkbox {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #666;
        }
        
        .meal-checkbox input[type="checkbox"] {
            margin: 0;
        }
        
        .added-services {
            margin-top: 15px;
        }
        
        .service-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .service-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .service-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        
        .service-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }
        
        .service-icon.actividad {
            background: #28a745;
        }
        
        .service-icon.transporte {
            background: #007bff;
        }
        
        .service-icon.alojamiento {
            background: #ffc107;
            color: #333;
        }
        
        .service-details h6 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        
        .service-details p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        
        .service-actions {
            display: flex;
            gap: 5px;
        }
        
        .service-actions button {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-edit-service {
            background: #6c757d;
            color: white;
        }
        
        .btn-remove-service {
            background: #dc3545;
            color: white;
        }
        
        /* Estilos para biblioteca modal */
        .biblioteca-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 20px;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .biblioteca-item {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .biblioteca-item:hover {
            border-color: #2d5a4a;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }
        
        .biblioteca-item.selected {
            border-color: #2d5a4a;
            background: #f0fff0;
            box-shadow: 0 8px 25px rgba(45, 90, 74, 0.3);
        }
        
        .biblioteca-item-image {
            height: 180px;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }
        
        .biblioteca-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .biblioteca-item:hover .biblioteca-item-image img {
            transform: scale(1.1);
        }
        
        .biblioteca-item-content {
            padding: 20px;
        }
        
        .biblioteca-item-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .biblioteca-item-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .biblioteca-item-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #888;
            font-size: 13px;
        }
        
        .biblioteca-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-left: 45px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
        }
        
        .search-box .fas {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state .fas {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        /* Estilos para Precio */
        .price-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .price-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
        }
        
        .price-input {
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            color: #2d5a4a;
        }
        
        /* Preview panel */
        .preview-section {
            width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 140px;
        }
        
        .preview-header {
            padding: 24px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .preview-body {
            padding: 24px;
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 20px 15px;
            }
            
            .preview-section {
                width: 100%;
                position: static;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .tab-nav {
                flex-wrap: wrap;
                padding: 0 15px;
            }
            
            .tab-item {
                padding: 12px 16px;
            }
        }
        
        /* Estados de carga */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Alertas y notificaciones */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .expand-icon {
            transition: transform 0.3s ease;
        }
        
        .section-header.collapsed .expand-icon {
            transform: rotate(180deg);
        }
        
        .section-body.collapsed {
            display: none;
        }
        
        /* Estilos adicionales para modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .preview-program {
            padding: 20px;
        }
        
        .preview-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .preview-details {
            margin-bottom: 20px;
        }
        
        .detail-row {
            margin-bottom: 8px;
        }
        
        .preview-days {
            margin-top: 20px;
        }
        
        .preview-day {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .preview-item {
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>

<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-brand">
            <a href="<?= APP_URL ?>/dashboard" class="logo"><?= $company_name ?></a>
        </div>
        <div class="nav-links">
            <a href="<?= APP_URL ?>/dashboard">Dashboard</a>
            <a href="<?= APP_URL ?>/itinerarios">Itinerarios</a>
            <a href="<?= APP_URL ?>/biblioteca">Biblioteca</a>
            <div class="user-avatar">
                <?= strtoupper(substr($user['name'] ?: 'U', 0, 1)) ?>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <div class="tab-nav">
            <a href="#" class="tab-item active" data-tab="mi-programa">Mi programa</a>
            <a href="#" class="tab-item" data-tab="dia-a-dia">Día a día</a>
            <a href="#" class="tab-item" data-tab="precio">Precio</a>
            <a href="<?= APP_URL ?>/biblioteca" class="tab-item">
                <i class="fas fa-book"></i> Biblioteca
            </a>
            <a href="#" class="tab-item" data-tab="vista-previa">
                <i class="fas fa-eye"></i> Vista previa
            </a>
            <a href="#" class="tab-item">
                <i class="fas fa-share"></i> Compartir
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Form Section -->
        <div class="form-section">
            <!-- Contenido de la pestaña Mi Programa -->
            <div id="mi-programa" class="tab-content active">
                <form id="programa-form" method="POST" enctype="multipart/form-data" novalidate>
                    
                    <!-- Campos ocultos -->
                    <?php if ($is_editing): ?>
                        <input type="hidden" id="programa-id-hidden" name="programa_id" value="<?= $programa_id ?>">
                    <?php endif; ?>
                    
                    <!-- Sección: Solicitud del viajero -->
                    <div class="section-card">
                        <div class="section-header" onclick="toggleSection(this)">
                            <div class="section-title">
                                <i class="fas fa-user"></i>
                                Solicitud del viajero
                            </div>
                            <i class="fas fa-chevron-up expand-icon"></i>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">ID de solicitud</label>
                                <input type="text" class="form-control" id="request-id" name="request_id" 
                                       value="<?= htmlspecialchars($form_data['request_id']) ?>" readonly>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="traveler-name">Nombre del viajero *</label>
                                    <input type="text" class="form-control" id="traveler-name" name="traveler_name" 
                                           value="<?= htmlspecialchars($form_data['traveler_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="traveler-lastname">Apellido del viajero *</label>
                                    <input type="text" class="form-control" id="traveler-lastname" name="traveler_lastname" 
                                           value="<?= htmlspecialchars($form_data['traveler_lastname']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="destination">Destino *</label>
                                <input type="text" class="form-control" id="destination" name="destination" 
                                       value="<?= htmlspecialchars($form_data['destination']) ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="arrival-date">Fecha de llegada *</label>
                                    <input type="date" class="form-control" id="arrival-date" name="arrival_date" 
                                           value="<?= htmlspecialchars($form_data['arrival_date']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="departure-date">Fecha de salida *</label>
                                    <input type="date" class="form-control" id="departure-date" name="departure_date" 
                                           value="<?= htmlspecialchars($form_data['departure_date']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="passengers">Número de pasajeros *</label>
                                    <input type="number" class="form-control" id="passengers" name="passengers" 
                                           value="<?= htmlspecialchars($form_data['passengers']) ?>" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="accompaniment">Acompañamiento</label>
                                    <select class="form-control" id="accompaniment" name="accompaniment">
                                        <option value="sin-acompanamiento" <?= $form_data['accompaniment'] === 'sin-acompanamiento' ? 'selected' : '' ?>>Sin acompañamiento</option>
                                        <option value="guide" <?= $form_data['accompaniment'] === 'guide' ? 'selected' : '' ?>>Con guía</option>
                                        <option value="representative" <?= $form_data['accompaniment'] === 'representative' ? 'selected' : '' ?>>Con representante</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Personalización del programa -->
                    <div class="section-card">
                        <div class="section-header" onclick="toggleSection(this)">
                            <div class="section-title">
                                <i class="fas fa-palette"></i>
                                Personalización del programa
                            </div>
                            <i class="fas fa-chevron-up expand-icon"></i>
                        </div>
                        <div class="section-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="program-title">Título del programa</label>
                                    <input type="text" class="form-control" id="program-title" name="program_title" 
                                           value="<?= htmlspecialchars($form_data['program_title']) ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="language">Idioma predeterminado</label>
                                    <select class="form-control" id="language" name="language">
                                        <option value="es" <?= $form_data['language'] === 'es' ? 'selected' : '' ?>>Español</option>
                                        <option value="en" <?= $form_data['language'] === 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="fr" <?= $form_data['language'] === 'fr' ? 'selected' : '' ?>>Français</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="cover-image">Foto de portada</label>
                                <input type="file" class="form-control" id="cover-image" name="cover_image" accept="image/*">
                                <?php if (!empty($form_data['cover_image'])): ?>
                                    <div class="current-image" style="margin-top: 10px;">
                                        <img src="<?= htmlspecialchars($form_data['cover_image']) ?>" alt="Imagen actual" style="max-width: 200px; height: auto; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="form-actions" style="text-align: center; padding: 24px 0;">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-save"></i>
                            <?= $is_editing ? 'Actualizar programa' : 'Crear programa' ?>
                        </button>
                        <a href="<?= APP_URL ?>/itinerarios" class="btn btn-outline" style="margin-left: 16px;">
                            <i class="fas fa-arrow-left"></i>
                            Volver a itinerarios
                        </a>
                    </div>
                </form>
            </div>

            <!-- Contenido de la pestaña Día a día -->
            <div id="dia-a-dia" class="tab-content">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-calendar-day"></i>
                            Gestión de días del programa
                        </div>
                        <button class="btn btn-primary" onclick="agregarDia()">
                            <i class="fas fa-plus"></i>
                            Agregar día
                        </button>
                    </div>
                    <div class="section-body">
                        <div id="days-container" class="days-container">
                            <!-- Los días se cargarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de la pestaña Precio -->
            <div id="precio" class="tab-content">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-dollar-sign"></i>
                            Configuración de precios
                        </div>
                    </div>
                    <div class="section-body">
                        <form id="precio-form" method="POST">
                            <div class="price-section">
                                <div class="price-card">
                                    <h4>Información de precios</h4>
                                    <div class="form-group">
                                        <label class="form-label">Moneda</label>
                                        <select class="form-control" name="moneda">
                                            <option value="USD">USD - Dólares</option>
                                            <option value="EUR">EUR - Euros</option>
                                            <option value="COP">COP - Pesos colombianos</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Precio por persona</label>
                                        <input type="number" class="form-control price-input" name="precio_por_persona" placeholder="0.00" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Precio total</label>
                                        <input type="number" class="form-control price-input" name="precio_total" placeholder="0.00" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Noches incluidas</label>
                                        <input type="number" class="form-control" name="noches_incluidas" placeholder="0" min="0">
                                    </div>
                                </div>
                                
                                <div class="price-card">
                                    <h4>Información adicional</h4>
                                    <div class="form-group">
                                        <label class="form-label">¿Qué incluye el precio?</label>
                                        <textarea class="form-control" name="precio_incluye" rows="4" placeholder="Describe qué servicios están incluidos..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">¿Qué NO incluye?</label>
                                        <textarea class="form-control" name="precio_no_incluye" rows="4" placeholder="Describe qué servicios NO están incluidos..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="movilidad_reducida" value="1">
                                        <label class="form-label" style="margin-left: 8px;">Adaptado para movilidad reducida</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="section-card" style="margin-top: 20px;">
                                <div class="section-body">
                                    <div class="form-group">
                                        <label class="form-label">Condiciones generales</label>
                                        <textarea class="form-control" name="condiciones_generales" rows="4" placeholder="Condiciones y términos del programa..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Información de pasaporte</label>
                                        <textarea class="form-control" name="info_pasaporte" rows="3" placeholder="Requisitos de documentación..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Información de seguros</label>
                                        <textarea class="form-control" name="info_seguros" rows="3" placeholder="Información sobre seguros de viaje..."></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions" style="text-align: center; padding: 24px 0;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Guardar precios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contenido de la pestaña Vista previa -->
            <div id="vista-previa" class="tab-content">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title">
                            <i class="fas fa-eye"></i>
                            Vista previa del programa
                        </div>
                    </div>
                    <div class="section-body">
                        <div id="preview-content">
                            <!-- Contenido de vista previa se generará aquí -->
                            <p style="text-align: center; color: #666; font-style: italic;">
                                La vista previa se generará cuando el programa esté guardado
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel (Solo visible en Mi programa) -->
        <div class="preview-section" id="preview-panel">
            <div class="preview-header">
                <i class="fas fa-eye"></i>
                Vista rápida
            </div>
            <div class="preview-body">
                <div id="preview-summary">
                    <!-- Resumen dinámico del programa -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar días desde biblioteca -->
    <div id="bibliotecaModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 1200px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3><i class="fas fa-book"></i> Seleccionar día de la biblioteca</h3>
                <button class="close-modal" onclick="cerrarModalBiblioteca()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="biblioteca-filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar días por título, ubicación o descripción..." 
                               id="search-dias" class="form-control">
                    </div>
                </div>
                <div id="biblioteca-dias-grid" class="biblioteca-grid">
                    <!-- Los días de la biblioteca se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalBiblioteca()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn btn-primary" onclick="agregarDiaSeleccionado()" id="btn-agregar-dia" disabled>
                    <i class="fas fa-plus"></i> Agregar día seleccionado
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para agregar servicios (actividades, transporte, alojamiento) -->
    <div id="serviciosModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 1200px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="servicios-modal-title"><i class="fas fa-plus"></i> Agregar servicio</h3>
                <button class="close-modal" onclick="cerrarModalServicios()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="biblioteca-filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar servicios..." 
                               id="search-servicios" class="form-control">
                    </div>
                </div>
                <div id="servicios-grid" class="biblioteca-grid">
                    <!-- Los servicios de la biblioteca se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalServicios()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn btn-primary" onclick="agregarServicioSeleccionado()" id="btn-agregar-servicio" disabled>
                    <i class="fas fa-plus"></i> Agregar servicio
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let currentTab = 'mi-programa';
        let programaId = <?= $programa_id ? $programa_id : 'null' ?>;
        let isEditing = <?= $is_editing ? 'true' : 'false' ?>;
        let selectedDiaId = null;
        let selectedServicioId = null;
        let currentDiaId = null;
        let currentTipoServicio = null;
        let diasPrograma = [];

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            setupTabNavigation();
            setupFormHandling();
            setupPreviewUpdates();
            setupMealHandlers();
            
            if (isEditing && programaId) {
                cargarDiasPrograma();
                cargarPreciosPrograma();
            }
        });

        // Gestión de pestañas
        function setupTabNavigation() {
            const tabItems = document.querySelectorAll('.tab-item[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');
            const previewPanel = document.getElementById('preview-panel');

            tabItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = this.dataset.tab;
                    
                    // Remover clase active de todas las pestañas
                    tabItems.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Activar pestaña seleccionada
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                    
                    currentTab = targetTab;
                    
                    // Mostrar/ocultar panel de preview
                    if (targetTab === 'mi-programa') {
                        previewPanel.style.display = 'block';
                    } else {
                        previewPanel.style.display = 'none';
                    }
                    
                    // Acciones específicas por pestaña
                    switch(targetTab) {
                        case 'dia-a-dia':
                            if (isEditing && programaId) {
                                cargarDiasPrograma();
                            }
                            break;
                        case 'precio':
                            if (isEditing && programaId) {
                                cargarPreciosPrograma();
                            }
                            break;
                        case 'vista-previa':
                            generarVistaPrevia();
                            break;
                    }
                });
            });
        }

        // Manejo del formulario principal
        function setupFormHandling() {
            const form = document.getElementById('programa-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    guardarPrograma();
                });
            }

            const precioForm = document.getElementById('precio-form');
            if (precioForm) {
                precioForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    guardarPrecios();
                });
            }
        }

        // Actualización de vista previa en tiempo real
        function setupPreviewUpdates() {
            const inputs = document.querySelectorAll('#programa-form input, #programa-form select, #programa-form textarea');
            inputs.forEach(input => {
                input.addEventListener('input', updatePreview);
            });
        }

        // Configurar manejadores de comidas
        function setupMealHandlers() {
            document.addEventListener('change', function(e) {
                if (e.target.name && e.target.name.startsWith('meals_')) {
                    const diaId = e.target.name.split('_')[1];
                    const mealDetails = document.getElementById(`meal-details-${diaId}`);
                    
                    if (e.target.value === 'incluidas') {
                        mealDetails.style.display = 'block';
                    } else {
                        mealDetails.style.display = 'none';
                    }
                }
            });
        }

        // Función para guardar programa
        async function guardarPrograma() {
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.innerHTML = '<span class="spinner"></span> Guardando...';
                submitBtn.disabled = true;

                const formData = new FormData(document.getElementById('programa-form'));
                formData.append('action', 'save_programa');

                const response = await fetch('<?= APP_URL ?>/modules/programa/api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Programa guardado exitosamente', 'success');
                    
                    if (!isEditing) {
                        // Redirigir a edición con el nuevo ID
                        programaId = result.programa_id;
                        isEditing = true;
                        
                        // Actualizar URL sin recargar página
                        window.history.replaceState({}, '', `<?= APP_URL ?>/programa?id=${programaId}`);
                        
                        // Actualizar campo hidden
                        const hiddenInput = document.getElementById('programa-id-hidden');
                        if (!hiddenInput) {
                            const form = document.getElementById('programa-form');
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.id = 'programa-id-hidden';
                            input.name = 'programa_id';
                            input.value = programaId;
                            form.appendChild(input);
                        } else {
                            hiddenInput.value = programaId;
                        }
                        
                        // Actualizar ID de solicitud si se generó
                        if (result.request_id) {
                            document.getElementById('request-id').value = result.request_id;
                        }
                    }
                    
                    updatePreview();
                } else {
                    showAlert(result.message || 'Error al guardar el programa', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión al guardar', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }

        // Función para cargar días del programa
        async function cargarDiasPrograma() {
            if (!programaId) return;

            try {
                const response = await fetch(`<?= APP_URL ?>/modules/programa/dias_api.php?action=list&programa_id=${programaId}`);
                const result = await response.json();

                if (result.success) {
                    diasPrograma = result.data;
                    renderizarDias();
                    
                    // Cargar servicios para cada día
                    diasPrograma.forEach(dia => {
                        cargarServiciosDia(dia.id);
                    });
                } else {
                    console.error('Error cargando días:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Función para renderizar días
        function renderizarDias() {
            const container = document.getElementById('days-container');
            if (!container) return;

            if (diasPrograma.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-plus"></i>
                        <h3>No hay días agregados</h3>
                        <p>Comienza agregando días a tu programa desde la biblioteca</p>
                        <button class="btn btn-primary" onclick="agregarDia()">
                            <i class="fas fa-plus"></i>
                            Agregar primer día
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = diasPrograma.map((dia, index) => `
                <div class="day-card" data-dia-id="${dia.id}">
                    <div class="day-header">
                        <div class="day-number">Día ${index + 1}</div>
                        <div class="day-actions">
                            <button class="btn btn-outline" onclick="editarDia(${dia.id})" title="Editar día">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary" onclick="eliminarDia(${dia.id})" title="Eliminar día">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="day-content">
                        ${dia.imagen1 || dia.imagen2 || dia.imagen3 ? `
                            <div class="day-images">
                                ${dia.imagen1 ? `
                                    <div class="day-image main">
                                        <img src="${dia.imagen1}" alt="${dia.titulo}" loading="lazy">
                                    </div>
                                ` : ''}
                                ${dia.imagen2 ? `
                                    <div class="day-image">
                                        <img src="${dia.imagen2}" alt="${dia.titulo}" loading="lazy">
                                    </div>
                                ` : ''}
                                ${dia.imagen3 ? `
                                    <div class="day-image">
                                        <img src="${dia.imagen3}" alt="${dia.titulo}" loading="lazy">
                                    </div>
                                ` : ''}
                            </div>
                        ` : ''}
                        <div class="day-info">
                            <h4>${dia.titulo}</h4>
                            <div class="day-description">
                                ${dia.descripcion || '<em style="color: #999;">Sin descripción</em>'}
                            </div>
                            <div class="day-meta">
                                <span>
                                    <i class="fas fa-map-marker-alt"></i> 
                                    ${dia.ubicacion || 'Sin ubicación especificada'}
                                </span>
                                ${dia.fecha_dia ? `
                                    <span>
                                        <i class="fas fa-calendar"></i> 
                                        ${new Date(dia.fecha_dia).toLocaleDateString('es-ES')}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        
                        <!-- Servicios del día -->
                        <div class="day-services">
                            <div class="services-header">
                                <h5><i class="fas fa-plus-circle"></i> Agregar servicios al día:</h5>
                            </div>
                            <div class="service-buttons">
                                <button class="service-btn" onclick="agregarServicio(${dia.id}, 'actividad')">
                                    <i class="fas fa-hiking"></i>
                                    Actividad
                                </button>
                                <button class="service-btn" onclick="agregarServicio(${dia.id}, 'transporte')">
                                    <i class="fas fa-car"></i>
                                    Transporte
                                </button>
                                <button class="service-btn" onclick="agregarServicio(${dia.id}, 'alojamiento')">
                                    <i class="fas fa-bed"></i>
                                    Alojamiento
                                </button>
                            </div>
                            
                            <!-- Opciones de comidas -->
                            <div class="meals-section">
                                <h6><i class="fas fa-utensils"></i> Comidas:</h6>
                                <div class="meals-options">
                                    <label class="meal-option">
                                        <input type="radio" name="meals_${dia.id}" value="incluidas">
                                        <span>Comidas incluidas</span>
                                    </label>
                                    <label class="meal-option">
                                        <input type="radio" name="meals_${dia.id}" value="no_incluidas" checked>
                                        <span>Comidas no incluidas</span>
                                    </label>
                                </div>
                                <div class="meal-details" id="meal-details-${dia.id}" style="display: none;">
                                    <div class="meal-checkboxes">
                                        <label class="meal-checkbox">
                                            <input type="checkbox" name="meal_desayuno_${dia.id}">
                                            <span>Desayuno</span>
                                        </label>
                                        <label class="meal-checkbox">
                                            <input type="checkbox" name="meal_almuerzo_${dia.id}">
                                            <span>Almuerzo</span>
                                        </label>
                                        <label class="meal-checkbox">
                                            <input type="checkbox" name="meal_cena_${dia.id}">
                                            <span>Cena</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lista de servicios agregados -->
                            <div class="added-services" id="services-${dia.id}">
                                <!-- Los servicios agregados aparecerán aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Función para agregar día desde biblioteca
        function agregarDia() {
            abrirModalBiblioteca();
        }

        // Función para abrir modal de biblioteca
        async function abrirModalBiblioteca() {
            const modal = document.getElementById('bibliotecaModal');
            modal.style.display = 'block';
            
            await cargarDiasBiblioteca();
        }

        // Función para cargar días de biblioteca
        async function cargarDiasBiblioteca() {
            try {
                const response = await fetch('<?= APP_URL ?>/modules/biblioteca/api.php?action=list&type=dias');
                const result = await response.json();

                if (result.success) {
                    renderizarDiasBiblioteca(result.data);
                } else {
                    console.error('Error cargando biblioteca:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Función para renderizar días de biblioteca
        function renderizarDiasBiblioteca(dias) {
            const container = document.getElementById('biblioteca-dias-grid');
            if (!container) return;

            if (dias.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1;" class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>No hay días en la biblioteca</h3>
                        <p>Primero debes crear días en la biblioteca</p>
                        <a href="<?= APP_URL ?>/biblioteca" class="btn btn-primary">
                            <i class="fas fa-book"></i>
                            Ir a biblioteca
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = dias.map(dia => `
                <div class="biblioteca-item" data-dia-id="${dia.id}" onclick="seleccionarDia(${dia.id})">
                    ${dia.imagen1 ? `
                        <div class="biblioteca-item-image">
                            <img src="${dia.imagen1}" alt="${dia.titulo}" loading="lazy">
                        </div>
                    ` : `
                        <div class="biblioteca-item-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <i class="fas fa-image" style="font-size: 32px; color: #dee2e6;"></i>
                        </div>
                    `}
                    <div class="biblioteca-item-content">
                        <div class="biblioteca-item-title">${dia.titulo}</div>
                        <div class="biblioteca-item-description">
                            ${dia.descripcion || 'Sin descripción disponible'}
                        </div>
                        <div class="biblioteca-item-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            ${dia.ubicacion || 'Ubicación no especificada'}
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Configurar búsqueda
            setupSearchFunctionality(dias);
        }

        // Función para configurar búsqueda
        function setupSearchFunctionality(dias) {
            const searchInput = document.getElementById('search-dias');
            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                const filteredDias = dias.filter(dia => 
                    dia.titulo.toLowerCase().includes(searchTerm) ||
                    (dia.descripcion && dia.descripcion.toLowerCase().includes(searchTerm)) ||
                    (dia.ubicacion && dia.ubicacion.toLowerCase().includes(searchTerm))
                );
                
                renderFilteredDias(filteredDias);
            });
        }

        // Función para renderizar días filtrados
        function renderFilteredDias(dias) {
            const container = document.getElementById('biblioteca-dias-grid');
            if (!container) return;

            if (dias.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <h3>No se encontraron días</h3>
                        <p>Intenta con otros términos de búsqueda</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = dias.map(dia => `
                <div class="biblioteca-item" data-dia-id="${dia.id}" onclick="seleccionarDia(${dia.id})">
                    ${dia.imagen1 ? `
                        <div class="biblioteca-item-image">
                            <img src="${dia.imagen1}" alt="${dia.titulo}" loading="lazy">
                        </div>
                    ` : `
                        <div class="biblioteca-item-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <i class="fas fa-image" style="font-size: 32px; color: #dee2e6;"></i>
                        </div>
                    `}
                    <div class="biblioteca-item-content">
                        <div class="biblioteca-item-title">${dia.titulo}</div>
                        <div class="biblioteca-item-description">
                            ${dia.descripcion || 'Sin descripción disponible'}
                        </div>
                        <div class="biblioteca-item-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            ${dia.ubicacion || 'Ubicación no especificada'}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Función para seleccionar día
        function seleccionarDia(diaId) {
            // Remover selección previa
            document.querySelectorAll('.biblioteca-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Seleccionar nuevo día
            const item = document.querySelector(`[data-dia-id="${diaId}"]`);
            if (item) {
                item.classList.add('selected');
                selectedDiaId = diaId;
                document.getElementById('btn-agregar-dia').disabled = false;
                
                // Scroll suave hacia el elemento seleccionado
                item.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest',
                    inline: 'nearest'
                });
            }
        }

        // Función para agregar día seleccionado
        async function agregarDiaSeleccionado() {
            if (!selectedDiaId || !programaId) return;

            try {
                const response = await fetch('<?= APP_URL ?>/modules/programa/dias_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_from_biblioteca',
                        programa_id: programaId,
                        biblioteca_dia_id: selectedDiaId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Día agregado exitosamente', 'success');
                    cerrarModalBiblioteca();
                    cargarDiasPrograma(); // Recargar días
                } else {
                    showAlert(result.message || 'Error al agregar día', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            }
        }

        // Función para cerrar modal biblioteca
        function cerrarModalBiblioteca() {
            const modal = document.getElementById('bibliotecaModal');
            modal.style.display = 'none';
            selectedDiaId = null;
            document.getElementById('btn-agregar-dia').disabled = true;
            
            // Limpiar búsqueda
            const searchInput = document.getElementById('search-dias');
            if (searchInput) {
                searchInput.value = '';
            }
        }

        // Función para eliminar día
        async function eliminarDia(diaId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este día?')) return;

            try {
                const response = await fetch('<?= APP_URL ?>/modules/programa/dias_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        dia_id: diaId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Día eliminado exitosamente', 'success');
                    cargarDiasPrograma(); // Recargar días
                } else {
                    showAlert(result.message || 'Error al eliminar día', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            }
        }

        // Función para editar día (placeholder)
        function editarDia(diaId) {
            // TODO: Implementar edición de días
            showAlert('Función de edición en desarrollo', 'info');
        }

        // === FUNCIONES PARA SERVICIOS ===

        // Función para agregar servicio
        function agregarServicio(diaId, tipoServicio) {
            currentDiaId = diaId;
            currentTipoServicio = tipoServicio;
            abrirModalServicios(tipoServicio);
        }

        // Función para abrir modal de servicios
        async function abrirModalServicios(tipoServicio) {
            const modal = document.getElementById('serviciosModal');
            const title = document.getElementById('servicios-modal-title');
            
            // Actualizar título según el tipo
            const icons = {
                'actividad': 'fas fa-hiking',
                'transporte': 'fas fa-car',
                'alojamiento': 'fas fa-bed'
            };
            
            const titles = {
                'actividad': 'Agregar Actividad',
                'transporte': 'Agregar Transporte',
                'alojamiento': 'Agregar Alojamiento'
            };
            
            title.innerHTML = `<i class="${icons[tipoServicio]}"></i> ${titles[tipoServicio]}`;
            modal.style.display = 'block';
            
            await cargarServiciosBiblioteca(tipoServicio);
        }

        // Función para cargar servicios de biblioteca
        async function cargarServiciosBiblioteca(tipoServicio) {
            try {
                let endpoint = '';
                switch(tipoServicio) {
                    case 'actividad':
                        endpoint = 'actividades';
                        break;
                    case 'transporte':
                        endpoint = 'transportes';
                        break;
                    case 'alojamiento':
                        endpoint = 'alojamientos';
                        break;
                }
                
                const response = await fetch(`<?= APP_URL ?>/modules/biblioteca/api.php?action=list&type=${endpoint}`);
                const result = await response.json();

                if (result.success) {
                    renderizarServiciosBiblioteca(result.data, tipoServicio);
                } else {
                    console.error('Error cargando servicios:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Función para renderizar servicios de biblioteca
        function renderizarServiciosBiblioteca(servicios, tipoServicio) {
            const container = document.getElementById('servicios-grid');
            if (!container) return;

            if (servicios.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1;" class="empty-state">
                        <i class="fas fa-${getServiceIcon(tipoServicio)}"></i>
                        <h3>No hay ${tipoServicio}s en la biblioteca</h3>
                        <p>Primero debes crear ${tipoServicio}s en la biblioteca</p>
                        <a href="<?= APP_URL ?>/biblioteca" class="btn btn-primary">
                            <i class="fas fa-book"></i>
                            Ir a biblioteca
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = servicios.map(servicio => {
                const imagen = getServiceImage(servicio, tipoServicio);
                const descripcion = getServiceDescription(servicio, tipoServicio);
                
                return `
                    <div class="biblioteca-item" data-servicio-id="${servicio.id}" onclick="seleccionarServicio(${servicio.id})">
                        ${imagen ? `
                            <div class="biblioteca-item-image">
                                <img src="${imagen}" alt="${servicio.titulo || servicio.nombre}" loading="lazy">
                            </div>
                        ` : `
                            <div class="biblioteca-item-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <i class="fas fa-${getServiceIcon(tipoServicio)}" style="font-size: 32px; color: #dee2e6;"></i>
                            </div>
                        `}
                        <div class="biblioteca-item-content">
                            <div class="biblioteca-item-title">${servicio.titulo || servicio.nombre}</div>
                            <div class="biblioteca-item-description">
                                ${descripcion}
                            </div>
                            <div class="biblioteca-item-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                ${getServiceLocation(servicio, tipoServicio)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Configurar búsqueda de servicios
            setupServiceSearch(servicios, tipoServicio);
        }

        // Funciones auxiliares para servicios
        function getServiceIcon(tipoServicio) {
            const icons = {
                'actividad': 'hiking',
                'transporte': 'car',
                'alojamiento': 'bed'
            };
            return icons[tipoServicio] || 'star';
        }

        function getServiceImage(servicio, tipoServicio) {
            if (tipoServicio === 'actividad') {
                return servicio.imagen || null;
            } else if (tipoServicio === 'alojamiento') {
                return servicio.imagen || null;
            }
            return null; // Los transportes generalmente no tienen imagen
        }

        function getServiceDescription(servicio, tipoServicio) {
            if (tipoServicio === 'transporte') {
                return `${servicio.medio} - ${servicio.descripcion || 'Sin descripción'}`;
            }
            return servicio.descripcion || 'Sin descripción disponible';
        }

        function getServiceLocation(servicio, tipoServicio) {
            if (tipoServicio === 'transporte') {
                return `${servicio.lugar_salida || ''} → ${servicio.lugar_llegada || ''}`;
            }
            return servicio.ubicacion || servicio.lugar || 'Ubicación no especificada';
        }

        // Función para configurar búsqueda de servicios
        function setupServiceSearch(servicios, tipoServicio) {
            const searchInput = document.getElementById('search-servicios');
            if (!searchInput) return;

            // Limpiar listener anterior
            searchInput.removeEventListener('input', searchInput.searchHandler);
            
            searchInput.searchHandler = function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                const filteredServicios = servicios.filter(servicio => {
                    const titulo = (servicio.titulo || servicio.nombre || '').toLowerCase();
                    const descripcion = (servicio.descripcion || '').toLowerCase();
                    const ubicacion = getServiceLocation(servicio, tipoServicio).toLowerCase();
                    
                    return titulo.includes(searchTerm) || 
                           descripcion.includes(searchTerm) || 
                           ubicacion.includes(searchTerm);
                });
                
                renderFilteredServicios(filteredServicios, tipoServicio);
            };
            
            searchInput.addEventListener('input', searchInput.searchHandler);
        }

        // Función para renderizar servicios filtrados
        function renderFilteredServicios(servicios, tipoServicio) {
            const container = document.getElementById('servicios-grid');
            if (!container) return;

            if (servicios.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <h3>No se encontraron servicios</h3>
                        <p>Intenta con otros términos de búsqueda</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = servicios.map(servicio => {
                const imagen = getServiceImage(servicio, tipoServicio);
                const descripcion = getServiceDescription(servicio, tipoServicio);
                
                return `
                    <div class="biblioteca-item" data-servicio-id="${servicio.id}" onclick="seleccionarServicio(${servicio.id})">
                        ${imagen ? `
                            <div class="biblioteca-item-image">
                                <img src="${imagen}" alt="${servicio.titulo || servicio.nombre}" loading="lazy">
                            </div>
                        ` : `
                            <div class="biblioteca-item-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <i class="fas fa-${getServiceIcon(tipoServicio)}" style="font-size: 32px; color: #dee2e6;"></i>
                            </div>
                        `}
                        <div class="biblioteca-item-content">
                            <div class="biblioteca-item-title">${servicio.titulo || servicio.nombre}</div>
                            <div class="biblioteca-item-description">
                                ${descripcion}
                            </div>
                            <div class="biblioteca-item-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                ${getServiceLocation(servicio, tipoServicio)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Función para seleccionar servicio
        function seleccionarServicio(servicioId) {
            // Remover selección previa
            document.querySelectorAll('#servicios-grid .biblioteca-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Seleccionar nuevo servicio
            const item = document.querySelector(`#servicios-grid [data-servicio-id="${servicioId}"]`);
            if (item) {
                item.classList.add('selected');
                selectedServicioId = servicioId;
                document.getElementById('btn-agregar-servicio').disabled = false;
                
                // Scroll suave hacia el elemento seleccionado
                item.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest',
                    inline: 'nearest'
                });
            }
        }

        // Función para agregar servicio seleccionado
        async function agregarServicioSeleccionado() {
            if (!selectedServicioId || !currentDiaId || !currentTipoServicio) return;

            try {
                const response = await fetch('<?= APP_URL ?>/modules/programa/servicios_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_service',
                        dia_id: currentDiaId,
                        tipo_servicio: currentTipoServicio,
                        biblioteca_item_id: selectedServicioId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Servicio agregado exitosamente', 'success');
                    cerrarModalServicios();
                    cargarServiciosDia(currentDiaId); // Recargar servicios del día
                } else {
                    showAlert(result.message || 'Error al agregar servicio', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            }
        }

        // Función para cerrar modal de servicios
        function cerrarModalServicios() {
            const modal = document.getElementById('serviciosModal');
            modal.style.display = 'none';
            selectedServicioId = null;
            currentDiaId = null;
            currentTipoServicio = null;
            document.getElementById('btn-agregar-servicio').disabled = true;
            
            // Limpiar búsqueda
            const searchInput = document.getElementById('search-servicios');
            if (searchInput) {
                searchInput.value = '';
            }
        }

        // Función para cargar servicios de un día específico
        async function cargarServiciosDia(diaId) {
            try {
                const response = await fetch(`<?= APP_URL ?>/modules/programa/servicios_api.php?action=list&dia_id=${diaId}`);
                const result = await response.json();

                if (result.success) {
                    renderizarServiciosDia(diaId, result.data);
                } else {
                    console.error('Error cargando servicios del día:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Función para renderizar servicios de un día
        function renderizarServiciosDia(diaId, servicios) {
            const container = document.getElementById(`services-${diaId}`);
            if (!container) return;

            if (servicios.length === 0) {
                container.innerHTML = `
                    <p style="color: #666; font-style: italic; text-align: center; padding: 10px;">
                        No hay servicios agregados a este día
                    </p>
                `;
                return;
            }

            container.innerHTML = `
                <h6 style="margin-bottom: 12px; color: #333; font-weight: 600;">
                    <i class="fas fa-list"></i> Servicios agregados:
                </h6>
                ${servicios.map(servicio => `
                    <div class="service-item">
                        <div class="service-info">
                            <div class="service-icon ${servicio.tipo_servicio}">
                                <i class="fas fa-${getServiceIconByType(servicio.tipo_servicio)}"></i>
                            </div>
                            <div class="service-details">
                                <h6>${servicio.titulo || servicio.nombre}</h6>
                                <p>${getServiceSummary(servicio)}</p>
                            </div>
                        </div>
                        <div class="service-actions">
                            <button class="btn-edit-service" onclick="editarServicio(${servicio.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-remove-service" onclick="eliminarServicio(${servicio.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            `;
        }

        // Funciones auxiliares para renderizar servicios
        function getServiceIconByType(tipo) {
            const icons = {
                'actividad': 'hiking',
                'transporte': 'car',
                'alojamiento': 'bed'
            };
            return icons[tipo] || 'star';
        }

        function getServiceSummary(servicio) {
            if (servicio.tipo_servicio === 'transporte') {
                return `${servicio.medio || ''} - ${servicio.lugar_salida || ''} → ${servicio.lugar_llegada || ''}`;
            }
            return servicio.descripcion ? 
                (servicio.descripcion.length > 80 ? 
                    servicio.descripcion.substring(0, 80) + '...' : 
                    servicio.descripcion) : 
                'Sin descripción';
        }

        // Función para eliminar servicio
        async function eliminarServicio(servicioId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este servicio?')) return;

            try {
                const response = await fetch('<?= APP_URL ?>/modules/programa/servicios_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        servicio_id: servicioId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Servicio eliminado exitosamente', 'success');
                    // Recargar servicios de todos los días visibles
                    diasPrograma.forEach(dia => {
                        cargarServiciosDia(dia.id);
                    });
                } else {
                    showAlert(result.message || 'Error al eliminar servicio', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            }
        }

        // Función para editar servicio (placeholder)
        function editarServicio(servicioId) {
            // TODO: Implementar edición de servicios
            showAlert('Función de edición en desarrollo', 'info');
        }

        // === FUNCIONES PARA PRECIOS ===

        // Función para cargar precios del programa
        async function cargarPreciosPrograma() {
            if (!programaId) return;

            try {
                const response = await fetch(`<?= APP_URL ?>/modules/programa/precios_api.php?action=get&programa_id=${programaId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const data = result.data;
                    const form = document.getElementById('precio-form');
                    
                    // Llenar campos del formulario
                    if (form) {
                        form.querySelector('[name="moneda"]').value = data.moneda || 'USD';
                        form.querySelector('[name="precio_por_persona"]').value = data.precio_por_persona || '';
                        form.querySelector('[name="precio_total"]').value = data.precio_total || '';
                        form.querySelector('[name="noches_incluidas"]').value = data.noches_incluidas || '';
                        form.querySelector('[name="precio_incluye"]').value = data.precio_incluye || '';
                        form.querySelector('[name="precio_no_incluye"]').value = data.precio_no_incluye || '';
                        form.querySelector('[name="condiciones_generales"]').value = data.condiciones_generales || '';
                        form.querySelector('[name="info_pasaporte"]').value = data.info_pasaporte || '';
                        form.querySelector('[name="info_seguros"]').value = data.info_seguros || '';
                        form.querySelector('[name="movilidad_reducida"]').checked = data.movilidad_reducida == 1;
                    }
                }
            } catch (error) {
                console.error('Error cargando precios:', error);
            }
        }

        // Función para guardar precios
        async function guardarPrecios() {
            if (!programaId) {
                showAlert('Primero debes guardar el programa', 'error');
                return;
            }

            try {
                const formData = new FormData(document.getElementById('precio-form'));
                formData.append('action', 'save');
                formData.append('programa_id', programaId);

                const response = await fetch('<?= APP_URL ?>/modules/programa/precios_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Precios guardados exitosamente', 'success');
                } else {
                    showAlert(result.message || 'Error al guardar precios', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            }
        }

        // === FUNCIONES PARA VISTA PREVIA ===

        // Función para generar vista previa
        function generarVistaPrevia() {
            const previewContent = document.getElementById('preview-content');
            if (!previewContent) return;

            // Obtener datos del formulario
            const formData = new FormData(document.getElementById('programa-form'));
            
            const preview = `
                <div class="preview-program">
                    <div class="preview-header">
                        <h2>${formData.get('program_title') || 'Programa sin título'}</h2>
                        <p><strong>Destino:</strong> ${formData.get('destination') || 'No especificado'}</p>
                    </div>
                    
                    <div class="preview-details">
                        <div class="detail-row">
                            <span><strong>Viajero:</strong> ${formData.get('traveler_name') || ''} ${formData.get('traveler_lastname') || ''}</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Fechas:</strong> ${formData.get('arrival_date') || ''} - ${formData.get('departure_date') || ''}</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Pasajeros:</strong> ${formData.get('passengers') || 1}</span>
                        </div>
                        <div class="detail-row">
                            <span><strong>Acompañamiento:</strong> ${formData.get('accompaniment') || 'Sin acompañamiento'}</span>
                        </div>
                    </div>
                    
                    <div class="preview-days">
                        <h4>Días del programa (${diasPrograma.length})</h4>
                        ${diasPrograma.length > 0 ? 
                            diasPrograma.map((dia, index) => `
                                <div class="preview-day">
                                    <strong>Día ${index + 1}:</strong> ${dia.titulo}
                                </div>
                            `).join('') : 
                            '<p style="color: #666; font-style: italic;">No hay días agregados</p>'
                        }
                    </div>
                </div>
            `;

            previewContent.innerHTML = preview;
        }

        // Función para actualizar vista previa rápida
        function updatePreview() {
            const previewSummary = document.getElementById('preview-summary');
            if (!previewSummary) return;

            const form = document.getElementById('programa-form');
            const formData = new FormData(form);

            const summary = `
                <div class="preview-item">
                    <strong>Programa:</strong> ${formData.get('program_title') || 'Sin título'}
                </div>
                <div class="preview-item">
                    <strong>Destino:</strong> ${formData.get('destination') || 'No especificado'}
                </div>
                <div class="preview-item">
                    <strong>Viajero:</strong> ${formData.get('traveler_name') || ''} ${formData.get('traveler_lastname') || ''}
                </div>
                <div class="preview-item">
                    <strong>Fechas:</strong> ${formData.get('arrival_date') || ''} - ${formData.get('departure_date') || ''}
                </div>
                <div class="preview-item">
                    <strong>Pasajeros:</strong> ${formData.get('passengers') || 1}
                </div>
            `;

            previewSummary.innerHTML = summary;
        }

        // === FUNCIONES AUXILIARES ===

        // Función para mostrar alertas
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;

            // Insertar al inicio del contenido activo
            const activeTab = document.querySelector('.tab-content.active');
            if (activeTab) {
                activeTab.insertBefore(alert, activeTab.firstChild);
                
                // Remover después de 5 segundos
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }
        }

        // Función para colapsar/expandir secciones
        function toggleSection(header) {
            const body = header.nextElementSibling;
            const icon = header.querySelector('.expand-icon');
            
            if (body.style.display === 'none' || body.classList.contains('collapsed')) {
                body.style.display = 'block';
                body.classList.remove('collapsed');
                header.classList.remove('collapsed');
                icon.style.transform = 'rotate(0deg)';
            } else {
                body.style.display = 'none';
                body.classList.add('collapsed');
                header.classList.add('collapsed');
                icon.style.transform = 'rotate(180deg)';
            }
        }

        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(e) {
            const bibliotecaModal = document.getElementById('bibliotecaModal');
            const serviciosModal = document.getElementById('serviciosModal');
            
            if (e.target === bibliotecaModal) {
                cerrarModalBiblioteca();
            }
            
            if (e.target === serviciosModal) {
                cerrarModalServicios();
            }
        });

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const bibliotecaModal = document.getElementById('bibliotecaModal');
                const serviciosModal = document.getElementById('serviciosModal');
                
                if (bibliotecaModal.style.display === 'block') {
                    cerrarModalBiblioteca();
                }
                
                if (serviciosModal.style.display === 'block') {
                    cerrarModalServicios();
                }
            }
        });

        // Inicializar vista previa al cargar
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
        });

    </script>
</body>
</html>