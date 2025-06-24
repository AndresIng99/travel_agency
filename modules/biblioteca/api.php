<?php
// =====================================
// ARCHIVO: modules/biblioteca/api.php - API Completa de Biblioteca
// =====================================

App::requireLogin();

class BibliotecaAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function handleRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $type = $_POST['type'] ?? $_GET['type'] ?? '';
        
        try {
            switch($action) {
                case 'list':
                    return $this->listResources($type);
                case 'create':
                    return $this->createResource($type);
                case 'update':
                    return $this->updateResource($type);
                case 'delete':
                    return $this->deleteResource($type);
                case 'get':
                    return $this->getResource($type, $_GET['id']);
                case 'upload':
                    return $this->uploadImage();
                default:
                    throw new Exception('Acción no válida');
            }
        } catch(Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function listResources($type) {
        $table = "biblioteca_" . $type;
        $search = $_GET['search'] ?? '';
        $language = $_GET['language'] ?? '';
        
        $sql = "SELECT * FROM {$table} WHERE activo = 1";
        $params = [];
        
        if ($search) {
            switch($type) {
                case 'dias':
                    $sql .= " AND (titulo LIKE ? OR descripcion LIKE ? OR ubicacion LIKE ?)";
                    $params = array_fill(0, 3, "%{$search}%");
                    break;
                case 'alojamientos':
                case 'actividades':
                    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ? OR ubicacion LIKE ?)";
                    $params = array_fill(0, 3, "%{$search}%");
                    break;
                case 'transportes':
                    $sql .= " AND (titulo LIKE ? OR descripcion LIKE ? OR lugar_salida LIKE ? OR lugar_llegada LIKE ?)";
                    $params = array_fill(0, 4, "%{$search}%");
                    break;
            }
        }
        
        if ($language) {
            $sql .= " AND idioma = ?";
            $params[] = $language;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $resources = $this->db->fetchAll($sql, $params);
        return ['success' => true, 'data' => $resources];
    }
    
    private function getResource($type, $id) {
        $table = "biblioteca_" . $type;
        $resource = $this->db->fetch("SELECT * FROM {$table} WHERE id = ? AND activo = 1", [$id]);
        
        if (!$resource) {
            throw new Exception('Recurso no encontrado');
        }
        
        return ['success' => true, 'data' => $resource];
    }
    
    private function createResource($type) {
        $table = "biblioteca_" . $type;
        $data = $this->prepareData($type, $_POST);
        $data['user_id'] = $_SESSION['user_id'];
        
        // Validar datos requeridos
        $this->validateData($type, $data);
        
        $id = $this->db->insert($table, $data);
        return ['success' => true, 'id' => $id, 'message' => 'Recurso creado correctamente'];
    }
    
    private function updateResource($type) {
        $table = "biblioteca_" . $type;
        $id = $_POST['id'];
        $data = $this->prepareData($type, $_POST);
        
        // Validar que el recurso pertenece al usuario
        $existing = $this->db->fetch("SELECT user_id FROM {$table} WHERE id = ?", [$id]);
        if (!$existing || $existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('No tienes permisos para editar este recurso');
        }
        
        $this->validateData($type, $data);
        
        $this->db->update($table, $data, 'id = ?', [$id]);
        return ['success' => true, 'message' => 'Recurso actualizado correctamente'];
    }
    
    private function deleteResource($type) {
        $table = "biblioteca_" . $type;
        $id = $_POST['id'];
        
        // Validar que el recurso pertenece al usuario
        $existing = $this->db->fetch("SELECT user_id FROM {$table} WHERE id = ?", [$id]);
        if (!$existing || $existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('No tienes permisos para eliminar este recurso');
        }
        
        // Soft delete
        $this->db->update($table, ['activo' => 0], 'id = ?', [$id]);
        return ['success' => true, 'message' => 'Recurso eliminado correctamente'];
    }
    
    private function prepareData($type, $postData) {
        $commonFields = ['idioma', 'descripcion'];
        
        switch($type) {
            case 'dias':
                $fields = [...$commonFields, 'titulo', 'ubicacion', 'latitud', 'longitud', 'imagen1', 'imagen2', 'imagen3'];
                break;
                
            case 'alojamientos':
                $fields = [...$commonFields, 'nombre', 'ubicacion', 'tipo', 'categoria', 'latitud', 'longitud', 'sitio_web', 'imagen'];
                break;
                
            case 'actividades':
                $fields = [...$commonFields, 'nombre', 'ubicacion', 'latitud', 'longitud', 'imagen1', 'imagen2', 'imagen3'];
                break;
                
            case 'transportes':
                $fields = [...$commonFields, 'medio', 'titulo', 'lugar_salida', 'lugar_llegada', 'lat_salida', 'lng_salida', 'lat_llegada', 'lng_llegada', 'duracion', 'distancia_km'];
                break;
                
            default:
                throw new Exception('Tipo de recurso no válido');
        }
        
        $data = [];
        foreach($fields as $field) {
            if (isset($postData[$field])) {
                $data[$field] = trim($postData[$field]);
            }
        }
        
        return $data;
    }
    
    private function validateData($type, $data) {
        switch($type) {
            case 'dias':
                if (empty($data['titulo'])) throw new Exception('El título es requerido');
                if (empty($data['ubicacion'])) throw new Exception('La ubicación es requerida');
                break;
                
            case 'alojamientos':
                if (empty($data['nombre'])) throw new Exception('El nombre es requerido');
                if (empty($data['tipo'])) throw new Exception('El tipo es requerido');
                if (empty($data['ubicacion'])) throw new Exception('La ubicación es requerida');
                break;
                
            case 'actividades':
                if (empty($data['nombre'])) throw new Exception('El nombre es requerido');
                if (empty($data['ubicacion'])) throw new Exception('La ubicación es requerida');
                break;
                
            case 'transportes':
                if (empty($data['medio'])) throw new Exception('El medio de transporte es requerido');
                if (empty($data['titulo'])) throw new Exception('El título es requerido');
                if (empty($data['lugar_salida'])) throw new Exception('El lugar de salida es requerido');
                if (empty($data['lugar_llegada'])) throw new Exception('El lugar de llegada es requerido');
                break;
        }
        
        if (empty($data['idioma'])) throw new Exception('El idioma es requerido');
    }
    
    private function uploadImage() {
        if (!isset($_FILES['image'])) {
            throw new Exception('No se recibió ninguna imagen');
        }
        
        $file = $_FILES['image'];
        
        // Validar archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception('El archivo es demasiado grande (máximo 5MB)');
        }
        
        // Crear directorio si no existe
        $uploadDir = BASE_PATH . '/assets/uploads/biblioteca/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $url = APP_URL . '/assets/uploads/biblioteca/' . $fileName;
            return ['success' => true, 'url' => $url, 'filename' => $fileName];
        } else {
            throw new Exception('Error al subir la imagen');
        }
    }
}

// Manejar la petición
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new BibliotecaAPI();
    $result = $api->handleRequest();
    echo json_encode($result);
    exit;
}