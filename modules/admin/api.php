<?php
// =====================================
// ARCHIVO: modules/admin/api.php - API de Administrador
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
                case 'statistics':
                    return $this->getStatistics();
                case 'toggle_user':
                    return $this->toggleUserStatus();
                default:
                    throw new Exception('Acción no válida');
            }
        } catch(Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function getUsers() {
        $users = $this->db->fetchAll(
            "SELECT id, username, email, full_name, role, active, last_login, created_at FROM users ORDER BY created_at DESC"
        );
        
        return ['success' => true, 'data' => $users];
    }
    
    private function createUser() {
        $data = $this->prepareUserData($_POST);
        
        // Validar datos
        $this->validateUserData($data, true);
        
        // Verificar que username y email no existan
        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$data['username'], $data['email']]);
        if ($existing) {
            throw new Exception('El nombre de usuario o email ya existe');
        }
        
        // Hash de la contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $id = $this->db->insert('users', $data);
        
        return ['success' => true, 'id' => $id, 'message' => 'Usuario creado correctamente'];
    }
    
    private function updateUser() {
        $id = $_POST['id'];
        $data = $this->prepareUserData($_POST);
        
        // Validar datos (sin contraseña obligatoria)
        $this->validateUserData($data, false);
        
        // Verificar que el usuario no sea el admin principal
        if ($id == 1 && $data['role'] !== 'admin') {
            throw new Exception('No se puede cambiar el rol del administrador principal');
        }
        
        // Verificar username y email únicos (excluyendo el usuario actual)
        $existing = $this->db->fetch("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?", [$data['username'], $data['email'], $id]);
        if ($existing) {
            throw new Exception('El nombre de usuario o email ya existe');
        }
        
        // Hash de la contraseña si se proporciona
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        $this->db->update('users', $data, 'id = ?', [$id]);
        
        return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
    }
    
    private function deleteUser() {
        $id = $_POST['id'];
        
        // No permitir eliminar el admin principal
        if ($id == 1) {
            throw new Exception('No se puede eliminar el administrador principal');
        }
        
        // No permitir que se elimine a sí mismo
        if ($id == $_SESSION['user_id']) {
            throw new Exception('No puedes eliminar tu propia cuenta');
        }
        
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
        
        return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
    }
    
    private function toggleUserStatus() {
        $id = $_POST['id'];
        
        // No permitir desactivar el admin principal
        if ($id == 1) {
            throw new Exception('No se puede desactivar el administrador principal');
        }
        
        $user = $this->db->fetch("SELECT active FROM users WHERE id = ?", [$id]);
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        $newStatus = !$user['active'];
        $this->db->update('users', ['active' => $newStatus], 'id = ?', [$id]);
        
        $message = $newStatus ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
        return ['success' => true, 'message' => $message];
    }
    
    private function getStatistics() {
        // Contar usuarios
        $totalUsers = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE active = 1")['count'];
        
        // Contar programas
        $totalPrograms = $this->db->fetch("SELECT COUNT(*) as count FROM programa_solicitudes")['count'] ?? 0;
        
        // Contar recursos de biblioteca
        $totalDias = $this->db->fetch("SELECT COUNT(*) as count FROM biblioteca_dias WHERE activo = 1")['count'] ?? 0;
        $totalAlojamientos = $this->db->fetch("SELECT COUNT(*) as count FROM biblioteca_alojamientos WHERE activo = 1")['count'] ?? 0;
        $totalActividades = $this->db->fetch("SELECT COUNT(*) as count FROM biblioteca_actividades WHERE activo = 1")['count'] ?? 0;
        $totalTransportes = $this->db->fetch("SELECT COUNT(*) as count FROM biblioteca_transportes WHERE activo = 1")['count'] ?? 0;
        
        $totalResources = $totalDias + $totalAlojamientos + $totalActividades + $totalTransportes;
        
        // Sesiones activas (usuarios que han hecho login en las últimas 24 horas)
        $activeSessions = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)")['count'];
        
        return [
            'success' => true,
            'data' => [
                'totalUsers' => $totalUsers,
                'totalPrograms' => $totalPrograms,
                'totalResources' => $totalResources,
                'activeSessions' => $activeSessions
            ]
        ];
    }
    
    private function prepareUserData($postData) {
        $fields = ['username', 'email', 'full_name', 'role', 'password', 'active'];
        $data = [];
        
        foreach($fields as $field) {
            if (isset($postData[$field]) && $postData[$field] !== '') {
                $data[$field] = trim($postData[$field]);
            }
        }
        
        // Convertir active a boolean
        if (isset($data['active'])) {
            $data['active'] = $data['active'] === '1' ? 1 : 0;
        }
        
        return $data;
    }
    
    private function validateUserData($data, $requirePassword = true) {
        if (empty($data['username'])) {
            throw new Exception('El nombre de usuario es requerido');
        }
        
        if (empty($data['email'])) {
            throw new Exception('El email es requerido');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email no tiene un formato válido');
        }
        
        if (empty($data['full_name'])) {
            throw new Exception('El nombre completo es requerido');
        }
        
        if (empty($data['role']) || !in_array($data['role'], ['admin', 'agent'])) {
            throw new Exception('El rol debe ser admin o agent');
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