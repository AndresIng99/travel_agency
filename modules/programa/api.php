<?php
// ====================================================================
// ARCHIVO: modules/programa/api.php - COMPLETAMENTE RESTRUCTURADO
// ====================================================================

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
            error_log("FILES: " . print_r(array_keys($_FILES), true));
            
            switch($action) {
                case 'save_programa':
                    $result = $this->savePrograma();
                    break;
                case 'get':
                    $result = $this->getPrograma($_GET['id'] ?? null);
                    break;
                case 'list':
                    $result = $this->listProgramas();
                    break;
                case 'delete':
                    $result = $this->deletePrograma($_POST['id'] ?? null);
                    break;
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("Error en API: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
                error_log("🔄 Actualizando programa ID: $programa_id");
                $this->verifyPermissions($programa_id, $user_id);
                
                $updated_data = $this->updatePrograma($programa_id);
                $request_id = $updated_data['id_solicitud'];
                
            } else {
                // CREAR nuevo programa
                error_log("➕ Creando nuevo programa");
                $created_data = $this->createPrograma($user_id);
                $programa_id = $created_data['programa_id'];
                $request_id = $created_data['request_id'];
            }
            
            // Guardar personalización
            $this->savePersonalizacion($programa_id);
            
            return [
                'success' => true,
                'message' => $programa_id && $_POST['programa_id'] ? 'Programa actualizado exitosamente' : 'Programa creado exitosamente',
                'id' => $programa_id,
                'request_id' => $request_id
            ];
            
        } catch(Exception $e) {
            error_log("❌ Error en savePrograma: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function validateProgramaData() {
        $required_fields = [
            'traveler_name' => 'Nombre del viajero',
            'traveler_lastname' => 'Apellido del viajero', 
            'destination' => 'Destino',
            'arrival_date' => 'Fecha de llegada',
            'departure_date' => 'Fecha de salida',
            'passengers' => 'Número de pasajeros'
        ];
        
        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo '$label' es obligatorio");
            }
        }
        
        // Validar fechas
        $arrival_date = $_POST['arrival_date'];
        $departure_date = $_POST['departure_date'];
        
        if (strtotime($arrival_date) < strtotime(date('Y-m-d'))) {
            throw new Exception('La fecha de llegada no puede ser anterior a hoy');
        }
        
        if (strtotime($departure_date) < strtotime($arrival_date)) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de llegada');
        }
        
        // Validar número de pasajeros
        $passengers = intval($_POST['passengers']);
        if ($passengers < 1 || $passengers > 20) {
            throw new Exception('El número de pasajeros debe estar entre 1 y 20');
        }
    }
    
    private function verifyPermissions($programa_id, $user_id) {
        $programa = $this->db->fetch(
            "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
            [$programa_id, $user_id]
        );
        
        if (!$programa) {
            throw new Exception('No tienes permisos para modificar este programa');
        }
    }
    
    private function createPrograma($user_id) {
        try {
            // Preparar datos para inserción
            $data = [
                'nombre_viajero' => trim($_POST['traveler_name']),
                'apellido_viajero' => trim($_POST['traveler_lastname']),
                'destino' => trim($_POST['destination']),
                'fecha_llegada' => $_POST['arrival_date'],
                'fecha_salida' => $_POST['departure_date'],
                'numero_pasajeros' => intval($_POST['passengers']),
                'acompanamiento' => trim($_POST['accompaniment'] ?? 'sin-acompanamiento'),
                'user_id' => $user_id
            ];
            
            error_log("📝 Insertando programa con datos: " . print_r($data, true));
            
            // Insertar en programa_solicitudes
            $programa_id = $this->db->insert('programa_solicitudes', $data);
            
            if (!$programa_id) {
                throw new Exception('Error al crear el programa en la base de datos');
            }
            
            error_log("✅ Programa creado con ID: $programa_id");
            
            // Generar ID de solicitud único
            $request_id = $this->generateUniqueRequestId($programa_id);
            
            // Actualizar con el ID de solicitud
            $updateResult = $this->db->update(
                'programa_solicitudes', 
                ['id_solicitud' => $request_id], 
                'id = ?', 
                [$programa_id]
            );
            
            if (!$updateResult) {
                // Si falla la actualización, eliminar el registro creado
                $this->db->delete('programa_solicitudes', 'id = ?', [$programa_id]);
                throw new Exception('Error al generar ID de solicitud');
            }
            
            error_log("✅ ID de solicitud generado: $request_id");
            
            return [
                'programa_id' => $programa_id,
                'request_id' => $request_id
            ];
            
        } catch(Exception $e) {
            error_log("❌ Error en createPrograma: " . $e->getMessage());
            throw new Exception('Error al crear programa: ' . $e->getMessage());
        }
    }
    
    private function updatePrograma($programa_id) {
        try {
            $data = [
                'nombre_viajero' => trim($_POST['traveler_name']),
                'apellido_viajero' => trim($_POST['traveler_lastname']),
                'destino' => trim($_POST['destination']),
                'fecha_llegada' => $_POST['arrival_date'],
                'fecha_salida' => $_POST['departure_date'],
                'numero_pasajeros' => intval($_POST['passengers']),
                'acompanamiento' => trim($_POST['accompaniment'] ?? 'sin-acompanamiento')
            ];
            
            error_log("📝 Actualizando programa $programa_id con datos: " . print_r($data, true));
            
            $result = $this->db->update('programa_solicitudes', $data, 'id = ?', [$programa_id]);
            
            if (!$result) {
                throw new Exception('Error al actualizar el programa');
            }
            
            // Obtener el ID de solicitud actual
            $programa = $this->db->fetch(
                "SELECT id_solicitud FROM programa_solicitudes WHERE id = ?", 
                [$programa_id]
            );
            
            error_log("✅ Programa actualizado exitosamente");
            
            return [
                'id_solicitud' => $programa['id_solicitud']
            ];
            
        } catch(Exception $e) {
            error_log("❌ Error en updatePrograma: " . $e->getMessage());
            throw new Exception('Error al actualizar programa: ' . $e->getMessage());
        }
    }
    
    private function savePersonalizacion($programa_id) {
        try {
            error_log("🎨 Guardando personalización para programa $programa_id");
            
            // Preparar datos de personalización
            $data = [
                'titulo_programa' => trim($_POST['program_title'] ?? ''),
                'idioma_predeterminado' => $_POST['budget_language'] ?? 'es'
            ];
            
            // Procesar imagen de portada si se subió
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                error_log("📷 Procesando imagen de portada");
                $imageUrl = $this->uploadImage($_FILES['cover_image'], $programa_id);
                if ($imageUrl) {
                    $data['foto_portada'] = $imageUrl;
                    error_log("✅ Imagen guardada: $imageUrl");
                }
            } else {
                error_log("⚠️ No se subió imagen o hay error: " . ($_FILES['cover_image']['error'] ?? 'archivo no presente'));
            }
            
            // Verificar si ya existe personalización
            $existing = $this->db->fetch(
                "SELECT id FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$programa_id]
            );
            
            if ($existing) {
                error_log("🔄 Actualizando personalización existente");
                $result = $this->db->update(
                    'programa_personalizacion', 
                    $data, 
                    'solicitud_id = ?', 
                    [$programa_id]
                );
            } else {
                error_log("➕ Creando nueva personalización");
                $data['solicitud_id'] = $programa_id;
                $result = $this->db->insert('programa_personalizacion', $data);
            }
            
            if (!$result) {
                throw new Exception('Error al guardar personalización');
            }
            
            error_log("✅ Personalización guardada exitosamente");
            
        } catch(Exception $e) {
            error_log("❌ Error en savePersonalizacion: " . $e->getMessage());
            // No lanzar excepción para que no falle todo el guardado
            error_log("⚠️ Continuando sin personalización");
        }
    }
    
    private function uploadImage($file, $programa_id) {
        try {
            error_log("=== SUBIENDO IMAGEN ===");
            error_log("Archivo recibido: " . print_r($file, true));
            
            // Validar archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido: ' . $file['type']);
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception('Archivo demasiado grande. Máximo 5MB');
            }
            
            // Crear directorios si no existen
            $baseDir = dirname(__DIR__, 2) . '/assets/uploads/programa';
            $year = date('Y');
            $month = date('m');
            $uploadDir = "$baseDir/$year/$month";
            
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear el directorio de uploads');
                }
            }
            
            // Generar nombre único para el archivo
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'programa_' . $programa_id . '_cover_' . time() . '.' . $extension;
            $filePath = $uploadDir . '/' . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Error al guardar el archivo');
            }
            
            // Generar URL accesible
            $imageUrl = APP_URL . "/assets/uploads/programa/$year/$month/$filename";
            
            error_log("✅ Imagen subida exitosamente: $imageUrl");
            return $imageUrl;
            
        } catch(Exception $e) {
            error_log("❌ Error subiendo imagen: " . $e->getMessage());
            return null;
        }
    }
    
    private function generateUniqueRequestId($programa_id) {
        $year = date('Y');
        $baseId = "SOL{$year}";
        
        // Buscar el último ID generado este año
        $lastRequest = $this->db->fetch(
            "SELECT id_solicitud FROM programa_solicitudes 
             WHERE id_solicitud LIKE ? 
             ORDER BY id_solicitud DESC LIMIT 1", 
            [$baseId . '%']
        );
        
        if ($lastRequest) {
            // Extraer el número y incrementar
            $lastNumber = intval(substr($lastRequest['id_solicitud'], strlen($baseId)));
            $newNumber = $lastNumber + 1;
        } else {
            // Primer ID del año
            $newNumber = 1;
        }
        
        // Formatear con ceros a la izquierda (3 dígitos)
        $requestId = $baseId . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        // Verificar que no exista (por seguridad)
        $exists = $this->db->fetch(
            "SELECT id FROM programa_solicitudes WHERE id_solicitud = ?", 
            [$requestId]
        );
        
        if ($exists) {
            // Si existe, intentar con el siguiente número
            return $this->generateUniqueRequestId($programa_id);
        }
        
        return $requestId;
    }
    
    private function getPrograma($id) {
        if (!$id) {
            throw new Exception('ID de programa requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Obtener datos del programa
            $programa = $this->db->fetch(
                "SELECT * FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
                [$id, $user_id]
            );
            
            if (!$programa) {
                throw new Exception('Programa no encontrado');
            }
            
            // Obtener personalización
            $personalizacion = $this->db->fetch(
                "SELECT * FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$id]
            );
            
            // Combinar datos
            $data = array_merge($programa, $personalizacion ?: []);
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch(Exception $e) {
            error_log("Error en getPrograma: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function listProgramas() {
        try {
            $user_id = $_SESSION['user_id'];
            $user_role = $_SESSION['user_role'];
            
            // Query base
            $query = "SELECT 
                ps.id,
                ps.id_solicitud,
                ps.nombre_viajero,
                ps.apellido_viajero,
                ps.destino,
                ps.fecha_llegada,
                ps.fecha_salida,
                ps.numero_pasajeros,
                ps.created_at,
                pp.titulo_programa,
                pp.foto_portada,
                u.full_name as agent_name
            FROM programa_solicitudes ps
            LEFT JOIN programa_personalizacion pp ON ps.id = pp.solicitud_id
            LEFT JOIN users u ON ps.user_id = u.id";
            
            $params = [];
            
            // Si es agente, solo ver sus programas
            if ($user_role !== 'admin') {
                $query .= " WHERE ps.user_id = ?";
                $params[] = $user_id;
            }
            
            $query .= " ORDER BY ps.created_at DESC";
            
            $programas = $this->db->fetchAll($query, $params);
            
            return [
                'success' => true,
                'data' => $programas
            ];
            
        } catch(Exception $e) {
            error_log("Error en listProgramas: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function deletePrograma($id) {
        if (!$id) {
            throw new Exception('ID de programa requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $user_role = $_SESSION['user_role'];
            
            // Verificar permisos
            if ($user_role !== 'admin') {
                $this->verifyPermissions($id, $user_id);
            }
            
            // Obtener datos antes de eliminar (para borrar imagen)
            $personalizacion = $this->db->fetch(
                "SELECT foto_portada FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$id]
            );
            
            // Eliminar personalización (CASCADE debería eliminar automáticamente)
            $this->db->delete('programa_personalizacion', 'solicitud_id = ?', [$id]);
            
            // Eliminar programa principal
            $result = $this->db->delete('programa_solicitudes', 'id = ?', [$id]);
            
            if (!$result) {
                throw new Exception('Error al eliminar el programa');
            }
            
            // Eliminar imagen física si existe
            if ($personalizacion && $personalizacion['foto_portada']) {
                $this->deleteImageFile($personalizacion['foto_portada']);
            }
            
            return [
                'success' => true,
                'message' => 'Programa eliminado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en deletePrograma: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function deleteImageFile($imageUrl) {
        try {
            // Extraer path del archivo desde la URL
            $urlPath = parse_url($imageUrl, PHP_URL_PATH);
            $filePath = dirname(__DIR__, 2) . $urlPath;
            
            if (file_exists($filePath)) {
                unlink($filePath);
                error_log("✅ Imagen eliminada: $filePath");
            }
        } catch(Exception $e) {
            error_log("⚠️ Error eliminando imagen: " . $e->getMessage());
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
    error_log("Error fatal en API: " . $e->getMessage());
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ], JSON_UNESCAPED_UNICODE);
}