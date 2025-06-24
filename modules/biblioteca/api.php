<?php
// =====================================
// ARCHIVO: modules/biblioteca/api.php - VERSIÓN SIMPLIFICADA Y CORREGIDA
// =====================================

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

App::init();
App::requireLogin();

class BibliotecaAPI {
    private $db;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch(Exception $e) {
            $this->sendError('Error de conexión a base de datos: ' . $e->getMessage());
        }
    }
    
    public function handleRequest() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $type = $_POST['type'] ?? $_GET['type'] ?? '';
        
        try {
            error_log("=== BIBLIOTECA API ===");
            error_log("Action: " . $action);
            error_log("Type: " . $type);
            error_log("POST: " . print_r($_POST, true));
            error_log("FILES: " . print_r(array_keys($_FILES), true));
            
            switch($action) {
                case 'list':
                    $result = $this->listResources($type);
                    break;
                case 'create':
                    $result = $this->createResource($type);
                    break;
                case 'update':
                    $result = $this->updateResource($type);
                    break;
                case 'delete':
                    $result = $this->deleteResource($type);
                    break;
                case 'get':
                    $result = $this->getResource($type, $_GET['id']);
                    break;
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("BibliotecaAPI Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
    
    private function listResources($type) {
        $allowedTypes = ['dias', 'alojamientos', 'actividades', 'transportes'];
        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Tipo de recurso no válido");
        }
        
        $table = "biblioteca_" . $type;
        
        try {
            // Consulta simple sin filtros primero
            $sql = "SELECT * FROM `{$table}` WHERE activo = 1 ORDER BY created_at DESC";
            $resources = $this->db->fetchAll($sql);
            
            // Procesar URLs de imágenes
            foreach($resources as &$resource) {
                $imageFields = $this->getImageFields($type);
                foreach($imageFields as $field) {
                    if (!empty($resource[$field])) {
                        if (strpos($resource[$field], 'http') !== 0) {
                            $resource[$field] = APP_URL . $resource[$field];
                        }
                    }
                }
            }
            
            return ['success' => true, 'data' => $resources];
            
        } catch(Exception $e) {
            throw new Exception('Error listando recursos: ' . $e->getMessage());
        }
    }
    
    private function getResource($type, $id) {
        $allowedTypes = ['dias', 'alojamientos', 'actividades', 'transportes'];
        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Tipo de recurso no válido");
        }
        
        $id = (int)$id;
        if ($id <= 0) {
            throw new Exception('ID de recurso no válido');
        }
        
        $table = "biblioteca_" . $type;
        
        try {
            $sql = "SELECT * FROM `{$table}` WHERE id = ? AND activo = 1";
            $resource = $this->db->fetch($sql, [$id]);
            
            if (!$resource) {
                throw new Exception('Recurso no encontrado');
            }
            
            // Procesar URLs de imágenes
            $imageFields = $this->getImageFields($type);
            foreach($imageFields as $field) {
                if (!empty($resource[$field])) {
                    if (strpos($resource[$field], 'http') !== 0) {
                        $resource[$field] = APP_URL . $resource[$field];
                    }
                }
            }
            
            return ['success' => true, 'data' => $resource];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo recurso: ' . $e->getMessage());
        }
    }
    
    private function createResource($type) {
        $allowedTypes = ['dias', 'alojamientos', 'actividades', 'transportes'];
        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Tipo de recurso no válido");
        }
        
        try {
            $table = "biblioteca_" . $type;
            
            // Preparar datos SIN imágenes primero
            $data = $this->prepareData($type, $_POST);
            $data['user_id'] = $_SESSION['user_id'];
            
            // Validar
            $this->validateData($type, $data);
            
            error_log("=== CREATING RESOURCE ===");
            error_log("Data to insert: " . print_r($data, true));
            
            // Insertar recurso PRIMERO
            $id = $this->db->insert($table, $data);
            
            if (!$id) {
                throw new Exception('Error al insertar en base de datos');
            }
            
            error_log("Resource created with ID: " . $id);
            
            // AHORA procesar imágenes con el ID válido
            $imageUrls = $this->processImages($type, $id);
            
            error_log("Image URLs: " . print_r($imageUrls, true));
            
            // Si hay imágenes, actualizar el registro
            if (!empty($imageUrls)) {
                $updateResult = $this->db->update($table, $imageUrls, 'id = ?', [$id]);
                error_log("Update result for images: " . $updateResult);
            }
            
            return ['success' => true, 'id' => $id, 'message' => 'Recurso creado correctamente'];
            
        } catch(Exception $e) {
            error_log("Create error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception('Error creando recurso: ' . $e->getMessage());
        }
    }
    
    private function updateResource($type) {
        $allowedTypes = ['dias', 'alojamientos', 'actividades', 'transportes'];
        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Tipo de recurso no válido");
        }
        
        $id = (int)$_POST['id'];
        if ($id <= 0) {
            throw new Exception('ID de recurso no válido');
        }
        
        try {
            $table = "biblioteca_" . $type;
            
            // Verificar permisos
            $existing = $this->db->fetch("SELECT user_id FROM `{$table}` WHERE id = ?", [$id]);
            if (!$existing) {
                throw new Exception('Recurso no encontrado');
            }
            
            if ($existing['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Sin permisos');
            }
            
            // Preparar datos
            $data = $this->prepareData($type, $_POST);
            $this->validateData($type, $data);
            
            // Procesar imágenes primero
            $imageUrls = $this->processImages($type, $id);
            
            // Agregar URLs de imágenes a los datos
            foreach($imageUrls as $field => $url) {
                $data[$field] = $url;
            }
            
            // Actualizar solo si hay datos
            if (!empty($data)) {
                $affected = $this->db->update($table, $data, 'id = ?', [$id]);
                error_log("Updated {$affected} rows for resource {$id}");
            }
            
            return ['success' => true, 'message' => 'Recurso actualizado correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error actualizando recurso: ' . $e->getMessage());
        }
    }
    
    private function deleteResource($type) {
        $allowedTypes = ['dias', 'alojamientos', 'actividades', 'transportes'];
        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Tipo de recurso no válido");
        }
        
        $id = (int)$_POST['id'];
        if ($id <= 0) {
            throw new Exception('ID de recurso no válido');
        }
        
        try {
            $table = "biblioteca_" . $type;
            
            // Verificar permisos
            $existing = $this->db->fetch("SELECT user_id FROM `{$table}` WHERE id = ?", [$id]);
            if (!$existing) {
                throw new Exception('Recurso no encontrado');
            }
            
            if ($existing['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Sin permisos');
            }
            
            // Soft delete
            $this->db->update($table, ['activo' => 0], 'id = ?', [$id]);
            
            return ['success' => true, 'message' => 'Recurso eliminado correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error eliminando recurso: ' . $e->getMessage());
        }
    }
    
    private function processImages($type, $resourceId) {
        $imageFields = $this->getImageFields($type);
        $imageUrls = [];
        
        error_log("=== PROCESSING IMAGES ===");
        error_log("Type: " . $type);
        error_log("Resource ID: " . $resourceId);
        error_log("Image fields to check: " . print_r($imageFields, true));
        error_log("Files received: " . print_r(array_keys($_FILES), true));
        
        foreach ($imageFields as $field) {
            error_log("Checking field: " . $field);
            
            if (isset($_FILES[$field])) {
                error_log("File found for {$field}: " . print_r($_FILES[$field], true));
                
                if ($_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    try {
                        $url = $this->uploadImage($_FILES[$field], $type, $resourceId, $field);
                        $imageUrls[$field] = $url;
                        error_log("Successfully uploaded {$field}: " . $url);
                    } catch (Exception $e) {
                        error_log("Error uploading {$field}: " . $e->getMessage());
                        // No lanzar excepción, solo log el error para que no falle todo el proceso
                    }
                } else {
                    error_log("Upload error for {$field}: " . $_FILES[$field]['error']);
                }
            } else {
                error_log("No file found for field: " . $field);
            }
        }
        
        error_log("Final image URLs: " . print_r($imageUrls, true));
        return $imageUrls;
    }
    
    private function uploadImage($file, $type, $resourceId, $field) {
        // Validar archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Archivo demasiado grande (máx 5MB)');
        }
        
        // Crear directorio
        $baseDir = dirname(__DIR__, 2) . '/assets/uploads/biblioteca/';
        $yearMonth = date('Y/m');
        $typeDir = $baseDir . $type . '/' . $yearMonth . '/';
        
        if (!is_dir($typeDir)) {
            if (!mkdir($typeDir, 0755, true)) {
                throw new Exception('No se pudo crear directorio');
            }
        }
        
        // Generar nombre
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $type . '_' . $resourceId . '_' . $field . '_' . time() . '.' . $extension;
        $filePath = $typeDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return APP_URL . '/assets/uploads/biblioteca/' . $type . '/' . $yearMonth . '/' . $fileName;
        } else {
            throw new Exception('Error moviendo archivo');
        }
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
        $data = [];
        
        // Campos comunes
        if (!empty($postData['idioma'])) $data['idioma'] = trim($postData['idioma']);
        if (!empty($postData['descripcion'])) $data['descripcion'] = trim($postData['descripcion']);
        
        // Campos específicos por tipo
        switch($type) {
            case 'dias':
                if (!empty($postData['titulo'])) $data['titulo'] = trim($postData['titulo']);
                if (!empty($postData['ubicacion'])) $data['ubicacion'] = trim($postData['ubicacion']);
                if (!empty($postData['latitud'])) $data['latitud'] = floatval($postData['latitud']);
                if (!empty($postData['longitud'])) $data['longitud'] = floatval($postData['longitud']);
                break;
                
            case 'alojamientos':
                if (!empty($postData['nombre'])) $data['nombre'] = trim($postData['nombre']);
                if (!empty($postData['ubicacion'])) $data['ubicacion'] = trim($postData['ubicacion']);
                if (!empty($postData['tipo'])) $data['tipo'] = trim($postData['tipo']);
                if (!empty($postData['categoria'])) $data['categoria'] = intval($postData['categoria']);
                if (!empty($postData['sitio_web'])) $data['sitio_web'] = trim($postData['sitio_web']);
                if (!empty($postData['latitud'])) $data['latitud'] = floatval($postData['latitud']);
                if (!empty($postData['longitud'])) $data['longitud'] = floatval($postData['longitud']);
                break;
                
            case 'actividades':
                if (!empty($postData['nombre'])) $data['nombre'] = trim($postData['nombre']);
                if (!empty($postData['ubicacion'])) $data['ubicacion'] = trim($postData['ubicacion']);
                if (!empty($postData['latitud'])) $data['latitud'] = floatval($postData['latitud']);
                if (!empty($postData['longitud'])) $data['longitud'] = floatval($postData['longitud']);
                break;
                
            case 'transportes':
                if (!empty($postData['medio'])) $data['medio'] = trim($postData['medio']);
                if (!empty($postData['titulo'])) $data['titulo'] = trim($postData['titulo']);
                if (!empty($postData['lugar_salida'])) $data['lugar_salida'] = trim($postData['lugar_salida']);
                if (!empty($postData['lugar_llegada'])) $data['lugar_llegada'] = trim($postData['lugar_llegada']);
                if (!empty($postData['duracion'])) $data['duracion'] = trim($postData['duracion']);
                if (!empty($postData['distancia_km'])) $data['distancia_km'] = floatval($postData['distancia_km']);
                if (!empty($postData['lat_salida'])) $data['lat_salida'] = floatval($postData['lat_salida']);
                if (!empty($postData['lng_salida'])) $data['lng_salida'] = floatval($postData['lng_salida']);
                if (!empty($postData['lat_llegada'])) $data['lat_llegada'] = floatval($postData['lat_llegada']);
                if (!empty($postData['lng_llegada'])) $data['lng_llegada'] = floatval($postData['lng_llegada']);
                break;
        }
        
        return $data;
    }
    
    private function validateData($type, $data) {
        switch($type) {
            case 'dias':
                if (empty($data['titulo'])) throw new Exception('El título es requerido');
                break;
            case 'alojamientos':
                if (empty($data['nombre'])) throw new Exception('El nombre es requerido');
                if (empty($data['tipo'])) throw new Exception('El tipo es requerido');
                break;
            case 'actividades':
                if (empty($data['nombre'])) throw new Exception('El nombre es requerido');
                break;
            case 'transportes':
                if (empty($data['medio'])) throw new Exception('El medio es requerido');
                if (empty($data['titulo'])) throw new Exception('El título es requerido');
                break;
        }
        
        if (empty($data['idioma'])) throw new Exception('El idioma es requerido');
    }
}

// Ejecutar API
try {
    $api = new BibliotecaAPI();
    $api->handleRequest();
} catch(Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>