<?php
// =====================================
// ARCHIVO: modules/biblioteca/api.php - VERSIÓN SIMPLIFICADA Y CORREGIDA
// =====================================

ob_start();
ini_set('display_errors', 1);
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
                // AGREGAR ESTE NUEVO CASE:
                case 'get_ubicaciones_secundarias':
                    $result = $this->getUbicacionesSecundarias($_GET['dia_id']);
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
        $data['activo'] = 1;
        $data['idioma'] = 'es';
        
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
        
        // PROCESAR UBICACIONES SECUNDARIAS (SOLO PARA DÍAS)
        if ($type === 'dias') {
            $this->processUbicacionesSecundarias($id, $_POST);
        }
        
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
        
        if ($existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('No tienes permisos para editar este recurso');
        }
        
        // Preparar datos
        $data = $this->prepareData($type, $_POST);
        $this->validateData($type, $data);
        
        // *** NUEVO: PROCESAR ELIMINACIONES DE IMÁGENES ***
        $this->processImageDeletions($type, $data);
        
        // PROCESAR UBICACIONES SECUNDARIAS (SOLO PARA DÍAS)
        if ($type === 'dias') {
            $this->processUbicacionesSecundarias($id, $_POST);
        }
        
        // Procesar imágenes nuevas
        $imageUrls = $this->processImages($type, $id);
        if (!empty($imageUrls)) {
            $data = array_merge($data, $imageUrls);
        }
    
        error_log("=== UPDATE DATA ===");
        error_log("Final data to update: " . print_r($data, true));
        
        // Actualizar registro principal
        // Actualizar registro principal
error_log("=== ATTEMPTING DATABASE UPDATE ===");
error_log("Table: " . $table);
error_log("Data to update: " . print_r($data, true));
error_log("ID: " . $id);

try {
    $success = $this->db->update($table, $data, 'id = ?', [$id]);
    error_log("Update result: " . ($success ? 'TRUE' : 'FALSE'));
    
    if ($success) {
        return ['success' => true, 'message' => 'Recurso actualizado correctamente'];
    } else {
        // Obtener más información del error
        $errorInfo = $this->db->errorInfo();
        error_log("Database error info: " . print_r($errorInfo, true));
        throw new Exception('Error al actualizar en base de datos. Info: ' . json_encode($errorInfo));
    }
} catch (Exception $dbException) {
    error_log("Database exception: " . $dbException->getMessage());
    error_log("Database exception trace: " . $dbException->getTraceAsString());
    throw new Exception('Error de base de datos: ' . $dbException->getMessage());
}
        
    } catch(Exception $e) {
        error_log("Update error: " . $e->getMessage());
        throw new Exception('Error actualizando recurso: ' . $e->getMessage());
    }
}
/**
 * Procesar eliminaciones de imágenes
 */
