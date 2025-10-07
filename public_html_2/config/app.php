<?php
// =====================================
// ARCHIVO: config/app.php - Configuración Principal para Hosting
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
        // Cargar variables de entorno desde .env
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $lines = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }

        // ✅ DEFINIR CONSTANTES DINÁMICAMENTE
        if (!defined('APP_NAME')) {
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
            // Detectar automáticamente la URL base
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Para hosting, usar la URL del .env si existe, sino detectar automáticamente
            $appUrl = $_ENV['APP_URL'] ?? ($protocol . '://' . $host);
            
            define('APP_URL', rtrim($appUrl, '/'));
            define('APP_PATH', '/');
        }
        
        if (!defined('APP_DEBUG')) {
            define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
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

    // ===== MÉTODOS PARA CONFIGURACIÓN =====
    
    public static function getLoginColors() {
        try {
            ConfigManager::init();
            return ConfigManager::getLoginColors();
        } catch(Exception $e) {
            return [
                'primary' => '#667eea',
                'secondary' => '#764ba2'
            ];
        }
    }

    public static function getCompanyName() {
        try {
            ConfigManager::init();
            return ConfigManager::getCompanyName();
        } catch(Exception $e) {
            return 'Travel Agency';
        }
    }

    public static function getLogo() {
        try {
            ConfigManager::init();
            return ConfigManager::getLogo();
        } catch(Exception $e) {
            return '';
        }
    }

    public static function getDefaultLanguage() {
        try {
            ConfigManager::init();
            return ConfigManager::getDefaultLanguage();
        } catch(Exception $e) {
            return 'es';
        }
    }

    public static function getColorsForRole($role) {
        try {
            ConfigManager::init();
            return ConfigManager::getColorsForRole($role);
        } catch(Exception $e) {
            if ($role === 'admin') {
                return [
                    'primary' => '#e53e3e',
                    'secondary' => '#fd746c'
                ];
            } else {
                return [
                    'primary' => '#667eea',
                    'secondary' => '#764ba2'
                ];
            }
        }
    }

    public static function getSessionTimeout() {
        try {
            ConfigManager::init();
            return ConfigManager::getSessionTimeout();
        } catch(Exception $e) {
            return 60;
        }
    }

    public static function getConfig($key = null) {
        try {
            ConfigManager::init();
            return ConfigManager::get($key);
        } catch(Exception $e) {
            return $key ? null : [];
        }
    }
}