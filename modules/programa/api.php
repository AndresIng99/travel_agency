<?php
// ====================================================================
// ARCHIVO: modules/programa/api.php - VERSIÓN CORREGIDA
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
            error_log("=== 🚀 INICIANDO GUARDADO DE PROGRAMA ===");
            
            $user_id = $_SESSION['user_id'];
            
            // Validar datos
            $this->validateProgramaData();
            
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
                
                // Para programas nuevos, guardar personalización por separado
                $this->savePersonalizacion($programa_id);
            }
            
            error_log("✅ PROCESO COMPLETADO EXITOSAMENTE");
            
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
                error_log("❌ Campo faltante: $field");
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
        
        error_log("✅ Validación de datos completada exitosamente");
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
            error_log("=== 🆕 CREANDO NUEVO PROGRAMA ===");
            
            // Datos para inserción (basado en estructura real de la DB)
            $data = [
                'nombre_viajero' => trim($_POST['traveler_name'] ?? ''),
                'apellido_viajero' => trim($_POST['traveler_lastname'] ?? ''),
                'destino' => trim($_POST['destination'] ?? ''),
                'fecha_llegada' => $_POST['arrival_date'] ?? null,
                'fecha_salida' => $_POST['departure_date'] ?? null,
                'numero_pasajeros' => intval($_POST['passengers'] ?? 1),
                'acompanamiento' => trim($_POST['accompaniment'] ?? 'sin-acompanamiento'),
                'user_id' => $user_id
            ];
            
            error_log("📝 Insertando programa con datos: " . print_r($data, true));
            
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
                // Si falla la actualización, eliminar el registro y lanzar error
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
    
    private function generateUniqueRequestId($programa_id) {
        try {
            $year = date('Y');
            $counter = 1;
            
            // Buscar el último número de solicitud del año
            $lastRequest = $this->db->fetch(
                "SELECT id_solicitud FROM programa_solicitudes 
                 WHERE id_solicitud LIKE ? 
                 ORDER BY id_solicitud DESC LIMIT 1",
                ["SOL{$year}%"]
            );
            
            if ($lastRequest) {
                // Extraer el número del último ID
                $lastNumber = intval(substr($lastRequest['id_solicitud'], -3));
                $counter = $lastNumber + 1;
            }
            
            // Generar nuevo ID con formato SOL2025001, SOL2025002, etc.
            $request_id = sprintf("SOL%s%03d", $year, $counter);
            
            // Verificar que no exista (por seguridad)
            $exists = $this->db->fetch(
                "SELECT id FROM programa_solicitudes WHERE id_solicitud = ?",
                [$request_id]
            );
            
            if ($exists) {
                // Si existe, intentar con el siguiente número
                $counter++;
                $request_id = sprintf("SOL%s%03d", $year, $counter);
            }
            
            return $request_id;
            
        } catch(Exception $e) {
            error_log("Error generando request ID: " . $e->getMessage());
            // Fallback a un ID simple
            return "SOL" . date('Y') . str_pad($programa_id, 3, '0', STR_PAD_LEFT);
        }
    }
    
    private function updatePrograma($programa_id) {
        try {
            error_log("=== 🔄 ACTUALIZANDO PROGRAMA ===");
            
            // ACTUALIZAR solicitud del viajero
            $solicitud_data = [
                'nombre_viajero' => trim($_POST['traveler_name'] ?? ''),
                'apellido_viajero' => trim($_POST['traveler_lastname'] ?? ''),
                'destino' => trim($_POST['destination'] ?? ''),
                'fecha_llegada' => $_POST['arrival_date'] ?? null,
                'fecha_salida' => $_POST['departure_date'] ?? null,
                'numero_pasajeros' => intval($_POST['passengers'] ?? 1),
                'acompanamiento' => trim($_POST['accompaniment'] ?? 'sin-acompanamiento')
            ];
            
            error_log("📝 Actualizando solicitud para programa $programa_id");
            error_log("Datos: " . print_r($solicitud_data, true));
            
            $result_solicitud = $this->db->update(
                'programa_solicitudes', 
                $solicitud_data, 
                'id = ?', 
                [$programa_id]
            );
            
            if ($result_solicitud === false) {
                throw new Exception('Error al actualizar datos del programa');
            }
            
            error_log("✅ Solicitud actualizada");
            
            // ACTUALIZAR personalización
            $personalizacion_data = [
                'titulo_programa' => trim($_POST['program_title'] ?? ''),
                'idioma_predeterminado' => trim($_POST['budget_language'] ?? 'es')
            ];
            
            // Procesar imagen si se subió
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = $this->uploadImage($_FILES['cover_image'], $programa_id);
                    if ($imageUrl) {
                        $personalizacion_data['foto_portada'] = $imageUrl;
                    }
                } catch (Exception $e) {
                    error_log("⚠️ Error procesando imagen: " . $e->getMessage());
                }
            }
            
            // Verificar si existe personalización
            $existing = $this->db->fetch(
                "SELECT id FROM programa_personalizacion WHERE solicitud_id = ?", 
                [$programa_id]
            );
            
            if ($existing) {
                error_log("🔄 Actualizando personalización existente ID: " . $existing['id']);
                $result_personalizacion = $this->db->update(
                    'programa_personalizacion', 
                    $personalizacion_data, 
                    'solicitud_id = ?', 
                    [$programa_id]
                );
            } else {
                error_log("➕ Creando nueva personalización");
                $personalizacion_data['solicitud_id'] = $programa_id;
                $result_personalizacion = $this->db->insert('programa_personalizacion', $personalizacion_data);
            }
            
            if ($result_personalizacion === false) {
                error_log("⚠️ Error al guardar personalización, pero continuando...");
            } else {
                error_log("✅ Personalización guardada. Resultado: $result_personalizacion");
            }
            
            // Obtener ID de solicitud
            $programa = $this->db->fetch(
                "SELECT id_solicitud FROM programa_solicitudes WHERE id = ?", 
                [$programa_id]
            );
            
            if (!$programa) {
                throw new Exception('No se pudo recuperar el programa después de la actualización');
            }
            
            error_log("✅ ACTUALIZACIÓN COMPLETA EXITOSA");
            
            return [
                'id_solicitud' => $programa['id_solicitud']
            ];
            
        } catch(Exception $e) {
            error_log("❌ Error detallado en updatePrograma: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            throw new Exception('Error al actualizar programa: ' . $e->getMessage());
        }
    }

    private function savePersonalizacion($programa_id) {
        try {
            error_log("🎨 Guardando personalización para programa $programa_id");
            
            $data = [
                'titulo_programa' => trim($_POST['program_title'] ?? ''),
                'idioma_predeterminado' => trim($_POST['budget_language'] ?? 'es')
            ];
            
            // Procesar imagen si se subió
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                error_log("📷 Procesando imagen de portada");
                try {
                    $imageUrl = $this->uploadImage($_FILES['cover_image'], $programa_id);
                    if ($imageUrl) {
                        $data['foto_portada'] = $imageUrl;
                        error_log("✅ Imagen guardada: $imageUrl");
                    }
                } catch (Exception $e) {
                    error_log("⚠️ Error procesando imagen: " . $e->getMessage());
                }
            }
            
            // Verificar si existe personalización
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
            
            if ($result === false) {
                error_log("⚠️ Error al guardar personalización");
                return false;
            }
            
            error_log("✅ Personalización guardada exitosamente");
            return true;
            
        } catch(Exception $e) {
            error_log("❌ Error en savePersonalizacion: " . $e->getMessage());
            return false;
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
            
            // Mover el archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('No se pudo subir el archivo');
            }
            
            // Generar URL relativa
            $baseUrl = rtrim($_SERVER['HTTP_HOST'], '/');
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $appPath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
            
            $imageUrl = "$scheme://$baseUrl$appPath/assets/uploads/programa/$year/$month/$filename";
            
            error_log("✅ Imagen subida exitosamente: $imageUrl");
            
            return $imageUrl;
            
        } catch(Exception $e) {
            error_log("❌ Error subiendo imagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function getPrograma($id) {
        try {
            if (!$id) {
                throw new Exception('ID de programa requerido');
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Obtener datos del programa
            $programa = $this->db->fetch(
                "SELECT ps.*, pp.titulo_programa, pp.idioma_predeterminado, pp.foto_portada 
                 FROM programa_solicitudes ps 
                 LEFT JOIN programa_personalizacion pp ON ps.id = pp.solicitud_id 
                 WHERE ps.id = ? AND ps.user_id = ?",
                [$id, $user_id]
            );
            
            if (!$programa) {
                throw new Exception('Programa no encontrado');
            }
            
            return [
                'success' => true,
                'data' => $programa
            ];
            
        } catch(Exception $e) {
            error_log("Error en getPrograma: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function listProgramas() {
        try {
            $user_id = $_SESSION['user_id'];
            
            $programas = $this->db->fetchAll(
                "SELECT ps.*, pp.titulo_programa, pp.foto_portada 
                 FROM programa_solicitudes ps 
                 LEFT JOIN programa_personalizacion pp ON ps.id = pp.solicitud_id 
                 WHERE ps.user_id = ? 
                 ORDER BY ps.created_at DESC",
                [$user_id]
            );
            
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
        try {
            if (!$id) {
                throw new Exception('ID de programa requerido');
            }
            
            $user_id = $_SESSION['user_id'];
            $this->verifyPermissions($id, $user_id);
            
            // Eliminar en orden para respetar las foreign keys
            $this->db->delete('programa_precios', 'solicitud_id = ?', [$id]);
            $this->db->delete('programa_dias_servicios', 'programa_dia_id IN (SELECT id FROM programa_dias WHERE solicitud_id = ?)', [$id]);
            $this->db->delete('programa_dias', 'solicitud_id = ?', [$id]);
            $this->db->delete('programa_personalizacion', 'solicitud_id = ?', [$id]);
            $this->db->delete('programa_solicitudes', 'id = ?', [$id]);
            
            return [
                'success' => true,
                'message' => 'Programa eliminado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en deletePrograma: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function sendError($message) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// =====================================================
// EJECUTAR API
// =====================================================

try {
    $api = new ProgramaAPI();
    $api->handleRequest();
} catch(Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}