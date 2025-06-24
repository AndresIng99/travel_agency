<?php
class App {
    public static function init() {
        self::loadConfig();
        self::startSession();
        self::setTimezone();
    }

    private static function loadConfig() {
        if (!isset($_ENV['APP_NAME'])) {
            Database::getInstance();
        }

        define('APP_NAME', $_ENV['APP_NAME'] ?? 'Travel Agency');
        define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/travel_agency');
        define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');
        define('BASE_PATH', dirname(__DIR__));
    }

    private static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function setTimezone() {
        date_default_timezone_set('America/Bogota');
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
    }

    public static function requireRole($role) {
        self::requireLogin();
        if (!self::hasRole($role)) {
            self::redirect('/dashboard');
        }
    }
}
?>