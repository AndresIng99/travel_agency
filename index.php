<?php
// =====================================
// ARCHIVO: index.php - VERSIÓN COMPLETA CON RUTAS DE ITINERARIOS
// =====================================

require_once 'config/database.php';
require_once 'config/app.php';

App::init();

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace(rtrim(parse_url(APP_URL, PHP_URL_PATH), '/'), '', $path);
$path = $path ?: '/';

switch($path) {
    case '/':
    case '/login':
        if (App::isLoggedIn()) {
            App::redirect('/dashboard');
        }
        include 'pages/login.php';
        break;
        
    case '/auth/login':
        include 'auth/login.php';
        break;
        
    case '/auth/logout':
        include 'auth/logout.php';
        break;
        
    case '/dashboard':
        App::requireLogin();
        $user = App::getUser();
        
        // Redirigir automáticamente según el rol si se accede por primera vez
        if (isset($_GET['redirect'])) {
            if ($user['role'] === 'admin') {
                App::redirect('/administrador');
            } else {
                // Los agentes van al dashboard normal
                include 'pages/dashboard.php';
            }
        } else {
            include 'pages/dashboard.php';
        }
        break;
        
    // ===== RUTAS DE BIBLIOTECA =====
    case '/biblioteca':
        App::requireLogin();
        include 'pages/biblioteca.php';
        break;
        
    case '/biblioteca/api':
        App::requireLogin();
        include 'modules/biblioteca/api.php';
        break;
        
    // ===== RUTAS DE MI PROGRAMA =====
    case '/programa':
        App::requireLogin();
        include 'pages/programa.php';
        break;
        
    case '/programa/api':
        App::requireLogin();
        include 'modules/programa/api.php';
        break;
        
    // ===== RUTAS DE ITINERARIOS =====
    case '/itinerarios':
        App::requireLogin();
        include 'pages/itinerarios.php';
        break;
        
    // Ruta específica para itinerarios con ID
    case (preg_match('/^\/itinerarios\/(\d+)$/', $path, $matches) ? true : false):
        App::requireLogin();
        $_GET['id'] = $matches[1];
        include 'pages/itinerarios.php';
        break;
        
    // ===== RUTAS DE ADMINISTRADOR =====
    case '/administrador':
    case '/administrador/usuarios':
        App::requireRole('admin');
        include 'pages/admin.php';
        break;
        
    case '/administrador/configuracion':
        App::requireRole('admin');
        include 'pages/admin_config.php';
        break;

    case '/admin/api':
        App::requireRole('admin');
        include 'modules/admin/api.php';
        break;
        
    

    case '/preview':  // ← NUEVA RUTA
        App::requireLogin();
        require_once 'pages/preview.php';
        break;

    case '/itinerary':
        // Vista completa del itinerario estético (público/compartible)
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Location: ' . APP_URL . '/itinerarios');
            exit;
        }
        require_once __DIR__ . '/pages/itinerary.php';
        break;
        
    case '/perfil':
        App::requireLogin();
        include 'pages/perfil.php';
        break;
        
    // ===== REDIRECCIONES COMPATIBILIDAD =====
    // Redireccionar rutas antiguas o alternativas a las nuevas
    case '/itinerario':
    case '/mis-itinerarios':
    case '/viajes':
        App::redirect('/itinerarios');
        break;
        
    case '/mi-programa':
        App::redirect('/programa');
        break;
        
    case '/biblioteca-destinos':
    case '/destinos':
        App::redirect('/biblioteca');
        break;
        
    // ===== PÁGINA 404 =====
    default:
        http_response_code(404);
        include 'pages/404.php';
        break;
}