<?php
// =====================================
// ARCHIVO: modules/programa/api.php - VERSIÓN OPTIMIZADA Y CORREGIDA
// =====================================

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

App::init();
App::requireLogin();

class ProgramaAPI {
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
        
        try {
            error_log("=== PROGRAMA API ===");
            error_log("Action: " . $action);
            error_log("POST: " . print_r($_POST, true));
            
            switch($action) {
                case 'create':
                case 'update':
                case 'save_programa':
                    $result = $this->savePrograma();
                    break;
                case 'get':
                    $result = $this->getPrograma($_GET['id']);
                    break;
                case 'list':
                    $result = $this->listProgramas();
                    break;
                case 'delete':
                    $result = $this->deletePrograma($_POST['id']);
                    break;
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
    
    private function savePrograma() {
        try {
            $user_id = $_SESSION['user_id'];
            
            // Validar datos requeridos
            $this->validateProgramaData();
            
            // Verificar si es edición o creación nueva
            $programa_id = $_POST['programa_id'] ?? null;
            
            if ($programa_id) {
                // ACTUALIZAR programa existente
                $this->verifyPermissions($programa_id, $user_id);
                
                $data = [
                    'nombre_viajero' => trim($_POST['traveler_name']),
                    'apellido_viajero' => trim($_POST['traveler_lastname']),
                    'destino' => trim($_POST['destination']),
                    'fecha_llegada' => $_POST['arrival_date'],
                    'fecha_salida' => $_POST['departure_date'],
                    'numero_pasajeros' => intval($_POST['passengers']),
                    'acompanamiento' => trim($_POST['accompaniment'])
                ];
                
                $result = $this->db->update('programa_solicitudes', $data, 'id = ?', [$programa_id]);
                if (!$result) {
                    throw new Exception('Error al actualizar el programa');
                }
                $request_id = null; // No cambiar el ID en actualización
                
            } else {
                // CREAR nuevo programa
                
                // Primero insertar sin numero_solicitud
                $data = [
                    'nombre_viajero' => trim($_POST['traveler_name']),
                    'apellido_viajero' => trim($_POST['traveler_lastname']),
                    'destino' => trim($_POST['destination']),
                    'fecha_llegada' => $_POST['arrival_date'],
                    'fecha_salida' => $_POST['departure_date'],
                    'numero_pasajeros' => intval($_POST['passengers']),
                    'acompanamiento' => trim($_POST['accompaniment']),
                    'user_id' => $user_id
                ];
                
                // Insertar registro
                $programa_id = $this->db->insert('programa_solicitudes', $data);
                if (!$programa_id) {
                    throw new Exception('Error al crear el programa');
                }
                
                // Generar y actualizar id_solicitud
                $request_id = $this->generateUniqueRequestId($programa_id);
                
                // Actualizar el registro con el id_solicitud
                $updateResult = $this->db->update(
                    'programa_solicitudes', 
                    ['id_solicitud' => $request_id], 
                    'id = ?', 
                    [$programa_id]
                );
                
                if (!$updateResult) {
                    // Si falla la actualización, eliminar el registro creado
                    $this->db->delete('programa_solicitudes', 'id = ?', [$programa_id]);
                    throw new Exception('Error al generar número de solicitud');
                }
            }
            
            // Guardar personalización
            $this->savePersonalizacion($programa_id);
            
            return [
                'success' => true, 
                'id' => $programa_id,
                'request_id' => $request_id,
                'message' => 'Programa guardado exitosamente'
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error guardando programa: ' . $e->getMessage());
        }
    }
    
    private function generateUniqueRequestId($programa_id = null) {
        $year = date('Y');
        $attempts = 0;
        $maxAttempts = 50;
        
        do {
            // Generar ID con formato: SOL-YYYY-NNNN
            if ($programa_id) {
                // Usar el ID del programa como base
                $requestId = sprintf('SOL-%s-%04d', $year, $programa_id);
            } else {
                // Usar contador secuencial
                $counter = $this->getNextCounter($year);
                $requestId = sprintf('SOL-%s-%04d', $year, $counter);
            }
            
            // Verificar que no exista (solo si hay registros en la tabla)
            try {
                $existing = $this->db->fetch(
                    "SELECT id FROM programa_solicitudes WHERE id_solicitud = ?", 
                    [$requestId]
                );
                
                if (!$existing) {
                    return $requestId;
                }
            } catch (Exception $e) {
                // Si hay error en la consulta (columna no existe), usar formato simple
                return $requestId;
            }
            
            $attempts++;
            
            // Si existe, probar con timestamp
            $requestId = 'SOL-' . $year . '-' . time() . '-' . $attempts;
            
        } while ($attempts < $maxAttempts);
        
        // Último recurso: usar timestamp actual
        return 'SOL-' . $year . '-' . time() . '-' . uniqid();
    }
    
    private function getNextCounter($year) {
        try {
            // Obtener el último número usado para este año
            $lastRecord = $this->db->fetch(
                "SELECT id_solicitud FROM programa_solicitudes 
                 WHERE id_solicitud LIKE ? 
                 ORDER BY id_solicitud DESC 
                 LIMIT 1", 
                ['SOL-' . $year . '-%']
            );
            
            if ($lastRecord && isset($lastRecord['id_solicitud'])) {
                // Extraer el número del formato SOL-YYYY-NNNN
                $parts = explode('-', $lastRecord['id_solicitud']);
                if (count($parts) >= 3) {
                    $lastNumber = intval($parts[2]);
                    return $lastNumber + 1;
                }
            }
        } catch (Exception $e) {
            // Si hay error (columna no existe), usar contador desde 1
            error_log("Error en getNextCounter: " . $e->getMessage());
        }
        
        // Si no hay registros previos o hay error, empezar desde 1
        return 1;
    }
    
    private function savePersonalizacion($programa_id) {
        try {
            $data = [
                'titulo_programa' => trim($_POST['program_title'] ?? ''),
                'idioma_presupuesto' => trim($_POST['budget_language'] ?? 'es')
            ];
            
            // Procesar imagen de portada si se subió
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $imageUrl = $this->uploadImage($_FILES['cover_image'], $programa_id, 'portada');
                $data['foto_portada'] = $imageUrl;
            }
            
            // Verificar si ya existe personalización
            $existing = $this->db->fetch(
                "SELECT id FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$programa_id]
            );
            
            if ($existing) {
                // Actualizar existente
                $result = $this->db->update(
                    'programa_personalizacion', 
                    $data, 
                    'solicitud_id = ?', 
                    [$programa_id]
                );
            } else {
                // Crear nuevo
                $data['solicitud_id'] = $programa_id;
                $result = $this->db->insert('programa_personalizacion', $data);
            }
            
            if (!$result) {
                throw new Exception('Error al guardar personalización');
            }
            
        } catch(Exception $e) {
            throw new Exception('Error en personalización: ' . $e->getMessage());
        }
    }
    
    private function uploadImage($file, $programa_id, $type) {
        try {
            // Validar archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido. Solo: JPG, PNG, GIF, WebP');
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Archivo demasiado grande (máx 5MB)');
            }
            
            // Crear directorio
            $baseDir = dirname(__DIR__, 2) . '/assets/uploads/programa/';
            $yearMonth = date('Y/m');
            $uploadDir = $baseDir . $yearMonth . '/';
            
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear directorio de uploads');
                }
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'programa_' . $programa_id . '_' . $type . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Error moviendo archivo subido');
            }
            
            return APP_URL . '/assets/uploads/programa/' . $yearMonth . '/' . $fileName;
            
        } catch(Exception $e) {
            throw new Exception('Error subiendo imagen: ' . $e->getMessage());
        }
    }
    
    private function getPrograma($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Construir consulta según permisos
            if ($user['role'] === 'admin') {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_presupuesto, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.id = ?";
                $programa = $this->db->fetch($sql, [$id]);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_presupuesto, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.id = ? AND s.user_id = ?";
                $programa = $this->db->fetch($sql, [$id, $user_id]);
            }
            
            if (!$programa) {
                throw new Exception('Programa no encontrado');
            }
            
            return ['success' => true, 'data' => $programa];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo programa: ' . $e->getMessage());
        }
    }
    
    private function listProgramas() {
        try {
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            if ($user['role'] === 'admin') {
                $sql = "SELECT s.*, p.titulo_programa, p.foto_portada, u.full_name as agente_nombre
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        LEFT JOIN users u ON s.user_id = u.id
                        ORDER BY s.created_at DESC";
                $programas = $this->db->fetchAll($sql);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.user_id = ?
                        ORDER BY s.created_at DESC";
                $programas = $this->db->fetchAll($sql, [$user_id]);
            }
            
            // Formatear fechas para mostrar
            foreach($programas as &$programa) {
                $programa['fecha_llegada_formatted'] = date('d/m/Y', strtotime($programa['fecha_llegada']));
                $programa['fecha_salida_formatted'] = date('d/m/Y', strtotime($programa['fecha_salida']));
                $programa['created_at_formatted'] = date('d/m/Y H:i', strtotime($programa['created_at']));
            }
            
            return ['success' => true, 'data' => $programas];
            
        } catch(Exception $e) {
            throw new Exception('Error listando programas: ' . $e->getMessage());
        }
    }
    
    private function deletePrograma($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $this->verifyPermissions($id, $user_id);
            
            // Eliminar programa (las claves foráneas se encargarán del resto)
            $result = $this->db->delete('programa_solicitudes', 'id = ?', [$id]);
            
            if (!$result) {
                throw new Exception('Error al eliminar programa');
            }
            
            return ['success' => true, 'message' => 'Programa eliminado correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error eliminando programa: ' . $e->getMessage());
        }
    }
    
    private function validateProgramaData() {
        $required = [
            'traveler_name' => 'Nombre del viajero',
            'traveler_lastname' => 'Apellido del viajero',
            'destination' => 'Destino',
            'arrival_date' => 'Fecha de llegada',
            'departure_date' => 'Fecha de salida'
        ];
        
        foreach($required as $field => $label) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo '{$label}' es obligatorio");
            }
        }
        
        // Validar fechas
        $arrival = new DateTime($_POST['arrival_date']);
        $departure = new DateTime($_POST['departure_date']);
        
        if ($departure <= $arrival) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de llegada');
        }
        
        // Validar número de pasajeros
        $passengers = intval($_POST['passengers']);
        if ($passengers < 1 || $passengers > 50) {
            throw new Exception('El número de pasajeros debe estar entre 1 y 50');
        }
    }
    
    private function verifyPermissions($programa_id, $user_id) {
        $user = App::getUser();
        
        if ($user['role'] !== 'admin') {
            $existing = $this->db->fetch(
                "SELECT user_id FROM programa_solicitudes WHERE id = ?", 
                [$programa_id]
            );
            
            if (!$existing || $existing['user_id'] != $user_id) {
                throw new Exception('No tienes permisos para realizar esta acción');
            }
        }
    }
    
    private function sendError($message) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Ejecutar API
try {
    $api = new ProgramaAPI();
    $api->handleRequest();
} catch(Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>