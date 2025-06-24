<?php
// =====================================
// ARCHIVO: index.php - VERSIÓN COMPLETA CON TODAS LAS RUTAS
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
        
    // ===== RUTAS FUTURAS (para próximas funcionalidades) =====
    case '/reportes':
        App::requireLogin();
        include 'pages/reportes.php';
        break;
        
    case '/perfil':
        App::requireLogin();
        include 'pages/perfil.php';
        break;
        
    // ===== PÁGINA 404 =====
    default:
        http_response_code(404);
        include 'pages/404.php';
        break;
}