private function processImageDeletions($type, &$data) {
    $imageFields = $this->getImageFields($type);
    
    error_log("=== PROCESSING IMAGE DELETIONS ===");
    
    foreach ($imageFields as $field) {
        $deleteField = "delete_{$field}";
        
        if (isset($_POST[$deleteField]) && $_POST[$deleteField] == '1') {
            error_log("Setting {$field} = NULL for deletion");
            $data[$field] = NULL;
        }
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
        
        // *** NUEVO: VERIFICAR SI ESTÁ MARCADO PARA ELIMINACIÓN ***
        $deleteField = "delete_{$field}";
        if (isset($_POST[$deleteField]) && $_POST[$deleteField] == '1') {
            error_log("⚠️ Field {$field} is marked for deletion, skipping file processing");
            continue; // Saltar el procesamiento de archivos para este campo
        }
        
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
        try {
            error_log("=== UPLOADING IMAGE ===");
            error_log("File: " . print_r($file, true));
            error_log("Type: $type, ResourceId: $resourceId, Field: $field");
            
            // Validar archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido: ' . $file['type']);
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB máximo
                throw new Exception('Archivo demasiado grande (máx 10MB)');
            }
            
            // Crear directorio con ruta correcta
            $uploadsBase = dirname(__DIR__, 2) . '/assets/uploads/biblioteca/';
            $yearMonth = date('Y/m');
            $fullDir = $uploadsBase . $type . '/' . $yearMonth . '/';
            
            error_log("Creating directory: " . $fullDir);
            
            if (!is_dir($fullDir)) {
                if (!mkdir($fullDir, 0755, true)) {
                    throw new Exception('No se pudo crear directorio: ' . $fullDir);
                }
            }
            
            // Generar nombre único
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName = $type . '_' . $resourceId . '_' . $field . '_' . time() . '.' . $extension;
            $filePath = $fullDir . $fileName;
            
            error_log("Moving file to: " . $filePath);
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Error moviendo archivo a: ' . $filePath);
            }
            
            // Verificar que el archivo se creó
            if (!file_exists($filePath)) {
                throw new Exception('El archivo no se creó correctamente');
            }
            
            // Generar URL relativa al dominio
            $url = APP_URL . '/assets/uploads/biblioteca/' . $type . '/' . $yearMonth . '/' . $fileName;
            
            error_log("Image uploaded successfully: " . $url);
            
            return $url;
            
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // ✅ FUNCIONES FALTANTES QUE NECESITAS AGREGAR:
    
    private function prepareData($type, $postData) {
        $data = [];
        
        switch($type) {
            case 'dias':
                $data = [
                    'titulo' => trim($postData['titulo'] ?? ''),
                    'descripcion' => trim($postData['descripcion'] ?? ''),
                    'ubicacion' => trim($postData['ubicacion'] ?? ''),
                    'latitud' => !empty($postData['latitud']) ? (float)$postData['latitud'] : null,
                    'longitud' => !empty($postData['longitud']) ? (float)$postData['longitud'] : null
                ];
                break;
                
            case 'alojamientos':
                $data = [
                    'nombre' => trim($postData['nombre'] ?? ''),
                    'descripcion' => trim($postData['descripcion'] ?? ''),
                    'ubicacion' => trim($postData['ubicacion'] ?? ''),
                    'tipo' => $postData['tipo'] ?? 'hotel',
                    'categoria' => !empty($postData['categoria']) ? (int)$postData['categoria'] : null,
                    'latitud' => !empty($postData['latitud']) ? (float)$postData['latitud'] : null,
                    'longitud' => !empty($postData['longitud']) ? (float)$postData['longitud'] : null,
                    'sitio_web' => trim($postData['sitio_web'] ?? '')
                ];
                break;
                
            case 'actividades':
                $data = [
                    'nombre' => trim($postData['nombre'] ?? ''),
                    'descripcion' => trim($postData['descripcion'] ?? ''),
                    'ubicacion' => trim($postData['ubicacion'] ?? ''),
                    'latitud' => !empty($postData['latitud']) ? (float)$postData['latitud'] : null,
                    'longitud' => !empty($postData['longitud']) ? (float)$postData['longitud'] : null
                ];
                break;
                
            case 'transportes':
                $data = [
                    'medio' => $postData['medio'] ?? 'bus',
                    'titulo' => trim($postData['titulo'] ?? ''),
                    'descripcion' => trim($postData['descripcion'] ?? ''),
                    'lugar_salida' => trim($postData['lugar_salida'] ?? ''),
                    'lugar_llegada' => trim($postData['lugar_llegada'] ?? ''),
                    'lat_salida' => !empty($postData['lat_salida']) ? (float)$postData['lat_salida'] : null,
                    'lng_salida' => !empty($postData['lng_salida']) ? (float)$postData['lng_salida'] : null,
                    'lat_llegada' => !empty($postData['lat_llegada']) ? (float)$postData['lat_llegada'] : null,
                    'lng_llegada' => !empty($postData['lng_llegada']) ? (float)$postData['lng_llegada'] : null,
                    'duracion' => trim($postData['duracion'] ?? ''),
                    'distancia_km' => !empty($postData['distancia_km']) ? (float)$postData['distancia_km'] : null
                ];
                break;
        }
        
        return $data;
    }
    
    private function validateData($type, $data) {
        switch($type) {
            case 'dias':
                if (empty($data['titulo'])) {
                    throw new Exception('El título es obligatorio');
                }
                break;
                
            case 'alojamientos':
                if (empty($data['nombre'])) {
                    throw new Exception('El nombre es obligatorio');
                }
                if (empty($data['descripcion'])) {
                    throw new Exception('La descripción es obligatoria');
                }
                if (empty($data['tipo'])) {
                    throw new Exception('El tipo es obligatorio');
                }
                
                // Validar tipo
                $tiposValidos = ['hotel','camping','casa_huespedes','crucero','lodge','atipico','campamento','camping_car','tren'];
                if (!in_array($data['tipo'], $tiposValidos)) {
                    throw new Exception('Tipo de alojamiento no válido');
                }
                
                // Validar categoría si es hotel
                if ($data['tipo'] === 'hotel' && !empty($data['categoria'])) {
                    if ($data['categoria'] < 1 || $data['categoria'] > 5) {
                        throw new Exception('La categoría debe estar entre 1 y 5 estrellas');
                    }
                }
                break;
                
            case 'actividades':
                if (empty($data['nombre'])) {
                    throw new Exception('El nombre es obligatorio');
                }
                break;
                
            case 'transportes':
                if (empty($data['titulo'])) {
                    throw new Exception('El título es obligatorio');
                }
                break;
        }
    }
    
    private function getImageFields($type) {
        switch($type) {
            case 'dias':
                return ['imagen1', 'imagen2', 'imagen3'];
            case 'alojamientos':
                return ['imagen'];
            case 'actividades':
                return ['imagen1', 'imagen2', 'imagen3'];
            case 'transportes':
                return []; // Los transportes no tienen imágenes
            default:
                return [];
        }
    }
    private function getUbicacionesSecundarias($diaId) {
        try {
            if (!$diaId) {
                throw new Exception('ID de día requerido');
            }
            
            $ubicaciones = $this->db->fetchAll(
                "SELECT ubicacion, latitud, longitud, orden 
                FROM biblioteca_dias_ubicaciones_secundarias 
                WHERE dia_id = ? 
                ORDER BY orden ASC", 
                [$diaId]
            );
            
            return [
                'success' => true,
                'ubicaciones' => $ubicaciones
            ];
            
        } catch(Exception $e) {
            error_log("Error obteniendo ubicaciones secundarias: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    private function processUbicacionesSecundarias($diaId, $postData) {
        try {
            error_log("=== PROCESANDO UBICACIONES SECUNDARIAS ===");
            error_log("Día ID: " . $diaId);
            error_log("POST data ubicaciones: " . print_r($postData, true));
            
            // Eliminar ubicaciones existentes
            $this->db->query("DELETE FROM biblioteca_dias_ubicaciones_secundarias WHERE dia_id = ?", [$diaId]);
            error_log("✅ Ubicaciones existentes eliminadas");
            
            // Verificar si hay ubicaciones secundarias
            if (isset($postData['ubicaciones_secundarias']) && is_array($postData['ubicaciones_secundarias'])) {
                $ubicaciones = $postData['ubicaciones_secundarias'];
                $latitudes = $postData['ubicaciones_secundarias_lat'] ?? [];
                $longitudes = $postData['ubicaciones_secundarias_lng'] ?? [];
                
                $insertedCount = 0;
                
                foreach ($ubicaciones as $index => $ubicacion) {
                    $ubicacion = trim($ubicacion);
                    if (!empty($ubicacion)) {
                        $data = [
                            'dia_id' => $diaId,
                            'ubicacion' => $ubicacion,
                            'latitud' => !empty($latitudes[$index]) ? (float)$latitudes[$index] : null,
                            'longitud' => !empty($longitudes[$index]) ? (float)$longitudes[$index] : null,
                            'orden' => $index + 1
                        ];
                        
                        $insertId = $this->db->insert('biblioteca_dias_ubicaciones_secundarias', $data);
                        if ($insertId) {
                            $insertedCount++;
                            error_log("✅ Ubicación secundaria insertada: {$ubicacion} (ID: {$insertId})");
                        } else {
                            error_log("❌ Error insertando ubicación: {$ubicacion}");
                        }
                    }
                }
                
                error_log("📍 Total ubicaciones secundarias procesadas: {$insertedCount}");
            } else {
                error_log("ℹ️ No hay ubicaciones secundarias para procesar");
            }
            
        } catch(Exception $e) {
            error_log("❌ Error procesando ubicaciones secundarias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // No lanzamos excepción para no interrumpir el flujo principal
        }
    }
}

// Inicializar y manejar la solicitud
$api = new BibliotecaAPI();
$api->handleRequest();
?>