<?php
// =====================================
// ARCHIVO: modules/admin/api.php - API COMPLETA de Administrador
// =====================================

App::requireRole('admin');

class AdminAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function handleRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        try {
            switch($action) {
                case 'users':
                    return $this->getUsers();
                case 'create_user':
                    return $this->createUser();
                case 'update_user':
                    return $this->updateUser();
                case 'delete_user':
                    return $this->deleteUser();
                case 'toggle_user':
                    return $this->toggleUserStatus();
                case 'statistics':
                    return $this->getStatistics();
                default:
                    throw new Exception('Acción no válida');
            }
        } catch(Exception $e) {
            error_log("Admin API Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function getUsers() {
        try {
            $users = $this->db->fetchAll(
                "SELECT id, username, email, full_name, role, active, last_login, created_at 
                 FROM users 
                 ORDER BY created_at DESC"
            );
            
            // Formatear fechas para mejor presentación
            foreach($users as &$user) {
                $user['active'] = (bool)$user['active'];
                $user['last_login_formatted'] = $user['last_login'] ? 
                    date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca';
                $user['created_at_formatted'] = date('d/m/Y', strtotime($user['created_at']));
            }
            
            return ['success' => true, 'data' => $users];
        } catch(Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            throw new Exception('Error al obtener usuarios: ' . $e->getMessage());
        }
    }
    
    private function createUser() {
        $data = $this->prepareUserData($_POST, true);
        
        // Validar datos
        $this->validateUserData($data, true);
        
        // Verificar que username y email no existan
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE username = ? OR email = ?", 
            [$data['username'], $data['email']]
        );
        
        if ($existing) {
            throw new Exception('El nombre de usuario o email ya existe');
        }
        
        // Hash de la contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $id = $this->db->insert('users', $data);
            
            // Obtener el usuario creado para retornarlo
            $newUser = $this->db->fetch(
                "SELECT id, username, email, full_name, role, active, created_at FROM users WHERE id = ?", 
                [$id]
            );
            
            return [
                'success' => true, 
                'message' => 'Usuario creado correctamente',
                'user' => $newUser
            ];
        } catch(Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw new Exception('Error al crear usuario: ' . $e->getMessage());
        }
    }
    
    private function updateUser() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID de usuario requerido');
        }
        
        // Verificar que el usuario existe
        $existingUser = $this->db->fetch("SELECT id, role, username, email FROM users WHERE id = ?", [$id]);
        if (!$existingUser) {
            throw new Exception('Usuario no encontrado');
        }
        
        $data = $this->prepareUserData($_POST, false);
        
        // Validar datos (sin contraseña obligatoria)
        $this->validateUserData($data, false, $existingUser);
        
        // Verificar que el usuario no sea el admin principal intentando cambiar su rol
        if ($id == 1 && isset($data['role']) && $data['role'] !== 'admin') {
            throw new Exception('No se puede cambiar el rol del administrador principal');
        }
        
        // Verificar username y email únicos (excluyendo el usuario actual)
        if (isset($data['username']) && $data['username'] !== $existingUser['username']) {
            $existing = $this->db->fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$data['username'], $id]);
            if ($existing) {
                throw new Exception('El nombre de usuario ya existe');
            }
        }
        
        if (isset($data['email']) && $data['email'] !== $existingUser['email']) {
            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$data['email'], $id]);
            if ($existing) {
                throw new Exception('El email ya existe');
            }
        }
        
        // Hash de la contraseña si se proporciona
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        // Remover campos vacíos para no sobrescribir con valores nulos
        $updateData = [];
        foreach($data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            throw new Exception('No hay datos para actualizar');
        }
        
        try {
            // Construir consulta SQL manualmente para evitar conflictos de parámetros
            $setParts = [];
            $params = [];
            
            foreach($updateData as $key => $value) {
                $setParts[] = "{$key} = ?";
                $params[] = $value;
            }
            
            // Agregar el ID al final de los parámetros
            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
            
            $this->db->query($sql, $params);
            
            // Obtener el usuario actualizado
            $updatedUser = $this->db->fetch(
                "SELECT id, username, email, full_name, role, active, last_login, created_at FROM users WHERE id = ?", 
                [$id]
            );
            
            return [
                'success' => true, 
                'message' => 'Usuario actualizado correctamente',
                'user' => $updatedUser
            ];
        } catch(Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw new Exception('Error al actualizar usuario: ' . $e->getMessage());
        }
    }
    
    private function deleteUser() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID de usuario requerido');
        }
        
        // No permitir deshabilitar el admin principal
        if ($id == 1) {
            throw new Exception('No se puede deshabilitar el administrador principal');
        }
        
        // No permitir que se deshabilite a sí mismo
        if ($id == $_SESSION['user_id']) {
            throw new Exception('No puedes deshabilitar tu propia cuenta');
        }
        
        // Verificar que el usuario existe y está activo
        $user = $this->db->fetch("SELECT id, username, active FROM users WHERE id = ?", [$id]);
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        if (!$user['active']) {
            throw new Exception('El usuario ya está deshabilitado');
        }
        
        try {
            // Solo cambiar el estado a inactivo (soft delete)
            $sql = "UPDATE users SET active = 0 WHERE id = ?";
            $params = [$id];
            
            $this->db->query($sql, $params);
            
            return [
                'success' => true, 
                'message' => "Usuario '{$user['username']}' deshabilitado correctamente"
            ];
        } catch(Exception $e) {
            error_log("Error disabling user: " . $e->getMessage());
            throw new Exception('Error al deshabilitar usuario: ' . $e->getMessage());
        }
    }
    
    private function toggleUserStatus() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID de usuario requerido');
        }
        
        // No permitir desactivar el admin principal
        if ($id == 1) {
            throw new Exception('No se puede desactivar el administrador principal');
        }
        
        $user = $this->db->fetch("SELECT id, username, active FROM users WHERE id = ?", [$id]);
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        $newStatus = !$user['active'];
        
        try {
            // Construir consulta SQL manualmente para evitar conflictos de parámetros
            $sql = "UPDATE users SET active = ? WHERE id = ?";
            $params = [$newStatus ? 1 : 0, $id];
            
            $this->db->query($sql, $params);
            
            $statusText = $newStatus ? 'activado' : 'desactivado';
            $message = "Usuario '{$user['username']}' {$statusText} correctamente";
            
            return [
                'success' => true, 
                'message' => $message,
                'newStatus' => $newStatus
            ];
        } catch(Exception $e) {
            error_log("Error toggling user status: " . $e->getMessage());
            throw new Exception('Error al cambiar estado del usuario: ' . $e->getMessage());
        }
    }
    
    private function getStatistics() {
        try {
            // Contar usuarios activos
            $totalUsers = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE active = 1")['count'] ?? 0;
            
            // Contar programas (verificar si existe la tabla)
            $totalPrograms = 0;
            try {
                $result = $this->db->fetch("SELECT COUNT(*) as count FROM programa_solicitudes");
                $totalPrograms = $result['count'] ?? 0;
            } catch(Exception $e) {
                // Tabla no existe aún
                $totalPrograms = 0;
            }
            
            // Contar recursos de biblioteca (verificar si existen las tablas)
            $totalResources = 0;
            $tables = ['biblioteca_dias', 'biblioteca_alojamientos', 'biblioteca_actividades', 'biblioteca_transportes'];
            
            foreach($tables as $table) {
                try {
                    $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$table} WHERE activo = 1");
                    $totalResources += $result['count'] ?? 0;
                } catch(Exception $e) {
                    // Tabla no existe aún
                    continue;
                }
            }
            
            // Sesiones activas (usuarios que han hecho login en las últimas 24 horas)
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
            error_log("Error getting statistics: " . $e->getMessage());
            throw new Exception('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
    
    private function prepareUserData($postData, $isNew = false) {
        $allowedFields = ['username', 'email', 'full_name', 'role', 'password', 'active'];
        $data = [];
        
        foreach($allowedFields as $field) {
            if (isset($postData[$field])) {
                $value = trim($postData[$field]);
                // Solo incluir el campo si tiene un valor o si es el campo 'active'
                if ($value !== '' || $field === 'active') {
                    $data[$field] = $value;
                }
            }
        }
        
        // Manejar el campo 'active' especialmente
        if (isset($data['active'])) {
            $data['active'] = ($data['active'] === '1' || $data['active'] === 'true' || $data['active'] === true) ? 1 : 0;
        } else if ($isNew) {
            $data['active'] = 1; // Por defecto activo para usuarios nuevos
        }
        
        return $data;
    }
    
    private function validateUserData($data, $requirePassword = true, $existingUser = null) {
        // Para actualizaciones, solo validar campos que están presentes
        $isUpdate = !$requirePassword && $existingUser !== null;
        
        if (isset($data['username'])) {
            if (empty($data['username'])) {
                throw new Exception('El nombre de usuario es requerido');
            }
            
            if (strlen($data['username']) < 3) {
                throw new Exception('El nombre de usuario debe tener al menos 3 caracteres');
            }
            
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $data['username'])) {
                throw new Exception('El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos');
            }
        } else if (!$isUpdate) {
            throw new Exception('El nombre de usuario es requerido');
        }
        
        if (isset($data['email'])) {
            if (empty($data['email'])) {
                throw new Exception('El email es requerido');
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El email no tiene un formato válido');
            }
        } else if (!$isUpdate) {
            throw new Exception('El email es requerido');
        }
        
        if (isset($data['full_name'])) {
            if (empty($data['full_name'])) {
                throw new Exception('El nombre completo es requerido');
            }
            
            if (strlen($data['full_name']) < 2) {
                throw new Exception('El nombre completo debe tener al menos 2 caracteres');
            }
        } else if (!$isUpdate) {
            throw new Exception('El nombre completo es requerido');
        }
        
        if (isset($data['role'])) {
            if (empty($data['role']) || !in_array($data['role'], ['admin', 'agent'])) {
                throw new Exception('El rol debe ser admin o agent');
            }
        } else if (!$isUpdate) {
            throw new Exception('El rol es requerido');
        }
        
        if ($requirePassword) {
            if (empty($data['password'])) {
                throw new Exception('La contraseña es requerida');
            }
            
            if (strlen($data['password']) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }
        } else {
            // Si se proporciona contraseña en actualización, validarla
            if (!empty($data['password']) && strlen($data['password']) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }
        }
    }
}

// Manejar la petición
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new AdminAPI();
    $result = $api->handleRequest();
    echo json_encode($result);
    exit;
}