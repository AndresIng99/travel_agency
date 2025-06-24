<?php
// =====================================
// ARCHIVO: modules/biblioteca/api.php - API CON SISTEMA ORGANIZADO DE IMÁGENES
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
            error_log("BibliotecaAPI Error: " . $e->getMessage());
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
        
        // Preparar datos del formulario primero
        $data = $this->prepareData($type, $_POST);
        $data['user_id'] = $_SESSION['user_id'];
        
        // Validar datos requeridos
        $this->validateData($type, $data);
        
        // Insertar el recurso primero para obtener el ID
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $values = array_values($data);
        
        $sql = "INSERT INTO {$table} (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->query($sql, $values);
        $id = $this->db->getConnection()->lastInsertId();
        
        // Ahora procesar imágenes con el ID del recurso
        $imageUrls = $this->processImagesOrganized($type, $id, $data);
        
        // Actualizar el recurso con las URLs de imágenes si existen
        if (!empty($imageUrls)) {
            $updateFields = [];
            $updateValues = [];
            
            foreach ($imageUrls as $field => $url) {
                $updateFields[] = "`{$field}` = ?";
                $updateValues[] = $url;
            }
            
            if (!empty($updateFields)) {
                $updateValues[] = $id;
                $updateSql = "UPDATE {$table} SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $this->db->query($updateSql, $updateValues);
            }
        }
        
        return ['success' => true, 'id' => $id, 'message' => 'Recurso creado correctamente'];
    }
    
    private function updateResource($type) {
        $table = "biblioteca_" . $type;
        $id = (int)$_POST['id'];
        
        if (!$id) {
            throw new Exception('ID de recurso no válido');
        }
        
        // Validar que el recurso pertenece al usuario
        $existing = $this->db->fetch("SELECT * FROM {$table} WHERE id = ?", [$id]);
        if (!$existing) {
            throw new Exception('Recurso no encontrado');
        }
        
        if ($existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('No tienes permisos para editar este recurso');
        }
        
        // Preparar datos del formulario
        $data = $this->prepareData($type, $_POST);
        $this->validateData($type, $data);
        
        // Procesar nuevas imágenes si existen
        $imageUrls = $this->processImagesOrganized($type, $id, array_merge($existing, $data));
        
        // Agregar URLs de imágenes al update
        foreach ($imageUrls as $field => $url) {
            if (!empty($url)) {
                $data[$field] = $url;
            }
        }
        
        // Actualizar recurso
        if (!empty($data)) {
            $setParts = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $setParts[] = "`{$key}` = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE id = ?";
            
            $this->db->query($sql, $values);
        }
        
        return ['success' => true, 'message' => 'Recurso actualizado correctamente'];
    }
    
    private function deleteResource($type) {
        $table = "biblioteca_" . $type;
        $id = (int)$_POST['id'];
        
        if (!$id) {
            throw new Exception('ID de recurso no válido');
        }
        
        // Validar que el recurso pertenece al usuario
        $existing = $this->db->fetch("SELECT user_id FROM {$table} WHERE id = ?", [$id]);
        if (!$existing) {
            throw new Exception('Recurso no encontrado');
        }
        
        if ($existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('No tienes permisos para eliminar este recurso');
        }
        
        // Soft delete
        $this->db->query("UPDATE {$table} SET activo = 0 WHERE id = ?", [$id]);
        return ['success' => true, 'message' => 'Recurso eliminado correctamente'];
    }
    
    // NUEVA FUNCIÓN: Procesar imágenes de forma organizada
    private function processImagesOrganized($type, $resourceId, $resourceData) {
        $imageFields = $this->getImageFields($type);
        $imageUrls = [];
        
        foreach ($imageFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                try {
                    $url = $this->uploadSingleImageOrganized($_FILES[$field], $type, $resourceId, $field, $resourceData);
                    $imageUrls[$field] = $url;
                } catch (Exception $e) {
                    error_log("Error uploading image for field {$field}: " . $e->getMessage());
                    throw new Exception("Error al subir {$field}: " . $e->getMessage());
                }
            }
        }
        
        return $imageUrls;
    }
    
    // NUEVA FUNCIÓN: Subir imagen con organización
    private function uploadSingleImageOrganized($file, $type, $resourceId, $field, $resourceData) {
        // Validar archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG, GIF, WebP');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception('El archivo es demasiado grande (máximo 5MB)');
        }
        
        // Crear estructura de carpetas organizada
        $baseDir = dirname(__DIR__, 2) . '/assets/uploads/biblioteca/';
        $yearMonth = date('Y/m'); // Ej: 2025/01
        $typeDir = $baseDir . $type . '/' . $yearMonth . '/';
        
        if (!is_dir($typeDir)) {
            if (!mkdir($typeDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de uploads');
            }
        }
        
        // Generar nombre descriptivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $resourceName = $this->getResourceName($type, $resourceData);
        $cleanName = $this->cleanFileName($resourceName);
        
        // Nomenclatura: tipo_id_nombre_campo_timestamp.ext
        // Ej: dias_123_paris-dia1_imagen1_20250101120000.jpg
        $fileName = sprintf(
            "%s_%d_%s_%s_%s.%s",
            $type,
            $resourceId,
            $cleanName,
            $field,
            date('YmdHis'),
            $extension
        );
        
        $filePath = $typeDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Retornar URL relativa organizada
            $relativeUrl = '/assets/uploads/biblioteca/' . $type . '/' . $yearMonth . '/' . $fileName;
            return APP_URL . $relativeUrl;
        } else {
            throw new Exception('Error al mover el archivo subido');
        }
    }
    
    // NUEVA FUNCIÓN: Obtener nombre del recurso para el archivo
    private function getResourceName($type, $data) {
        switch($type) {
            case 'dias':
                return $data['titulo'] ?? 'sin-titulo';
            case 'alojamientos':
            case 'actividades':
                return $data['nombre'] ?? 'sin-nombre';
            case 'transportes':
                return $data['titulo'] ?? 'sin-titulo';
            default:
                return 'recurso';
        }
    }
    
    // NUEVA FUNCIÓN: Limpiar nombre de archivo
    private function cleanFileName($name) {
        // Convertir a minúsculas
        $name = strtolower($name);
        
        // Reemplazar caracteres especiales
        $name = str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], $name);
        
        // Remover caracteres no permitidos
        $name = preg_replace('/[^a-z0-9\s\-]/', '', $name);
        
        // Reemplazar espacios y múltiples guiones
        $name = preg_replace('/[\s\-]+/', '-', $name);
        
        // Limitar longitud
        $name = substr($name, 0, 30);
        
        // Remover guiones al inicio y final
        return trim($name, '-');
    }
    
    private function getImageFields($type) {
        switch($type) {
            case 'dias':
            case 'actividades':
                return ['imagen1', 'imagen2', 'imagen3'];
            case 'alojamientos':
                return ['imagen'];
            default:
                return [];
        }
    }
    
    private function prepareData($type, $postData) {
        $commonFields = ['idioma', 'descripcion'];
        
        switch($type) {
            case 'dias':
                $fields = [...$commonFields, 'titulo', 'ubicacion', 'latitud', 'longitud'];
                break;
                
            case 'alojamientos':
                $fields = [...$commonFields, 'nombre', 'ubicacion', 'tipo', 'categoria', 'latitud', 'longitud', 'sitio_web'];
                break;
                
            case 'actividades':
                $fields = [...$commonFields, 'nombre', 'ubicacion', 'latitud', 'longitud'];
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
                $value = trim($postData[$field]);
                if ($value !== '') {
                    $data[$field] = $value;
                }
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
        $uploadDir = dirname(__DIR__, 2) . '/assets/uploads/biblioteca/misc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'misc_' . uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $url = APP_URL . '/assets/uploads/biblioteca/misc/' . $fileName;
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