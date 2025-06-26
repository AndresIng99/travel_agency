<?php
// =====================================
// ARCHIVO: modules/programa/api.php - CÓDIGO COMPLETO
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
            error_log("FILES: " . print_r($_FILES, true));
            
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
            error_log("Error en API: " . $e->getMessage());
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
                
                $updateResult = $this->db->update(
                    'programa_solicitudes', 
                    ['id_solicitud' => $request_id], 
                    'id = ?', 
                    [$programa_id]
                );
                
                if (!$updateResult) {
                    $this->db->delete('programa_solicitudes', 'id = ?', [$programa_id]);
                    throw new Exception('Error al generar número de solicitud');
                }
            }
            
            // Guardar personalización (incluyendo imagen)
            try {
                $this->savePersonalizacion($programa_id);
                error_log("✅ Personalización guardada correctamente para programa ID: " . $programa_id);
            } catch (Exception $e) {
                error_log("❌ Error guardando personalización: " . $e->getMessage());
                // No hacer throw aquí para que el programa se guarde aunque falle la imagen
            }
            
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
            if ($programa_id) {
                $requestId = sprintf('SOL-%s-%04d', $year, $programa_id);
            } else {
                $counter = $this->getNextCounter($year);
                $requestId = sprintf('SOL-%s-%04d', $year, $counter);
            }
            
            try {
                $existing = $this->db->fetch(
                    "SELECT id FROM programa_solicitudes WHERE id_solicitud = ?", 
                    [$requestId]
                );
                
                if (!$existing) {
                    return $requestId;
                }
            } catch (Exception $e) {
                return $requestId;
            }
            
            $attempts++;
            $requestId = 'SOL-' . $year . '-' . time() . '-' . $attempts;
            
        } while ($attempts < $maxAttempts);
        
        return 'SOL-' . $year . '-' . time() . '-' . uniqid();
    }
    
    private function getNextCounter($year) {
        try {
            $lastRecord = $this->db->fetch(
                "SELECT id_solicitud FROM programa_solicitudes 
                 WHERE id_solicitud LIKE ? 
                 ORDER BY id_solicitud DESC 
                 LIMIT 1", 
                ['SOL-' . $year . '-%']
            );
            
            if ($lastRecord && isset($lastRecord['id_solicitud'])) {
                $parts = explode('-', $lastRecord['id_solicitud']);
                if (count($parts) >= 3) {
                    $lastNumber = intval($parts[2]);
                    return $lastNumber + 1;
                }
            }
        } catch (Exception $e) {
            error_log("Error en getNextCounter: " . $e->getMessage());
        }
        
        return 1;
    }
    
    // ✅ FUNCIÓN CORREGIDA PARA GUARDAR PERSONALIZACIÓN E IMAGEN
    private function savePersonalizacion($programa_id) {
        try {
            error_log("=== GUARDANDO PERSONALIZACIÓN ===");
            error_log("Programa ID: " . $programa_id);
            
            $data = [
                'titulo_programa' => trim($_POST['program_title'] ?? ''),
                'idioma_predeterminado' => trim($_POST['budget_language'] ?? 'es')
            ];
            
            error_log("Datos base: " . print_r($data, true));
            
            // ✅ PROCESAR IMAGEN DE PORTADA - MEJORADO
            error_log("=== VERIFICANDO ARCHIVO DE IMAGEN ===");
            error_log("FILES disponibles: " . print_r($_FILES, true));

            if (isset($_FILES['cover_image'])) {
                error_log("cover_image encontrado - Error: " . $_FILES['cover_image']['error']);
                
                if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    error_log("✅ Archivo cover_image válido detectado");
                    
                    try {
                        $imageUrl = $this->uploadImage($_FILES['cover_image'], $programa_id);
                        $data['foto_portada'] = $imageUrl;
                        error_log("✅ Imagen subida exitosamente: " . $imageUrl);
                    } catch (Exception $e) {
                        error_log("❌ Error subiendo imagen: " . $e->getMessage());
                        // Continuar sin imagen si hay error
                    }
                } else {
                    error_log("⚠️ Error en archivo cover_image: " . $_FILES['cover_image']['error']);
                    $errors = [
                        UPLOAD_ERR_INI_SIZE => 'Archivo demasiado grande (ini_size)',
                        UPLOAD_ERR_FORM_SIZE => 'Archivo demasiado grande (form_size)', 
                        UPLOAD_ERR_PARTIAL => 'Carga parcial',
                        UPLOAD_ERR_NO_FILE => 'No se subió archivo',
                        UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal',
                        UPLOAD_ERR_CANT_WRITE => 'No se puede escribir',
                        UPLOAD_ERR_EXTENSION => 'Extensión bloqueada'
                    ];
                    error_log("Descripción del error: " . ($errors[$_FILES['cover_image']['error']] ?? 'Error desconocido'));
                }
            } else {
                error_log("⚠️ No se recibió archivo cover_image en $_FILES");
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
                throw new Exception('Error al guardar personalización en base de datos');
            }
            
            error_log("✅ Personalización guardada exitosamente");
            
        } catch(Exception $e) {
            error_log("❌ Error en savePersonalizacion: " . $e->getMessage());
            throw new Exception('Error en personalización: ' . $e->getMessage());
        }
    }
    
    // ✅ FUNCIÓN CORREGIDA PARA SUBIR IMÁGENES
    private function uploadImage($file, $programa_id) {
        try {
            error_log("=== SUBIENDO IMAGEN ===");
            error_log("Archivo: " . print_r($file, true));
            
            // Validar archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido: ' . $file['type']);
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Archivo demasiado grande: ' . round($file['size']/1024/1024, 2) . 'MB');
            }
            
            // Crear directorio
           // Verificar directorio base
            if (!is_dir(dirname(__DIR__, 2) . '/assets/uploads/')) {
                mkdir(dirname(__DIR__, 2) . '/assets/uploads/', 0755, true);
            }
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0755, true);
            }
            $yearMonth = date('Y/m');
            $uploadDir = $baseDir . $yearMonth . '/';
            
            error_log("Directorio destino: " . $uploadDir);
            
            if (!is_dir($uploadDir)) {
                error_log("Creando directorio: " . $uploadDir);
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear directorio: ' . $uploadDir);
                }
            }
            
            if (!is_writable($uploadDir)) {
                throw new Exception('Directorio no escribible: ' . $uploadDir);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'programa_' . $programa_id . '_portada_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            error_log("Archivo destino: " . $filePath);
            
            // Verificar archivo temporal
            if (!file_exists($file['tmp_name'])) {
                throw new Exception('Archivo temporal no existe: ' . $file['tmp_name']);
            }
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Error moviendo archivo');
            }
            
            // Verificar que se guardó
            if (!file_exists($filePath)) {
                throw new Exception('El archivo no se guardó correctamente');
            }
            
            // Generar URL
            $imageUrl = APP_URL . '/assets/uploads/programa/' . $yearMonth . '/' . $fileName;
            error_log("✅ Imagen subida exitosamente: " . $imageUrl);
            
            return $imageUrl;
            
        } catch(Exception $e) {
            error_log("❌ Error en uploadImage: " . $e->getMessage());
            throw new Exception('Error subiendo imagen: ' . $e->getMessage());
        }
    }
    
    private function getPrograma($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            if ($user['role'] === 'admin') {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_predeterminado, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.id = ?";
                $programa = $this->db->fetch($sql, [$id]);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_predeterminado, p.foto_portada
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
                $sql = "SELECT s.*, p.titulo_programa, p.foto_portada, p.idioma_predeterminado, u.full_name as agente_nombre
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        LEFT JOIN users u ON s.user_id = u.id
                        ORDER BY s.created_at DESC";
                $programas = $this->db->fetchAll($sql);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.foto_portada, p.idioma_predeterminado
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.user_id = ?
                        ORDER BY s.created_at DESC";
                $programas = $this->db->fetchAll($sql, [$user_id]);
            }
            
            // Formatear fechas
            foreach($programas as &$programa) {
                $programa['fecha_llegada_formatted'] = date('d/m/Y', strtotime($programa['fecha_llegada']));
                $programa['fecha_salida_formatted'] = date('d/m/Y', strtotime($programa['fecha_salida']));
                $programa['created_at_formatted'] = date('d/m/Y H:i', strtotime($programa['created_at']));
                
                // Procesar imagen de portada
                if ($programa['foto_portada']) {
                    if (strpos($programa['foto_portada'], 'http') === 0) {
                        // Ya es URL completa
                    } else {
                        $programa['foto_portada'] = APP_URL . $programa['foto_portada'];
                    }
                }
            }
            
            return ['success' => true, 'data' => $programas];
            
        } catch(Exception $e) {
            throw new Exception('Error listando programas: ' . $e->getMessage());
        }
    }
    
    private function deletePrograma($id) {
        try {
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            if ($user['role'] !== 'admin') {
                $programa = $this->db->fetch(
                    "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
                    [$id, $user_id]
                );
                if (!$programa) {
                    throw new Exception('No tienes permisos para eliminar este programa');
                }
            }
            
            // Eliminar personalización primero
            $this->db->delete('programa_personalizacion', 'solicitud_id = ?', [$id]);
            
            // Eliminar programa
            $result = $this->db->delete('programa_solicitudes', 'id = ?', [$id]);
            
            if (!$result) {
                throw new Exception('Error al eliminar el programa');
            }
            
            return ['success' => true, 'message' => 'Programa eliminado exitosamente'];
            
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
            'departure_date' => 'Fecha de salida',
            'passengers' => 'Número de pasajeros'
        ];
        
        foreach ($required as $field => $label) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo '$label' es requerido");
            }
        }
        
        // Validar fechas
        $arrival = strtotime($_POST['arrival_date']);
        $departure = strtotime($_POST['departure_date']);
        
        if ($arrival >= $departure) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de llegada');
        }
        
        if ($arrival < strtotime('today')) {
            throw new Exception('La fecha de llegada no puede ser en el pasado');
        }
    }
    
    private function verifyPermissions($programa_id, $user_id) {
        $user = App::getUser();
        
        if ($user['role'] === 'admin') {
            return;
        }
        
        $programa = $this->db->fetch(
            "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
            [$programa_id, $user_id]
        );
        
        if (!$programa) {
            throw new Exception('No tienes permisos para editar este programa');
        }
    }
    
    private function sendError($message) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Inicializar y ejecutar
$api = new ProgramaAPI();
$api->handleRequest();
?>