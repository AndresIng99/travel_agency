<?php
// =====================================
// ARCHIVO: modules/admin/api.php - API Corregida
// =====================================

// Evitar cualquier output antes del JSON
ob_start();

// Configurar error handling para que no muestre errores en pantalla
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

// Verificar sesión y permisos
App::init();
App::requireRole('admin');

class AdminAPI {
    private $db;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch(Exception $e) {
            $this->sendError('Error de conexión a base de datos: ' . $e->getMessage());
        }
    }
    
    public function handleRequest() {
        // Limpiar cualquier output previo
        ob_clean();
        
        // Establecer headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        try {
            switch($action) {
                case 'save_config':
                    $result = $this->saveConfiguration();
                    break;
                case 'get_config':
                    $result = $this->getConfiguration();
                    break;
                case 'upload_config_image':
                    $result = $this->uploadConfigImage();
                    break;
                case 'users':
                    $result = $this->getUsers();
                    break;
                case 'statistics':
                    $result = $this->getStatistics();
                    break;
                default:
                    $result = ['success' => false, 'error' => 'Acción no válida: ' . $action];
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("Admin API Error: " . $e->getMessage());
            $this->sendError($e->getMessage());
        }
        
        exit;
    }
    
    private function sendError($message) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    private function saveConfiguration() {
        try {
            // Verificar que la tabla existe
            $this->ensureConfigTable();
            
            // Preparar datos
            $configData = $this->prepareConfigData($_POST);
            
            if (empty($configData)) {
                throw new Exception('No hay datos válidos para guardar');
            }
            
            // Validar datos
            $this->validateConfigData($configData);
            
            // Buscar configuración existente
            $existing = $this->db->fetch("SELECT id FROM company_settings ORDER BY id DESC LIMIT 1");
            
            if ($existing) {
                // Actualizar
                $this->updateConfig($existing['id'], $configData);
            } else {
                // Crear nuevo
                $this->createConfig($configData);
            }
            
            return [
                'success' => true,
                'message' => 'Configuración guardada correctamente'
            ];
            
        } catch(Exception $e) {
            error_log("Save config error: " . $e->getMessage());
            throw new Exception('Error al guardar: ' . $e->getMessage());
        }
    }
    
    private function ensureConfigTable() {
        try {
            // Verificar si existe
            $exists = $this->db->fetch("SHOW TABLES LIKE 'company_settings'");
            
            if (!$exists) {
                // Crear tabla
                $sql = "CREATE TABLE `company_settings` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $this->db->query($sql);
            }
            
            // Verificar columnas necesarias
            $this->ensureColumns();
            
        } catch(Exception $e) {
            throw new Exception('Error preparando tabla de configuración: ' . $e->getMessage());
        }
    }
    
    private function ensureColumns() {
        $requiredColumns = [
            'admin_primary_color' => "VARCHAR(7) DEFAULT '#e53e3e'",
            'admin_secondary_color' => "VARCHAR(7) DEFAULT '#fd746c'",
            'agent_primary_color' => "VARCHAR(7) DEFAULT '#667eea'",
            'agent_secondary_color' => "VARCHAR(7) DEFAULT '#764ba2'",
            'login_bg_color' => "VARCHAR(7) DEFAULT '#667eea'",
            'login_secondary_color' => "VARCHAR(7) DEFAULT '#764ba2'",
            'default_language' => "VARCHAR(5) DEFAULT 'es'",
            'session_timeout' => "INT DEFAULT 60"
        ];
        
        $existingColumns = $this->db->fetchAll("SHOW COLUMNS FROM company_settings");
        $columnNames = array_column($existingColumns, 'Field');
        
        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $columnNames)) {
                try {
                    $this->db->query("ALTER TABLE company_settings ADD COLUMN `{$column}` {$definition}");
                } catch(Exception $e) {
                    error_log("Error adding column {$column}: " . $e->getMessage());
                }
            }
        }
    }
    
    private function updateConfig($id, $data) {
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $sql = "UPDATE company_settings SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
    }
    
    private function createConfig($data) {
        // Asegurar valores por defecto
        $defaults = [
            'company_name' => 'Travel Agency',
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
        
        $finalData = array_merge($defaults, $data);
        $this->db->insert('company_settings', $finalData);
    }
    
    private function prepareConfigData($postData) {
        $allowedFields = [
            'company_name',
            'logo_url',
            'background_image',
            'admin_primary_color',
            'admin_secondary_color',
            'agent_primary_color',
            'agent_secondary_color',
            'login_bg_color',
            'login_secondary_color',
            'default_language',
            'session_timeout',
            'max_file_size',
            'backup_frequency',
            'maintenance_mode'
        ];
        
        $data = [];
        
        foreach ($allowedFields as $field) {
            if (isset($postData[$field])) {
                $value = trim((string)$postData[$field]);
                
                if ($value !== '') {
                    switch ($field) {
                        case 'session_timeout':
                        case 'max_file_size':
                            $data[$field] = max(1, (int)$value);
                            break;
                        case 'maintenance_mode':
                            $data[$field] = ($value === '1' || $value === 'true') ? 1 : 0;
                            break;
                        default:
                            $data[$field] = $value;
                    }
                }
            }
        }
        
        return $data;
    }
    
    private function validateConfigData($data) {
        // Validar colores
        $colorFields = [
            'admin_primary_color', 'admin_secondary_color',
            'agent_primary_color', 'agent_secondary_color',
            'login_bg_color', 'login_secondary_color'
        ];
        
        foreach ($colorFields as $field) {
            if (isset($data[$field])) {
                if (!preg_match('/^#[a-f0-9]{6}$/i', $data[$field])) {
                    throw new Exception("Color {$field} inválido. Use formato #rrggbb");
                }
            }
        }
        
        // Validar idioma
        if (isset($data['default_language'])) {
            $validLangs = ['es', 'en', 'fr', 'pt'];
            if (!in_array($data['default_language'], $validLangs)) {
                throw new Exception('Idioma no válido');
            }
        }
        
        // Validar timeout
        if (isset($data['session_timeout'])) {
            $timeout = (int)$data['session_timeout'];
            if ($timeout < 15 || $timeout > 480) {
                throw new Exception('Tiempo de sesión debe estar entre 15 y 480 minutos');
            }
        }
        
        // Validar tamaño archivo
        if (isset($data['max_file_size'])) {
            $size = (int)$data['max_file_size'];
            if ($size < 1 || $size > 100) {
                throw new Exception('Tamaño de archivo debe estar entre 1 y 100 MB');
            }
        }
    }
    
    private function getConfiguration() {
        try {
            $this->ensureConfigTable();
            
            $config = $this->db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            
            if (!$config) {
                // Crear configuración por defecto
                $defaults = [
                    'company_name' => 'Travel Agency',
                    'admin_primary_color' => '#e53e3e',
                    'admin_secondary_color' => '#fd746c',
                    'agent_primary_color' => '#667eea',
                    'agent_secondary_color' => '#764ba2',
                    'login_bg_color' => '#667eea',
                    'login_secondary_color' => '#764ba2',
                    'default_language' => 'es',
                    'session_timeout' => 60
                ];
                
                $this->createConfig($defaults);
                $config = $this->db->fetch("SELECT * FROM company_settings ORDER BY id DESC LIMIT 1");
            }
            
            return [
                'success' => true,
                'data' => $config
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo configuración: ' . $e->getMessage());
        }
    }
    
    private function uploadConfigImage() {
        try {
            if (!isset($_FILES['image'])) {
                throw new Exception('No se recibió imagen');
            }
            
            $file = $_FILES['image'];
            $type = $_POST['type'] ?? 'general';
            
            // Validaciones
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error en la subida del archivo');
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido');
            }
            
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('Archivo demasiado grande (máx 10MB)');
            }
            
            // Crear directorio
            $uploadDir = dirname(__DIR__, 2) . '/assets/uploads/config/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear directorio de uploads');
                }
            }
            
            // Generar nombre
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $type . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Error moviendo archivo');
            }
            
            $url = APP_URL . '/assets/uploads/config/' . $filename;
            
            return [
                'success' => true,
                'url' => $url,
                'message' => 'Imagen subida correctamente'
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error subiendo imagen: ' . $e->getMessage());
        }
    }
    
    private function getUsers() {
        try {
            $users = $this->db->fetchAll(
                "SELECT id, username, email, full_name, role, active, last_login, created_at 
                 FROM users 
                 ORDER BY created_at DESC"
            );
            
            foreach($users as &$user) {
                $user['active'] = (bool)$user['active'];
                $user['last_login_formatted'] = $user['last_login'] ? 
                    date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca';
                $user['created_at_formatted'] = date('d/m/Y', strtotime($user['created_at']));
            }
            
            return ['success' => true, 'data' => $users];
        } catch(Exception $e) {
            throw new Exception('Error obteniendo usuarios: ' . $e->getMessage());
        }
    }
    
    private function getStatistics() {
        try {
            $totalUsers = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE active = 1")['count'] ?? 0;
            
            $totalPrograms = 0;
            try {
                $result = $this->db->fetch("SELECT COUNT(*) as count FROM programa_solicitudes");
                $totalPrograms = $result['count'] ?? 0;
            } catch(Exception $e) {
                $totalPrograms = 0;
            }
            
            $totalResources = 0;
            $tables = ['biblioteca_dias', 'biblioteca_alojamientos', 'biblioteca_actividades', 'biblioteca_transportes'];
            
            foreach($tables as $table) {
                try {
                    $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$table} WHERE activo = 1");
                    $totalResources += $result['count'] ?? 0;
                } catch(Exception $e) {
                    continue;
                }
            }
            
            $activeSessions = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            )['count'] ?? 0;
            
            return [
                'success' => true,
                'data' => [
                    'totalUsers' => (int)$totalUsers,
                    'totalPrograms' => (int)$totalPrograms,
                    'totalResources' => (int)$totalResources,
                    'activeSessions' => (int)$activeSessions
                ]
            ];
        } catch(Exception $e) {
            throw new Exception('Error obteniendo estadísticas: ' . $e->getMessage());
        }
    }
}

// Ejecutar API
try {
    $api = new AdminAPI();
    $api->handleRequest();
} catch(Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>