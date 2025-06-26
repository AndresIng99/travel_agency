<?php
// =====================================
// ARCHIVO: config/app.php - Configuración Principal Corregida
// =====================================

require_once 'config_functions.php';

class App {
    public static function init() {
        self::loadConfig();
        self::startSession();
        self::setTimezone();
        self::initializeConfigManager();
    }

    private static function loadConfig() {
        // ✅ SOLO DEFINIR CONSTANTES SI NO EXISTEN
        if (!defined('APP_NAME')) {
            if (!isset($_ENV['APP_NAME'])) {
                Database::getInstance();
            }

            // Usar ConfigManager para obtener el nombre de la empresa
            $companyName = 'Travel Agency';
            try {
                ConfigManager::init();
                $companyName = ConfigManager::getCompanyName();
            } catch(Exception $e) {
                // Si hay error, usar valor por defecto
            }

            define('APP_NAME', $_ENV['APP_NAME'] ?? $companyName);
        }
        
        if (!defined('APP_URL')) {
            define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/travel_agency');
        }
        
        if (!defined('APP_DEBUG')) {
            define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');
        }
        
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }
    }

    private static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar timeout de sesión desde la configuración
            try {
                $sessionTimeout = ConfigManager::getSessionTimeout();
                ini_set('session.gc_maxlifetime', $sessionTimeout * 60);
                session_set_cookie_params($sessionTimeout * 60);
            } catch(Exception $e) {
                // Usar valor por defecto
                ini_set('session.gc_maxlifetime', 3600); // 60 minutos
                session_set_cookie_params(3600);
            }
            
            session_start();
            
            // Verificar timeout de sesión
            self::checkSessionTimeout();
        }
    }
    
    private static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $sessionTimeout = 3600; // Default 1 hour
            try {
                $sessionTimeout = ConfigManager::getSessionTimeout() * 60;
            } catch(Exception $e) {
                // Usar valor por defecto
            }
            
            if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
                // Sesión expirada
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['session_expired'] = true;
            }
        }
        
        $_SESSION['last_activity'] = time();
    }

    private static function setTimezone() {
        date_default_timezone_set('America/Bogota');
    }
    
    private static function initializeConfigManager() {
        try {
            ConfigManager::init();
        } catch(Exception $e) {
            error_log("Error initializing ConfigManager: " . $e->getMessage());
        }
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            self::redirect('/login');
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'agent'
        ];
    }

    public static function redirect($path) {
        $url = APP_URL . $path;
        header("Location: $url");
        exit();
    }

    public static function getCurrentPath() {
        $request = $_SERVER['REQUEST_URI'];
        $path = parse_url($request, PHP_URL_PATH);
        return str_replace(rtrim(parse_url(APP_URL, PHP_URL_PATH), '/'), '', $path) ?: '/';
    }

    public static function asset($path) {
        return APP_URL . '/assets/' . ltrim($path, '/');
    }

    public static function url($path) {
        return APP_URL . '/' . ltrim($path, '/');
    }

    public static function requireRole($role) {
        self::requireLogin();
        $user = self::getUser();
        if ($user['role'] !== $role) {
            self::redirect('/dashboard');
        }
    }
}

   