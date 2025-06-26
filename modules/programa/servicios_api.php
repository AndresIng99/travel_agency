<?php
// ====================================================================
// ARCHIVO: modules/programa/servicios_api.php - API PARA GESTIÓN DE SERVICIOS
// ====================================================================

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

App::init();
App::requireLogin();

class ProgramaServiciosAPI {
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
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input) {
            $_POST = array_merge($_POST, $input);
            $action = $action ?: ($input['action'] ?? '');
        }
        
        try {
            error_log("=== PROGRAMA SERVICIOS API ===");
            error_log("Action: " . $action);
            error_log("Data: " . print_r($_POST, true));
            
            switch($action) {
                case 'add_service':
                    $result = $this->addService(
                        $_POST['dia_id'] ?? null,
                        $_POST['tipo_servicio'] ?? null,
                        $_POST['biblioteca_item_id'] ?? null
                    );
                    break;
                case 'list':
                    $result = $this->listServices($_GET['dia_id'] ?? null);
                    break;
                case 'delete':
                    $result = $this->deleteService($_POST['servicio_id'] ?? null);
                    break;
                case 'update':
                    $result = $this->updateService($_POST['servicio_id'] ?? null, $_POST);
                    break;
                case 'reorder':
                    $result = $this->reorderServices($_POST['dia_id'] ?? null, $_POST['orden'] ?? []);
                    break;
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("Error en Servicios API: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendError($e->getMessage());
        }
    }
    
    private function addService($diaId, $tipoServicio, $bibliotecaItemId) {
        if (!$diaId || !$tipoServicio || !$bibliotecaItemId) {
            throw new Exception('Día, tipo de servicio e item de biblioteca requeridos');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar que el día pertenece a un programa del usuario
            $dia = $this->db->fetch(
                "SELECT pd.*, ps.user_id 
                 FROM programa_dias pd 
                 JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                 WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado o sin permisos');
            }
            
            // Verificar que el item de biblioteca existe y es del tipo correcto
            $bibliotecaItem = $this->getBibliotecaItem($tipoServicio, $bibliotecaItemId);
            
            if (!$bibliotecaItem) {
                throw new Exception('Item de biblioteca no encontrado');
            }
            
            // Obtener el siguiente orden para este día
            $lastOrder = $this->db->fetch(
                "SELECT MAX(orden) as max_orden FROM programa_dias_servicios WHERE programa_dia_id = ?", 
                [$diaId]
            );
            
            $nextOrder = ($lastOrder['max_orden'] ?? 0) + 1;
            
            // Insertar servicio
            $servicioData = [
                'programa_dia_id' => $diaId,
                'tipo_servicio' => $tipoServicio,
                'biblioteca_item_id' => $bibliotecaItemId,
                'orden' => $nextOrder
            ];
            
            $servicioId = $this->db->insert('programa_dias_servicios', $servicioData);
            
            if (!$servicioId) {
                throw new Exception('Error al insertar servicio');
            }
            
            error_log("✅ Servicio agregado: ID $servicioId, Tipo: $tipoServicio");
            
            return [
                'success' => true,
                'servicio_id' => $servicioId,
                'message' => 'Servicio agregado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en addService: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function listServices($diaId) {
        if (!$diaId) {
            throw new Exception('ID de día requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $dia = $this->db->fetch(
                "SELECT pd.*, ps.user_id 
                 FROM programa_dias pd 
                 JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                 WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado o sin permisos');
            }
            
            // Obtener servicios del día con datos de biblioteca
            $servicios = $this->db->fetchAll(
                "SELECT 
                    pds.*,
                    CASE 
                        WHEN pds.tipo_servicio = 'actividad' THEN ba.titulo
                        WHEN pds.tipo_servicio = 'transporte' THEN bt.titulo
                        WHEN pds.tipo_servicio = 'alojamiento' THEN bal.nombre
                    END as titulo,
                    CASE 
                        WHEN pds.tipo_servicio = 'actividad' THEN ba.descripcion
                        WHEN pds.tipo_servicio = 'transporte' THEN bt.descripcion
                        WHEN pds.tipo_servicio = 'alojamiento' THEN bal.descripcion
                    END as descripcion,
                    CASE 
                        WHEN pds.tipo_servicio = 'actividad' THEN ba.ubicacion
                        WHEN pds.tipo_servicio = 'transporte' THEN CONCAT(bt.lugar_salida, ' → ', bt.lugar_llegada)
                        WHEN pds.tipo_servicio = 'alojamiento' THEN bal.ubicacion
                    END as ubicacion,
                    CASE 
                        WHEN pds.tipo_servicio = 'transporte' THEN bt.medio
                        ELSE NULL
                    END as medio,
                    CASE 
                        WHEN pds.tipo_servicio = 'transporte' THEN bt.lugar_salida
                        ELSE NULL
                    END as lugar_salida,
                    CASE 
                        WHEN pds.tipo_servicio = 'transporte' THEN bt.lugar_llegada
                        ELSE NULL
                    END as lugar_llegada,
                    CASE 
                        WHEN pds.tipo_servicio = 'alojamiento' THEN bal.nombre
                        ELSE NULL
                    END as nombre
                FROM programa_dias_servicios pds
                LEFT JOIN biblioteca_actividades ba ON pds.tipo_servicio = 'actividad' AND pds.biblioteca_item_id = ba.id
                LEFT JOIN biblioteca_transportes bt ON pds.tipo_servicio = 'transporte' AND pds.biblioteca_item_id = bt.id
                LEFT JOIN biblioteca_alojamientos bal ON pds.tipo_servicio = 'alojamiento' AND pds.biblioteca_item_id = bal.id
                WHERE pds.programa_dia_id = ?
                ORDER BY pds.orden ASC", 
                [$diaId]
            );
            
            return [
                'success' => true,
                'data' => $servicios
            ];
            
        } catch(Exception $e) {
            error_log("Error en listServices: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function deleteService($servicioId) {
        if (!$servicioId) {
            throw new Exception('ID de servicio requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $servicio = $this->db->fetch(
                "SELECT pds.*, ps.user_id 
                 FROM programa_dias_servicios pds
                 JOIN programa_dias pd ON pds.programa_dia_id = pd.id
                 JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                 WHERE pds.id = ? AND ps.user_id = ?", 
                [$servicioId, $user_id]
            );
            
            if (!$servicio) {
                throw new Exception('Servicio no encontrado o sin permisos');
            }
            
            // Eliminar servicio
            $deleted = $this->db->delete('programa_dias_servicios', 'id = ?', [$servicioId]);
            
            if (!$deleted) {
                throw new Exception('Error al eliminar servicio');
            }
            
            // Reordenar servicios restantes
            $this->reorderServicesAfterDelete($servicio['programa_dia_id'], $servicio['orden']);
            
            return [
                'success' => true,
                'message' => 'Servicio eliminado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en deleteService: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateService($servicioId, $data) {
        if (!$servicioId) {
            throw new Exception('ID de servicio requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $servicio = $this->db->fetch(
                "SELECT pds.*, ps.user_id 
                 FROM programa_dias_servicios pds
                 JOIN programa_dias pd ON pds.programa_dia_id = pd.id
                 JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                 WHERE pds.id = ? AND ps.user_id = ?", 
                [$servicioId, $user_id]
            );
            
            if (!$servicio) {
                throw new Exception('Servicio no encontrado o sin permisos');
            }
            
            // Preparar datos para actualizar
            $updateData = [];
            $allowedFields = ['orden'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (empty($updateData)) {
                throw new Exception('No hay datos para actualizar');
            }
            
            // Actualizar servicio
            $updated = $this->db->update('programa_dias_servicios', $updateData, 'id = ?', [$servicioId]);
            
            if (!$updated) {
                throw new Exception('Error al actualizar servicio');
            }
            
            return [
                'success' => true,
                'message' => 'Servicio actualizado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en updateService: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function reorderServices($diaId, $orden) {
        if (!$diaId || !is_array($orden)) {
            throw new Exception('ID de día y orden requeridos');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $dia = $this->db->fetch(
                "SELECT pd.*, ps.user_id 
                 FROM programa_dias pd 
                 JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                 WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado o sin permisos');
            }
            
            // Actualizar orden de servicios
            foreach ($orden as $index => $servicioId) {
                $this->db->update(
                    'programa_dias_servicios', 
                    ['orden' => $index + 1], 
                    'id = ? AND programa_dia_id = ?', 
                    [$servicioId, $diaId]
                );
            }
            
            return [
                'success' => true,
                'message' => 'Orden actualizado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en reorderServices: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function getBibliotecaItem($tipoServicio, $itemId) {
        try {
            switch($tipoServicio) {
                case 'actividad':
                    return $this->db->fetch(
                        "SELECT * FROM biblioteca_actividades WHERE id = ? AND activo = 1", 
                        [$itemId]
                    );
                case 'transporte':
                    return $this->db->fetch(
                        "SELECT * FROM biblioteca_transportes WHERE id = ? AND activo = 1", 
                        [$itemId]
                    );
                case 'alojamiento':
                    return $this->db->fetch(
                        "SELECT * FROM biblioteca_alojamientos WHERE id = ? AND activo = 1", 
                        [$itemId]
                    );
                default:
                    return null;
            }
        } catch(Exception $e) {
            error_log("Error obteniendo item de biblioteca: " . $e->getMessage());
            return null;
        }
    }
    
    private function reorderServicesAfterDelete($diaId, $deletedOrder) {
        try {
            // Reordenar servicios posteriores al eliminado
            $this->db->execute(
                "UPDATE programa_dias_servicios 
                 SET orden = orden - 1 
                 WHERE programa_dia_id = ? AND orden > ?", 
                [$diaId, $deletedOrder]
            );
            
        } catch(Exception $e) {
            error_log("Error reordenando servicios: " . $e->getMessage());
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

// Instanciar y ejecutar API
$api = new ProgramaServiciosAPI();
$api->handleRequest();