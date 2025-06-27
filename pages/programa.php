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

/* ============================================================
   CSS PARA ALTERNATIVAS - AGREGAR AL <style> DE programa.php
   ============================================================ */

/* Grupo de servicios con alternativas */
.service-group {
    margin-bottom: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    transition: all 0.3s ease;
}

.service-group:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #2d5a4a;
}

/* Servicio principal */
.service-item.principal {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 2px solid #e0e0e0;
    position: relative;
}

.service-item.principal::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #2d5a4a 0%, #4a7c59 100%);
}

/* Container de alternativas */
.alternatives-container {
    background: #fafbfc;
    border-top: 1px solid #e9ecef;
}

/* Alternativas */
.service-item.alternativa {
    background: #fafbfc;
    border-bottom: 1px solid #e9ecef;
    position: relative;
    margin-left: 20px;
    margin-right: 0;
}

.service-item.alternativa:last-child {
    border-bottom: none;
}

.service-item.alternativa::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
}

/* Conector visual para alternativas */
.alternative-connector {
    position: absolute;
    left: -20px;
    top: 20px;
    width: 20px;
    height: 2px;
    background: linear-gradient(90deg, #17a2b8 0%, #20c997 100%);
}

.alternative-connector::before {
    content: '';
    position: absolute;
    right: -4px;
    top: -2px;
    width: 6px;
    height: 6px;
    background: #17a2b8;
    border-radius: 50%;
}

/* Iconos de servicios alternativas */
.service-icon.alternativa {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Botón para agregar alternativa */
.btn-add-alternative {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-add-alternative:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    transform: scale(1.05);
}

.btn-add-alternative:active {
    transform: scale(0.95);
}

/* Efecto hover para grupos de servicios */
.service-group:hover .service-item.principal {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
}

.service-group:hover .alternatives-container {
    background: #f0f8ff;
}

.service-group:hover .service-item.alternativa {
    background: #f0f8ff;
}

/* Badges para identificar principal vs alternativa */
.service-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
}

.service-badge.principal {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: #333;
}

.service-badge.alternativa {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    color: white;
}

/* Animaciones para alternativas */
.alternatives-container {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.service-group:hover .alternatives-container,
.service-group.expanded .alternatives-container {
    max-height: 1000px;
}

/* Indicador de cantidad de alternativas */
.alternatives-indicator {
    position: absolute;
    top: 8px;
    right: 50px;
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

/* Mejorar hover de acciones para alternativas */
.service-item.alternativa .service-actions {
    opacity: 0.7;
}

.service-item.alternativa:hover .service-actions {
    opacity: 1;
}

/* Líneas de conexión más elaboradas */
.service-group::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 60px;
    bottom: 20px;
    width: 1px;
    background: linear-gradient(180deg, #2d5a4a 0%, #17a2b8 50%, #20c997 100%);
    z-index: 1;
}

/* Responsive para alternativas */
@media (max-width: 768px) {
    .service-item.alternativa {
        margin-left: 15px;
    }
    
    .alternative-connector {
        left: -15px;
        width: 15px;
    }
    
    .service-item.alternativa::before {
        left: -15px;
    }
    
    .service-actions {
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-add-alternative {
        padding: 3px 6px;
        font-size: 10px;
    }
}

/* Estados de carga para alternativas */
.loading-alternatives {
    padding: 10px;
    text-align: center;
    color: #666;
    font-style: italic;
    background: #f8f9fa;
}

.loading-alternatives .fas {
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

/* Efecto de aparición de alternativas */
.service-item.alternativa {
    animation: slideInAlternative 0.3s ease;
}

@keyframes slideInAlternative {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Hover effects mejorados */
.service-group:hover .service-item.principal .service-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(45, 90, 74, 0.3);
}

.service-item.alternativa:hover .service-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

/* Mejorar legibilidad de texto en alternativas */
.service-item.alternativa .service-details h6 {
    color: #2c3e50;
    font-weight: 600;
}

.service-item.alternativa .service-details p {
    color: #5a6c7d;
}



/* ============================================================
   ESTILOS PARA BARRA LATERAL DE DÍAS
   ============================================================ */

/* Contenedor principal de día a día */
.dias-layout {
    display: flex;
    gap: 20px;
    height: calc(100vh - 200px);
}

/* Barra lateral de días */
.days-sidebar {
    width: 280px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    max-height: 100%;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.add-day-btn {
    padding: 8px 16px;
    background: #2d5a4a;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.add-day-btn:hover {
    background: #234a3a;
    transform: translateY(-1px);
}

.days-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.day-sidebar-item {
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.day-sidebar-item:hover {
    background: #f8f9fa;
    border-color: #e0e0e0;
}

.day-sidebar-item.active {
    background: linear-gradient(135deg, #2d5a4a 0%, #4a7c59 100%);
    color: white;
    border-color: #2d5a4a;
}

.day-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.day-number-sidebar {
    font-weight: 600;
    font-size: 14px;
}

.day-actions-sidebar {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.day-sidebar-item:hover .day-actions-sidebar,
.day-sidebar-item.active .day-actions-sidebar {
    opacity: 1;
}

.day-action-btn {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    transition: all 0.2s;
}

.day-action-btn.edit {
    background: rgba(108, 117, 125, 0.2);
    color: #6c757d;
}

.day-action-btn.delete {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.day-sidebar-item.active .day-action-btn.edit {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.day-sidebar-item.active .day-action-btn.delete {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.day-action-btn:hover {
    transform: scale(1.1);
}

.day-item-title {
    font-size: 12px;
    margin-bottom: 4px;
    font-weight: 500;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.day-item-location {
    font-size: 10px;
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 4px;
}

.day-services-count {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(45, 90, 74, 0.9);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

.day-sidebar-item.active .day-services-count {
    background: rgba(255, 255, 255, 0.3);
}

/* Contenido del día seleccionado */
.day-detail-container {
    flex: 1;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    max-height: 100%;
}

.day-detail-header {
    padding: 24px;
    border-bottom: 1px solid #e0e0e0;
    background: linear-gradient(135deg, #2d5a4a 0%, #4a7c59 100%);
    color: white;
    border-radius: 12px 12px 0 0;
}

.day-detail-number {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 8px;
}

.day-detail-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 8px;
}

.day-detail-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    opacity: 0.9;
}

.day-detail-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

/* Estado vacío de sidebar */
.empty-sidebar {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-sidebar .fas {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.empty-sidebar h3 {
    font-size: 16px;
    margin-bottom: 8px;
    color: #333;
}

.empty-sidebar p {
    font-size: 14px;
    margin-bottom: 20px;
}

/* Estado vacío de detalle */
.empty-detail {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    color: #666;
}

.empty-detail .fas {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-detail h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.empty-detail p {
    font-size: 16px;
}

/* Responsivo */
@media (max-width: 1024px) {
    .dias-layout {
        flex-direction: column;
        height: auto;
    }
    
    .days-sidebar {
        width: 100%;
        max-height: 300px;
    }
    
    .days-list {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px;
    }
    
    .day-sidebar-item {
        min-width: 200px;
        flex-shrink: 0;
    }
}

@media (max-width: 768px) {
    .days-sidebar {
        max-height: 200px;
    }
    
    .day-sidebar-item {
        min-width: 160px;
    }
    
    .sidebar-title {
        font-size: 16px;
    }
    
    .add-day-btn {
        padding: 6px 12px;
        font-size: 11px;
    }
}



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
                    </div>
                    <div class="section-body">
                        <!-- NUEVO LAYOUT CON BARRA LATERAL -->
                        <div class="dias-layout">
                            <!-- Barra lateral de días -->
                            <div class="days-sidebar">
                                <div class="sidebar-header">
                                    <div class="sidebar-title">
                                        <i class="fas fa-list"></i>
                                        Días
                                    </div>
                                    <button class="add-day-btn" onclick="agregarDia()">
                                        <i class="fas fa-plus"></i>
                                        Agregar
                                    </button>
                                </div>
                                <div class="days-list" id="days-sidebar-list">
                                    <!-- Los días se cargarán aquí dinámicamente -->
                                    <div class="empty-sidebar">
                                        <i class="fas fa-calendar-plus"></i>
                                        <h3>No hay días</h3>
                                        <p>Agrega tu primer día</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido del día seleccionado -->
                            <div class="day-detail-container" id="day-detail-content">
                                <div class="empty-detail">
                                    <div>
                                        <i class="fas fa-calendar-day"></i>
                                        <h3>Selecciona un día</h3>
                                        <p>Elige un día de la lista para ver y editar sus detalles</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN NUEVO LAYOUT -->
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
        // ====================================================================
// SCRIPT JAVASCRIPT COMPLETO CORREGIDO PARA PROGRAMA.PHP
// ====================================================================

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
    console.log('🚀 Iniciando programa.php...');
    setupTabNavigation();
    setupFormHandling();
    setupPreviewUpdates();
    setupMealHandlers();
    
    if (isEditing && programaId) {
        console.log(`📋 Cargando datos para programa ID: ${programaId}`);
        cargarDiasPrograma();
        cargarPreciosPrograma();
    } else {
        console.log('💡 Programa nuevo - no hay días que cargar');
    }
});

// ============================================================
// GESTIÓN DE PESTAÑAS
// ============================================================
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

// ============================================================
// MANEJO DE FORMULARIOS
// ============================================================
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

// ============================================================
// FUNCIÓN PARA GUARDAR PROGRAMA
// ============================================================
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
                programaId = result.id;
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

// ============================================================
// FUNCIONES PARA GESTIÓN DE DÍAS
// ============================================================
async function cargarDiasPrograma() {
    if (!programaId) {
        console.log('❌ No hay programa ID para cargar días');
        return;
    }

    console.log(`📥 Cargando días para programa ${programaId}...`);

    try {
        const response = await fetch(`<?= APP_URL ?>/modules/programa/dias_api.php?action=list&programa_id=${programaId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        console.log('📋 Respuesta de días API:', result);

        if (result.success) {
            diasPrograma = result.data || [];
            console.log(`✅ ${diasPrograma.length} días cargados:`, diasPrograma);
            
            renderizarDias();
            
            // Cargar servicios para cada día
            for (const dia of diasPrograma) {
                console.log(`🔧 Cargando servicios para día ${dia.id}`);
                await cargarServiciosDia(dia.id);
            }
        } else {
            console.error('❌ Error en respuesta de días:', result.message);
            mostrarErrorDias(result.message || 'Error desconocido');
        }
    } catch (error) {
        console.error('❌ Error crítico cargando días:', error);
        mostrarErrorDias('Error de conexión: ' + error.message);
    }
}

function renderizarDias() {
    const container = document.getElementById('days-container');
    if (!container) {
        console.error('❌ No se encontró el contenedor days-container');
        return;
    }

    console.log(`🎨 Renderizando ${diasPrograma.length} días...`);

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

    // Ordenar días por dia_numero
    const diasOrdenados = [...diasPrograma].sort((a, b) => (a.dia_numero || 0) - (b.dia_numero || 0));

    container.innerHTML = diasOrdenados.map((dia, index) => {
        console.log(`🏗️ Renderizando día ${index + 1}:`, dia);
        
        const diaNumero = dia.dia_numero || (index + 1);
        const titulo = dia.titulo || 'Día sin título';
        const descripcion = dia.descripcion || '';
        const ubicacion = dia.ubicacion || 'Sin ubicación especificada';
        const fechaDia = dia.fecha_dia ? new Date(dia.fecha_dia).toLocaleDateString('es-ES') : null;

        return `
            <div class="day-card" data-dia-id="${dia.id}">
                <div class="day-header">
                    <div class="day-number">Día ${diaNumero}</div>
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
                    ${renderizarImagenesDia(dia)}
                    <div class="day-info">
                        <h4>${titulo}</h4>
                        <div class="day-description">
                            ${descripcion ? descripcion : '<em style="color: #999;">Sin descripción</em>'}
                        </div>
                        <div class="day-meta">
                            <span>
                                <i class="fas fa-map-marker-alt"></i> 
                                ${ubicacion}
                            </span>
                            ${fechaDia ? `
                                <span>
                                    <i class="fas fa-calendar"></i> 
                                    ${fechaDia}
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
                            <div class="loading-services">
                                <i class="fas fa-spinner fa-spin"></i> Cargando servicios...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    console.log('✅ Días renderizados exitosamente');
}

function renderizarImagenesDia(dia) {
    const imagenes = [dia.imagen1, dia.imagen2, dia.imagen3].filter(img => img && img.trim());
    
    if (imagenes.length === 0) {
        return ''; // Sin imágenes
    }

    let imagenesHtml = '<div class="day-images">';
    
    imagenes.forEach((imagen, index) => {
        const isMain = index === 0;
        imagenesHtml += `
            <div class="day-image ${isMain ? 'main' : ''}">
                <img src="${imagen}" alt="${dia.titulo || 'Imagen del día'}" loading="lazy" onerror="this.style.display='none'">
            </div>
        `;
    });
    
    imagenesHtml += '</div>';
    return imagenesHtml;
}

// Función para agregar día desde biblioteca
function agregarDia() {
    abrirModalBiblioteca();
}

async function abrirModalBiblioteca() {
    const modal = document.getElementById('bibliotecaModal');
    modal.style.display = 'block';
    
    await cargarDiasBiblioteca();
}

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

function editarDia(diaId) {
    // TODO: Implementar edición de días
    showAlert('Función de edición en desarrollo', 'info');
}

// ============================================================
// FUNCIONES PARA SERVICIOS
// ============================================================
function agregarServicio(diaId, tipoServicio) {
    currentDiaId = diaId;
    currentTipoServicio = tipoServicio;
    abrirModalServicios(tipoServicio);
}

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
        return servicio.imagen1 || null;
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

// ============================================================
// FUNCIÓN CORREGIDA PARA CARGAR SERVICIOS DE UN DÍA
// ============================================================
async function cargarServiciosDia(diaId) {
    console.log(`🔧 Cargando servicios para día ${diaId}...`);
    
    try {
        const response = await fetch(`<?= APP_URL ?>/modules/programa/servicios_api.php?action=list&dia_id=${diaId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        console.log(`📋 Servicios del día ${diaId}:`, result);

        if (result.success) {
            renderizarServiciosDia(diaId, result.data || []);
        } else {
            console.error(`❌ Error cargando servicios del día ${diaId}:`, result.message);
            mostrarErrorServicios(diaId, result.message);
        }
    } catch (error) {
        console.error(`❌ Error crítico cargando servicios del día ${diaId}:`, error);
        mostrarErrorServicios(diaId, 'Error de conexión: ' + error.message);
    }
}

function renderizarServiciosDia(diaId, servicios) {
    const container = document.getElementById(`services-${diaId}`);
    if (!container) {
        console.error(`❌ No se encontró contenedor de servicios para día ${diaId}`);
        return;
    }

    console.log(`🎨 Renderizando ${servicios.length} servicios para día ${diaId}`);

    if (servicios.length === 0) {
        container.innerHTML = `
            <p style="color: #666; font-style: italic; text-align: center; padding: 10px;">
                <i class="fas fa-info-circle"></i> No hay servicios agregados a este día
            </p>
        `;
        return;
    }

    // Ordenar servicios por orden
    const serviciosOrdenados = [...servicios].sort((a, b) => (a.orden || 0) - (b.orden || 0));

    container.innerHTML = `
        <h6 style="margin-bottom: 12px; color: #333; font-weight: 600;">
            <i class="fas fa-list"></i> Servicios agregados (${serviciosOrdenados.length}):
        </h6>
        ${serviciosOrdenados.map(servicio => `
            <div class="service-item" data-servicio-id="${servicio.id}">
                <div class="service-info">
                    <div class="service-icon ${servicio.tipo_servicio}">
                        <i class="fas fa-${getServiceIconByType(servicio.tipo_servicio)}"></i>
                    </div>
                    <div class="service-details">
                        <h6>${servicio.titulo || servicio.nombre || 'Servicio sin título'}</h6>
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

    console.log(`✅ Servicios renderizados para día ${diaId}`);
}

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
        const salida = servicio.lugar_salida || '';
        const llegada = servicio.lugar_llegada || '';
        const medio = servicio.medio ? `${servicio.medio} - ` : '';
        return `${medio}${salida} → ${llegada}`;
    }
    
    if (servicio.descripcion) {
        return servicio.descripcion.length > 80 ? 
            servicio.descripcion.substring(0, 80) + '...' : 
            servicio.descripcion;
    }
    
    return 'Sin descripción disponible';
}

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

function editarServicio(servicioId) {
    // TODO: Implementar edición de servicios
    showAlert('Función de edición en desarrollo', 'info');
}

// ============================================================
// FUNCIONES DE MANEJO DE ERRORES
// ============================================================
function mostrarErrorDias(mensaje) {
    const container = document.getElementById('days-container');
    if (container) {
        container.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error al cargar días</h3>
                <p>${mensaje}</p>
                <button class="btn btn-primary" onclick="cargarDiasPrograma()">
                    <i class="fas fa-redo"></i>
                    Reintentar
                </button>
            </div>
        `;
    }
}

function mostrarErrorServicios(diaId, mensaje) {
    const container = document.getElementById(`services-${diaId}`);
    if (container) {
        container.innerHTML = `
            <div style="color: #dc3545; text-align: center; padding: 10px; font-size: 14px;">
                <i class="fas fa-exclamation-triangle"></i>
                Error: ${mensaje}
                <br>
                <button class="btn btn-outline" style="margin-top: 8px; font-size: 12px;" onclick="cargarServiciosDia(${diaId})">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// ============================================================
// FUNCIONES PARA PRECIOS
// ============================================================
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

// ============================================================
// FUNCIONES PARA VISTA PREVIA
// ============================================================
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

// ============================================================
// FUNCIONES AUXILIARES
// ============================================================
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

console.log('✅ Script de programa.php cargado completamente');

// ============================================================
// JAVASCRIPT PARA BARRA LATERAL DE DÍAS
// ============================================================

let selectedDayId = null;

// Función modificada para renderizar días en sidebar
function renderizarDias() {
    console.log(`🎨 Renderizando ${diasPrograma.length} días en sidebar...`);
    
    renderizarSidebarDias();
    renderizarDetalleVacio();
}

function renderizarSidebarDias() {
    const sidebarContainer = document.getElementById('days-sidebar-list');
    if (!sidebarContainer) {
        console.error('❌ No se encontró el contenedor days-sidebar-list');
        return;
    }

    if (diasPrograma.length === 0) {
        sidebarContainer.innerHTML = `
            <div class="empty-sidebar">
                <i class="fas fa-calendar-plus"></i>
                <h3>No hay días</h3>
                <p>Agrega tu primer día</p>
                <button class="btn btn-primary" onclick="agregarDia()">
                    <i class="fas fa-plus"></i>
                    Agregar día
                </button>
            </div>
        `;
        return;
    }

    // Ordenar días por dia_numero
    const diasOrdenados = [...diasPrograma].sort((a, b) => (a.dia_numero || 0) - (b.dia_numero || 0));

    sidebarContainer.innerHTML = diasOrdenados.map((dia, index) => {
        const diaNumero = dia.dia_numero || (index + 1);
        const titulo = dia.titulo || 'Día sin título';
        const ubicacion = dia.ubicacion || 'Sin ubicación';
        
        return `
            <div class="day-sidebar-item ${selectedDayId === dia.id ? 'active' : ''}" 
                 data-dia-id="${dia.id}" 
                 onclick="seleccionarDiaEnSidebar(${dia.id})">
                <div class="day-services-count" id="services-count-${dia.id}">0</div>
                <div class="day-item-header">
                    <div class="day-number-sidebar">Día ${diaNumero}</div>
                    <div class="day-actions-sidebar">
                        <button class="day-action-btn edit" onclick="event.stopPropagation(); editarDia(${dia.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="day-action-btn delete" onclick="event.stopPropagation(); eliminarDia(${dia.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="day-item-title">${titulo}</div>
                <div class="day-item-location">
                    <i class="fas fa-map-marker-alt"></i>
                    ${ubicacion}
                </div>
            </div>
        `;
    }).join('');

    // Cargar servicios para actualizar contadores
    diasOrdenados.forEach(dia => {
        cargarServiciosParaContador(dia.id);
    });

    // Seleccionar primer día si no hay ninguno seleccionado
    if (!selectedDayId && diasOrdenados.length > 0) {
        seleccionarDiaEnSidebar(diasOrdenados[0].id);
    }
}

function seleccionarDiaEnSidebar(diaId) {
    console.log(`📌 Seleccionando día ${diaId} en sidebar`);
    
    // Remover clase active de todos los items
    document.querySelectorAll('.day-sidebar-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Agregar clase active al item seleccionado
    const selectedItem = document.querySelector(`[data-dia-id="${diaId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    
    selectedDayId = diaId;
    
    // Renderizar detalle del día seleccionado
    renderizarDetalleDia(diaId);
    
    // Cargar servicios del día seleccionado
    cargarServiciosDia(diaId);
}

function renderizarDetalleDia(diaId) {
    const detailContainer = document.getElementById('day-detail-content');
    if (!detailContainer) {
        console.error('❌ No se encontró el contenedor day-detail-content');
        return;
    }

    const dia = diasPrograma.find(d => d.id == diaId);
    if (!dia) {
        console.error(`❌ No se encontró el día con ID ${diaId}`);
        return;
    }

    const diaNumero = dia.dia_numero || 1;
    const titulo = dia.titulo || 'Día sin título';
    const descripcion = dia.descripcion || '';
    const ubicacion = dia.ubicacion || 'Sin ubicación especificada';
    const fechaDia = dia.fecha_dia ? new Date(dia.fecha_dia).toLocaleDateString('es-ES') : null;

    detailContainer.innerHTML = `
        <div class="day-detail-header">
            <div class="day-detail-number">Día ${diaNumero}</div>
            <div class="day-detail-title">${titulo}</div>
            <div class="day-detail-meta">
                <span>
                    <i class="fas fa-map-marker-alt"></i> 
                    ${ubicacion}
                </span>
                ${fechaDia ? `
                    <span>
                        <i class="fas fa-calendar"></i> 
                        ${fechaDia}
                    </span>
                ` : ''}
            </div>
        </div>
        
        <div class="day-detail-body">
            ${renderizarImagenesDia(dia)}
            
            ${descripcion ? `
                <div class="day-description" style="margin-bottom: 20px; color: #666; line-height: 1.6;">
                    ${descripcion}
                </div>
            ` : ''}
            
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
                    <div class="loading-services">
                        <i class="fas fa-spinner fa-spin"></i> Cargando servicios...
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderizarDetalleVacio() {
    const detailContainer = document.getElementById('day-detail-content');
    if (!detailContainer) return;

    detailContainer.innerHTML = `
        <div class="empty-detail">
            <div>
                <i class="fas fa-calendar-day"></i>
                <h3>Selecciona un día</h3>
                <p>Elige un día de la lista para ver y editar sus detalles</p>
            </div>
        </div>
    `;
}

// Función para cargar servicios solo para contador
async function cargarServiciosParaContador(diaId) {
    try {
        const response = await fetch(`<?= APP_URL ?>/modules/programa/servicios_api.php?action=list&dia_id=${diaId}`);
        const result = await response.json();

        if (result.success) {
            const count = result.data ? result.data.length : 0;
            const countElement = document.getElementById(`services-count-${diaId}`);
            if (countElement) {
                countElement.textContent = count;
                countElement.style.display = count > 0 ? 'block' : 'none';
            }
        }
    } catch (error) {
        console.error(`Error cargando contador de servicios para día ${diaId}:`, error);
    }
}

// Función modificada para actualizar contador después de agregar/eliminar servicios
function actualizarContadorServicios(diaId, count) {
    const countElement = document.getElementById(`services-count-${diaId}`);
    if (countElement) {
        countElement.textContent = count;
        countElement.style.display = count > 0 ? 'block' : 'none';
    }
}

// Modificar función de renderizar servicios para actualizar contador
function renderizarServiciosDia(diaId, servicios) {
    const container = document.getElementById(`services-${diaId}`);
    if (!container) {
        console.error(`❌ No se encontró contenedor de servicios para día ${diaId}`);
        return;
    }

    console.log(`🎨 Renderizando ${servicios.length} servicios para día ${diaId}`);

    // Actualizar contador en sidebar
    actualizarContadorServicios(diaId, servicios.length);

    if (servicios.length === 0) {
        container.innerHTML = `
            <p style="color: #666; font-style: italic; text-align: center; padding: 10px;">
                <i class="fas fa-info-circle"></i> No hay servicios agregados a este día
            </p>
        `;
        return;
    }

    // Ordenar servicios por orden
    const serviciosOrdenados = [...servicios].sort((a, b) => (a.orden || 0) - (b.orden || 0));

    container.innerHTML = `
        <h6 style="margin-bottom: 12px; color: #333; font-weight: 600;">
            <i class="fas fa-list"></i> Servicios agregados (${serviciosOrdenados.length}):
        </h6>
        ${serviciosOrdenados.map(servicio => `
            <div class="service-item" data-servicio-id="${servicio.id}">
                <div class="service-info">
                    <div class="service-icon ${servicio.tipo_servicio}">
                        <i class="fas fa-${getServiceIconByType(servicio.tipo_servicio)}"></i>
                    </div>
                    <div class="service-details">
                        <h6>${servicio.titulo || servicio.nombre || 'Servicio sin título'}</h6>
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

    console.log(`✅ Servicios renderizados para día ${diaId}`);
}

// Modificar función de eliminar servicio para actualizar contador
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
            // Recargar servicios del día seleccionado
            if (selectedDayId) {
                cargarServiciosDia(selectedDayId);
                cargarServiciosParaContador(selectedDayId);
            }
        } else {
            showAlert(result.message || 'Error al eliminar servicio', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    }
}

console.log('✅ Script de sidebar de días cargado');



// ============================================================
// JAVASCRIPT COMPLETO PARA ALTERNATIVAS - AGREGAR A programa.php
// ============================================================
// Agregar estas funciones a tu script existente

// Variables globales adicionales para alternativas
let currentServicioPrincipal = null;

// Función modificada para renderizar servicios CON alternativas
function renderizarServiciosDia(diaId, servicios) {
    const container = document.getElementById(`services-${diaId}`);
    if (!container) {
        console.error(`❌ No se encontró contenedor de servicios para día ${diaId}`);
        return;
    }

    console.log(`🎨 Renderizando ${servicios.length} servicios CON ALTERNATIVAS para día ${diaId}`);

    // Actualizar contador en sidebar (solo contar principales)
    const principalesCount = servicios.length;
    actualizarContadorServicios(diaId, principalesCount);

    if (servicios.length === 0) {
        container.innerHTML = `
            <p style="color: #666; font-style: italic; text-align: center; padding: 10px;">
                <i class="fas fa-info-circle"></i> No hay servicios agregados a este día
            </p>
        `;
        return;
    }

    // Renderizar servicios principales con sus alternativas
    container.innerHTML = `
        <h6 style="margin-bottom: 12px; color: #333; font-weight: 600;">
            <i class="fas fa-list"></i> Servicios agregados (${principalesCount}):
        </h6>
        ${servicios.map(servicio => renderizarServicioConAlternativas(servicio)).join('')}
    `;

    console.log(`✅ Servicios con alternativas renderizados para día ${diaId}`);
}

function renderizarServicioConAlternativas(servicio) {
    const alternativas = servicio.alternativas || [];
    const hasAlternatives = alternativas.length > 0;
    
    return `
        <div class="service-group" data-servicio-id="${servicio.id}">
            <!-- Servicio Principal -->
            <div class="service-item principal">
                <div class="service-info">
                    <div class="service-icon ${servicio.tipo_servicio}">
                        <i class="fas fa-${getServiceIconByType(servicio.tipo_servicio)}"></i>
                    </div>
                    <div class="service-details">
                        <h6>
                            <i class="fas fa-star" style="color: #ffc107; font-size: 12px; margin-right: 4px;" title="Principal"></i>
                            ${servicio.titulo || servicio.nombre || 'Servicio sin título'}
                            ${hasAlternatives ? `<span class="alternatives-indicator">${alternativas.length} alt</span>` : ''}
                        </h6>
                        <p>${getServiceSummary(servicio)}</p>
                    </div>
                </div>
                <div class="service-actions">
                    <button class="btn-add-alternative" onclick="abrirModalAlternativa(${servicio.id}, '${servicio.tipo_servicio}')" title="Agregar alternativa">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                    <button class="btn-edit-service" onclick="editarServicio(${servicio.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-remove-service" onclick="eliminarServicio(${servicio.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <!-- Alternativas -->
            ${hasAlternatives ? `
                <div class="alternatives-container">
                    ${alternativas.map(alt => renderizarAlternativa(alt)).join('')}
                </div>
            ` : ''}
        </div>
    `;
}

function renderizarAlternativa(alternativa) {
    return `
        <div class="service-item alternativa" data-alternativa-id="${alternativa.id}">
            <div class="alternative-connector"></div>
            <div class="service-info">
                <div class="service-icon ${alternativa.tipo_servicio} alternativa">
                    <i class="fas fa-${getServiceIconByType(alternativa.tipo_servicio)}"></i>
                </div>
                <div class="service-details">
                    <h6>
                        <i class="fas fa-sync-alt" style="color: #17a2b8; font-size: 12px; margin-right: 4px;" title="Alternativa"></i>
                        Alternativa ${alternativa.orden_alternativa}: ${alternativa.titulo || alternativa.nombre || 'Sin título'}
                    </h6>
                    <p>${getServiceSummary(alternativa)}</p>
                    ${alternativa.notas_alternativa ? `
                        <div style="font-size: 11px; color: #6c757d; margin-top: 4px;">
                            <i class="fas fa-sticky-note"></i> ${alternativa.notas_alternativa}
                        </div>
                    ` : ''}
                </div>
            </div>
            <div class="service-actions">
                <button class="btn-edit-service" onclick="editarAlternativa(${alternativa.id})" title="Editar alternativa">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-remove-service" onclick="eliminarAlternativa(${alternativa.id})" title="Eliminar alternativa">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
}

// Función para abrir modal de alternativas
async function abrirModalAlternativa(servicioPrincipalId, tipoServicio) {
    console.log(`🔄 Abriendo modal de alternativas para servicio ${servicioPrincipalId} tipo ${tipoServicio}`);
    
    // Guardar referencias globales
    currentServicioPrincipal = servicioPrincipalId;
    currentTipoServicio = tipoServicio;
    
    const modal = document.getElementById('serviciosModal');
    const title = document.getElementById('servicios-modal-title');
    
    // Actualizar título según el tipo
    const icons = {
        'actividad': 'fas fa-hiking',
        'transporte': 'fas fa-car',
        'alojamiento': 'fas fa-bed'
    };
    
    const titles = {
        'actividad': 'Agregar Alternativa de Actividad',
        'transporte': 'Agregar Alternativa de Transporte',
        'alojamiento': 'Agregar Alternativa de Alojamiento'
    };
    
    title.innerHTML = `<i class="${icons[tipoServicio]}"></i> ${titles[tipoServicio]}`;
    
    // Cambiar texto del botón
    const btnAgregar = document.getElementById('btn-agregar-servicio');
    if (btnAgregar) {
        btnAgregar.innerHTML = '<i class="fas fa-plus"></i> Agregar alternativa';
    }
    
    modal.style.display = 'block';
    
    await cargarServiciosBiblioteca(tipoServicio);
}

// Función para agregar alternativa seleccionada
async function agregarAlternativaSeleccionada() {
    if (!selectedServicioId || !currentServicioPrincipal) {
        console.error('❌ Datos faltantes para agregar alternativa');
        return;
    }

    try {
        console.log(`🔄 Agregando alternativa: Principal=${currentServicioPrincipal}, Item=${selectedServicioId}`);
        
        const response = await fetch('<?= APP_URL ?>/modules/programa/servicios_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_alternative',
                servicio_principal_id: currentServicioPrincipal,
                biblioteca_item_id: selectedServicioId
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert('Alternativa agregada exitosamente', 'success');
            cerrarModalServicios();
            // Recargar servicios del día seleccionado
            if (selectedDayId) {
                cargarServiciosDia(selectedDayId);
                cargarServiciosParaContador(selectedDayId);
            }
        } else {
            showAlert(result.message || 'Error al agregar alternativa', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    }
}

// Función para eliminar alternativa
async function eliminarAlternativa(alternativaId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta alternativa?')) return;

    try {
        const response = await fetch('<?= APP_URL ?>/modules/programa/servicios_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                servicio_id: alternativaId
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert('Alternativa eliminada exitosamente', 'success');
            // Recargar servicios del día seleccionado
            if (selectedDayId) {
                cargarServiciosDia(selectedDayId);
                cargarServiciosParaContador(selectedDayId);
            }
        } else {
            showAlert(result.message || 'Error al eliminar alternativa', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    }
}

// Función para editar alternativa
function editarAlternativa(alternativaId) {
    // TODO: Implementar edición de alternativas
    showAlert('Función de edición de alternativas en desarrollo', 'info');
}

// Modificar función de agregar servicio seleccionado para detectar si es alternativa
async function agregarServicioSeleccionado() {
    // Si hay servicio principal seleccionado, es una alternativa
    if (currentServicioPrincipal) {
        await agregarAlternativaSeleccionada();
    } else {
        // Es un servicio principal normal
        await agregarServicioPrincipalSeleccionado();
    }
}

async function agregarServicioPrincipalSeleccionado() {
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

// Modificar función de cerrar modal para limpiar variables de alternativas
function cerrarModalServicios() {
    const modal = document.getElementById('serviciosModal');
    modal.style.display = 'none';
    selectedServicioId = null;
    currentDiaId = null;
    currentTipoServicio = null;
    currentServicioPrincipal = null; // Limpiar referencia de alternativa
    
    const btnAgregar = document.getElementById('btn-agregar-servicio');
    if (btnAgregar) {
        btnAgregar.disabled = true;
        // Restaurar texto del botón
        btnAgregar.innerHTML = '<i class="fas fa-plus"></i> Agregar servicio';
    }
    
    // Limpiar búsqueda
    const searchInput = document.getElementById('search-servicios');
    if (searchInput) {
        searchInput.value = '';
    }
}

// Función modificada para eliminar servicio (principal + alternativas)
async function eliminarServicio(servicioId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este servicio y todas sus alternativas?')) return;

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
            // Recargar servicios del día seleccionado
            if (selectedDayId) {
                cargarServiciosDia(selectedDayId);
                cargarServiciosParaContador(selectedDayId);
            }
        } else {
            showAlert(result.message || 'Error al eliminar servicio', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    }
}

// Función para expandir/contraer alternativas (opcional)
function toggleAlternativas(servicioId) {
    const serviceGroup = document.querySelector(`[data-servicio-id="${servicioId}"]`);
    if (serviceGroup) {
        serviceGroup.classList.toggle('expanded');
    }
}

// Función para contar total de servicios incluyendo alternativas
function contarTotalServicios(servicios) {
    let total = servicios.length; // Principales
    servicios.forEach(servicio => {
        if (servicio.alternativas) {
            total += servicio.alternativas.length;
        }
    });
    return total;
}

// Función para obtener estadísticas de servicios
function getEstadisticasServicios(servicios) {
    const stats = {
        principales: servicios.length,
        alternativas: 0,
        total: servicios.length
    };
    
    servicios.forEach(servicio => {
        if (servicio.alternativas) {
            stats.alternativas += servicio.alternativas.length;
            stats.total += servicio.alternativas.length;
        }
    });
    
    return stats;
}

// Función de utilidad para verificar si un servicio tiene alternativas
function tieneAlternativas(servicio) {
    return servicio.alternativas && servicio.alternativas.length > 0;
}

// Función para buscar un servicio específico (principal o alternativa)
function buscarServicioPorId(servicios, id) {
    for (const servicio of servicios) {
        if (servicio.id == id) {
            return { tipo: 'principal', servicio: servicio };
        }
        
        if (servicio.alternativas) {
            for (const alt of servicio.alternativas) {
                if (alt.id == id) {
                    return { tipo: 'alternativa', servicio: alt, principal: servicio };
                }
            }
        }
    }
    return null;
}

// Función para reordenar alternativas dentro de un servicio principal
async function reordenarAlternativas(servicioPrincipalId, nuevoOrden) {
    try {
        const response = await fetch('<?= APP_URL ?>/modules/programa/servicios_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'reorder_alternatives',
                servicio_principal_id: servicioPrincipalId,
                orden: nuevoOrden
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert('Orden de alternativas actualizado', 'success');
            // Recargar servicios
            if (selectedDayId) {
                cargarServiciosDia(selectedDayId);
            }
        } else {
            showAlert(result.message || 'Error al reordenar alternativas', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    }
}

// Eventos para drag & drop de alternativas (opcional - futuro)
function initDragAndDropAlternativas() {
    // TODO: Implementar drag & drop para reordenar alternativas
    console.log('💡 Drag & drop de alternativas - funcionalidad futura');
}

console.log('✅ Script completo de alternativas cargado');
console.log('🔧 Funciones disponibles:');
console.log('   - abrirModalAlternativa()');
console.log('   - agregarAlternativaSeleccionada()');
console.log('   - eliminarAlternativa()');
console.log('   - renderizarServicioConAlternativas()');
console.log('   - toggleAlternativas()');
console.log('   - getEstadisticasServicios()');
console.log('   - buscarServicioPorId()');
console.log('   - reordenarAlternativas()');



    </script>
</body>
</html>