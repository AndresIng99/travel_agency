<?php
// =====================================
// ARCHIVO: config/config_functions.php - Funciones de Configuración Mejoradas
// =====================================

class ConfigManager {
    private static $config = null;
    private static $db = null;
    
    public static function init() {
        try {
            self::$db = Database::getInstance();
            self::loadConfig();
        } catch(Exception $e) {
            error_log("ConfigManager init error: " . $e->getMessage());
            self::$config = self::getDefaultConfig();
        }
    }
    
    private static function loadConfig() {
        try {
            // Verificar si la tabla existe
            $tableExists = self::$db->fetch("SHOW TABLES LIKE 'company_settings'");
            
            if (!$tableExists) {
                self::createDefaultConfig();
            }
            
            self::$config = self::$db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            
            if (!self::$config) {
                self::createDefaultConfig();
                self::$config = self::$db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            }
        } catch(Exception $e) {
            error_log("Error loading config: " . $e->getMessage());
            self::$config = self::getDefaultConfig();
        }
    }
    
    private static function createDefaultConfig() {
        try {
            // Crear tabla si no existe
            $sql = "CREATE TABLE IF NOT EXISTS `company_settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(100) DEFAULT 'Travel Agency',
                `logo_url` VARCHAR(255) NULL,
                `background_image` VARCHAR(255) NULL,
                `admin_primary_color` VARCHAR(7) DEFAULT '#e53e3e',
                `admin_secondary_color` VARCHAR(7) DEFAULT '#fd746c',
                `agent_primary_color` VARCHAR(7) DEFAULT '#667eea',
                `agent_secondary_color` VARCHAR(7) DEFAULT '#764ba2',
                `login_bg_color` VARCHAR(7) DEFAULT '#667eea',
                `login_secondary_color` VARCHAR(7) DEFAULT '#764ba2',
                `default_language` VARCHAR(5) DEFAULT 'es',
                `session_timeout` INT DEFAULT 60,
                `max_file_size` INT DEFAULT 10,
                `backup_frequency` ENUM('daily','weekly','monthly','never') DEFAULT 'weekly',
                `maintenance_mode` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            self::$db->query($sql);
            
            // Insertar configuración por defecto
            $defaultConfig = self::getDefaultConfig();
            self::$db->insert('company_settings', $defaultConfig);
            
        } catch(Exception $e) {
            error_log("Error creating default config: " . $e->getMessage());
        }
    }
    
    private static function getDefaultConfig() {
        return [
            'company_name' => 'Travel Agency',
            'logo_url' => '',
            'background_image' => '',
            'admin_primary_color' => '#e53e3e',
            'admin_secondary_color' => '#fd746c',
            'agent_primary_color' => '#667eea',
            'agent_secondary_color' => '#764ba2',
            'login_bg_color' => '#667eea',
            'login_secondary_color' => '#764ba2',
            'default_language' => 'es',
            'session_timeout' => 60,
            'max_file_size' => 10,
            'backup_frequency' => 'weekly',
            'maintenance_mode' => 0
        ];
    }
    
    public static function get($key = null) {
        if (!self::$config) {
            self::init();
        }
        
        if ($key === null) {
            return self::$config;
        }
        
        return self::$config[$key] ?? null;
    }
    
    public static function getCompanyName() {
        return self::get('company_name') ?: 'Travel Agency';
    }
    
    public static function getLogo() {
        return self::get('logo_url') ?: '';
    }
    
    public static function getDefaultLanguage() {
        return self::get('default_language') ?: 'es';
    }
    
    public static function getSessionTimeout() {
        return (int)self::get('session_timeout') ?: 60;
    }
    
    public static function getColorsForRole($role) {
        $config = self::get();
        
        if ($role === 'admin') {
            return [
                'primary' => $config['admin_primary_color'] ?? '#e53e3e',
                'secondary' => $config['admin_secondary_color'] ?? '#fd746c'
            ];
        } else {
            return [
                'primary' => $config['agent_primary_color'] ?? '#667eea',
                'secondary' => $config['agent_secondary_color'] ?? '#764ba2'
            ];
        }
    }
    
    public static function getLoginColors() {
        $config = self::get();
        return [
            'primary' => $config['login_bg_color'] ?? '#667eea',
            'secondary' => $config['login_secondary_color'] ?? '#764ba2'
        ];
    }
    
    public static function update($data) {
        try {
            if (!self::$db) {
                self::init();
            }
            
            $currentConfig = self::get();
            if (!$currentConfig || !isset($currentConfig['id'])) {
                // Crear nueva configuración
                self::$db->insert('company_settings', $data);
            } else {
                // Actualizar existente
                $setParts = [];
                $params = [];
                
                foreach ($data as $key => $value) {
                    $setParts[] = "`{$key}` = ?";
                    $params[] = $value;
                }
                
                if (!empty($setParts)) {
                    $params[] = $currentConfig['id'];
                    $sql = "UPDATE company_settings SET " . implode(', ', $setParts) . " WHERE id = ?";
                    self::$db->query($sql, $params);
                }
            }
            
            self::loadConfig(); // Recargar configuración
            return true;
        } catch(Exception $e) {
            error_log("Error updating config: " . $e->getMessage());
            return false;
        }
    }
    
    public static function uploadFile($file, $type = 'general') {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        $maxSize = (self::get('max_file_size') ?: 10) * 1024 * 1024; // MB to bytes
        
        // Validar archivo
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, SVG, WebP');
        }
        
        if ($file['size'] > $maxSize) {
            $maxMB = $maxSize / (1024 * 1024);
            throw new Exception("El archivo es demasiado grande. Máximo {$maxMB}MB permitido");
        }
        
        // Crear directorio si no existe
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/config/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $url = APP_URL . '/assets/uploads/config/' . $filename;
            return $url;
        } else {
            throw new Exception('Error al subir el archivo');
        }
    }
    
    public static function generateCSS($role = null) {
        $config = self::get();
        
        if ($role === 'admin') {
            $primary = $config['admin_primary_color'] ?? '#e53e3e';
            $secondary = $config['admin_secondary_color'] ?? '#fd746c';
        } elseif ($role === 'agent') {
            $primary = $config['agent_primary_color'] ?? '#667eea';
            $secondary = $config['agent_secondary_color'] ?? '#764ba2';
        } else {
            // Para login
            $primary = $config['login_bg_color'] ?? '#667eea';
            $secondary = $config['login_secondary_color'] ?? '#764ba2';
        }
        
        return "
        :root {
            --primary-color: {$primary};
            --secondary-color: {$secondary};
            --primary-gradient: linear-gradient(135deg, {$primary} 0%, {$secondary} 100%);
        }
        
        .header {
            background: var(--primary-gradient) !important;
        }
        
        .role-badge,
        .add-btn,
        .btn-primary,
        .save-btn,
        .login-btn {
            background: var(--primary-gradient) !important;
        }
        
        .action-card:hover {
            border-color: {$primary} !important;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: {$primary} !important;
        }
        
        .stat-number {
            color: {$primary} !important;
        }
        ";
    }
}

// Función helper para obtener configuración
function getConfig($key = null) {
    return ConfigManager::get($key);
}

// Función helper para obtener colores según rol
function getThemeColors($role = null) {
    if ($role) {
        return ConfigManager::getColorsForRole($role);
    }
    return ConfigManager::getLoginColors();
}
?>