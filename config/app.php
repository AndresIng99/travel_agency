<?php
// =====================================
// ARCHIVO: config/app.php - Configuración Principal Actualizada
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
        define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/travel_agency');
        define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');
        define('BASE_PATH', dirname(__DIR__));
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

    public static function redirect($path) {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function getUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role'],
                'name' => $_SESSION['user_name'] ?? $_SESSION['username']
            ];
        }
        return null;
    }

    public static function hasRole($role) {
        return self::isLoggedIn() && $_SESSION['user_role'] === $role;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            self::redirect('/login');
        }
        
        // Verificar modo mantenimiento
        self::checkMaintenanceMode();
    }

    public static function requireRole($role) {
        self::requireLogin();
        if (!self::hasRole($role)) {
            self::redirect('/dashboard');
        }
    }
    
    private static function checkMaintenanceMode() {
        try {
            $config = ConfigManager::get();
            if ($config['maintenance_mode'] && !self::hasRole('admin')) {
                // Mostrar página de mantenimiento
                self::showMaintenancePage();
            }
        } catch(Exception $e) {
            // Si hay error, continuar normalmente
        }
    }
    
    private static function showMaintenancePage() {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Mantenimiento - <?= APP_NAME ?></title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    color: white;
                    text-align: center;
                }
                .maintenance-container {
                    background: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                }
                .maintenance-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    margin-bottom: 20px;
                }
                .logout-btn {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    padding: 10px 20px;
                    border-radius: 25px;
                    text-decoration: none;
                    margin-top: 20px;
                    display: inline-block;
                }
            </style>
        </head>
        <body>
            <div class="maintenance-container">
                <div class="maintenance-icon">🔧</div>
                <h1>Sistema en Mantenimiento</h1>
                <p>Estamos realizando mejoras en el sistema. Por favor, inténtalo más tarde.</p>
                <a href="<?= APP_URL ?>/auth/logout" class="logout-btn">Cerrar Sesión</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    // Métodos para obtener configuración de temas
    public static function getThemeCSS($role = null) {
        try {
            return ConfigManager::generateCSS($role);
        } catch(Exception $e) {
            return '';
        }
    }
    
    public static function getCompanyName() {
        try {
            return ConfigManager::getCompanyName();
        } catch(Exception $e) {
            return APP_NAME;
        }
    }
    
    public static function getLogo() {
        try {
            return ConfigManager::getLogo();
        } catch(Exception $e) {
            return '';
        }
    }
    
    public static function getDefaultLanguage() {
        try {
            return ConfigManager::getDefaultLanguage();
        } catch(Exception $e) {
            return 'es';
        }
    }
    
    public static function getColorsForRole($role) {
        try {
            return ConfigManager::getColorsForRole($role);
        } catch(Exception $e) {
            // Valores por defecto
            if ($role === 'admin') {
                return ['primary' => '#e53e3e', 'secondary' => '#fd746c'];
            } else {
                return ['primary' => '#667eea', 'secondary' => '#764ba2'];
            }
        }
    }
    
    public static function getLoginColors() {
        try {
            return ConfigManager::getLoginColors();
        } catch(Exception $e) {
            return ['primary' => '#667eea', 'secondary' => '#764ba2'];
        }
    }
}
?>