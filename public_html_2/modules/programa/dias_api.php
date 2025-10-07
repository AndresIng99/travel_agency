<?php
// ====================================================================
// ARCHIVO: modules/programa/dias_api.php - API PARA GESTIÓN DE DÍAS
// ====================================================================

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

App::init();
App::requireLogin();

class ProgramaDiasAPI {
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
            error_log("=== PROGRAMA DÍAS API ===");
            error_log("Action: " . $action);
            error_log("Data: " . print_r($_POST, true));
            
            switch($action) {
                case 'list':
                    $result = $this->listDias($_GET['programa_id'] ?? null);
                    break;
                case 'add_from_biblioteca':
                    $result = $this->addDiaFromBiblioteca($_POST['programa_id'] ?? null, $_POST['biblioteca_dia_id'] ?? null);
                    break;
                case 'delete':
                    $result = $this->deleteDia($_POST['dia_id'] ?? null);
                    break;
                case 'update':
                    $result = $this->updateDia($_POST['dia_id'] ?? null, $_POST);
                    break;
                case 'reorder':
                    $result = $this->reorderDias($_POST['programa_id'] ?? null, $_POST['orden'] ?? []);
                    break;
                case 'cambiar_estancia':
                    $result = $this->cambiarEstancia($_POST['dia_id'] ?? null, $_POST['duracion'] ?? null);
                    break;

                case 'update_comidas':
                    $result = $this->updateComidas($_POST['dia_id'] ?? null, $_POST);
                    break;
                case 'get_comidas':
                    $result = $this->getComidas($_GET['dia_id'] ?? null);
                    break;
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("Error en Días API: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendError($e->getMessage());
        }
    }
    
    private function listDias($programaId) {
        if (!$programaId) {
            throw new Exception('ID de programa requerido');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar que el programa pertenece al usuario
            $programa = $this->db->fetch(
                "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
                [$programaId, $user_id]
            );
            
            if (!$programa) {
                throw new Exception('Programa no encontrado o sin permisos');
            }
            
            // Obtener días del programa
            $dias = $this->db->fetchAll(
                "SELECT *, COALESCE(duracion_estancia, 1) as duracion_estancia FROM programa_dias WHERE solicitud_id = ? ORDER BY dia_numero ASC", 
                [$programaId]
            );
                        
            return [
                'success' => true,
                'data' => $dias
            ];
            
        } catch(Exception $e) {
            error_log("Error en listDias: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function addDiaFromBiblioteca($programaId, $bibliotecaDiaId) {
        if (!$programaId || !$bibliotecaDiaId) {
            throw new Exception('ID de programa y día de biblioteca requeridos');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos del programa
            $programa = $this->db->fetch(
                "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
                [$programaId, $user_id]
            );
            
            if (!$programa) {
                throw new Exception('Programa no encontrado o sin permisos');
            }
            
            // Obtener datos del día de biblioteca
            $bibliotecaDia = $this->db->fetch(
                "SELECT * FROM biblioteca_dias WHERE id = ? AND activo = 1", 
                [$bibliotecaDiaId]
            );
            
            if (!$bibliotecaDia) {
                throw new Exception('Día de biblioteca no encontrado');
            }
            
            // Obtener el siguiente número de día
            $lastDia = $this->db->fetch(
                "SELECT MAX(dia_numero) as max_dia FROM programa_dias WHERE solicitud_id = ?", 
                [$programaId]
            );
            
            $nextDiaNumber = ($lastDia['max_dia'] ?? 0) + 1;
            
            // Insertar día en el programa
            $diaData = [
                'solicitud_id' => $programaId,
                'dia_numero' => $nextDiaNumber,
                'titulo' => $bibliotecaDia['titulo'],
                'descripcion' => $bibliotecaDia['descripcion'],
                'ubicacion' => $bibliotecaDia['ubicacion'],
                'duracion_estancia' => 1, // ← AGREGAR ESTA LÍNEA
                'imagen1' => $bibliotecaDia['imagen1'],
                'imagen2' => $bibliotecaDia['imagen2'],
                'imagen3' => $bibliotecaDia['imagen3']
            ];
            
            $diaId = $this->db->insert('programa_dias', $diaData);
            
            if (!$diaId) {
                throw new Exception('Error al insertar día en el programa');
            }
            
            error_log("✅ Día agregado al programa: ID $diaId");
            
            return [
                'success' => true,
                'dia_id' => $diaId,
                'message' => 'Día agregado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en addDiaFromBiblioteca: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function deleteDia($diaId) {
        if (!$diaId) {
            throw new Exception('ID de día requerido');
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
            
            // Eliminar servicios del día
            $this->db->delete('programa_dias_servicios', 'programa_dia_id = ?', [$diaId]);
            
            // Eliminar día
            $deleted = $this->db->delete('programa_dias', 'id = ?', [$diaId]);
            
            if (!$deleted) {
                throw new Exception('Error al eliminar día');
            }
            
            // Reordenar días restantes
            $this->reorderDiasAfterDelete($dia['solicitud_id'], $dia['dia_numero']);
            
            return [
                'success' => true,
                'message' => 'Día eliminado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en deleteDia: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateDia($diaId, $data) {
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
            
            // Preparar datos para actualizar
            $updateData = [];
            $allowedFields = ['titulo', 'descripcion', 'ubicacion', 'fecha_dia'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (empty($updateData)) {
                throw new Exception('No hay datos para actualizar');
            }
            
            // Actualizar día
            $updated = $this->db->update('programa_dias', $updateData, 'id = ?', [$diaId]);
            
            if (!$updated) {
                throw new Exception('Error al actualizar día');
            }
            
            return [
                'success' => true,
                'message' => 'Día actualizado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en updateDia: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function reorderDias($programaId, $orden) {
        if (!$programaId || !is_array($orden)) {
            throw new Exception('ID de programa y orden requeridos');
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            
            // Verificar permisos
            $programa = $this->db->fetch(
                "SELECT id FROM programa_solicitudes WHERE id = ? AND user_id = ?", 
                [$programaId, $user_id]
            );
            
            if (!$programa) {
                throw new Exception('Programa no encontrado o sin permisos');
            }
            
            // Actualizar orden de días
            foreach ($orden as $index => $diaId) {
                $this->db->update(
                    'programa_dias', 
                    ['dia_numero' => $index + 1], 
                    'id = ? AND solicitud_id = ?', 
                    [$diaId, $programaId]
                );
            }
            
            return [
                'success' => true,
                'message' => 'Orden actualizado exitosamente'
            ];
            
        } catch(Exception $e) {
            error_log("Error en reorderDias: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function reorderDiasAfterDelete($programaId, $deletedDiaNumber) {
        try {
            // Reordenar días posteriores al eliminado
            $this->db->execute(
                "UPDATE programa_dias 
                 SET dia_numero = dia_numero - 1 
                 WHERE solicitud_id = ? AND dia_numero > ?", 
                [$programaId, $deletedDiaNumber]
            );
            
        } catch(Exception $e) {
            error_log("Error reordenando días: " . $e->getMessage());
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

    private function cambiarEstancia($diaId, $nuevaDuracion) {
        try {
            $diaId = (int)$diaId;
            $nuevaDuracion = (int)$nuevaDuracion;
            $user_id = $_SESSION['user_id'];
            
            if ($diaId <= 0 || $nuevaDuracion < 1 || $nuevaDuracion > 30) {
                throw new Exception('Datos no válidos');
            }
            
            // Verificar permisos
            $dia = $this->db->fetch(
                "SELECT pd.*, ps.user_id 
                FROM programa_dias pd 
                JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado');
            }
            
            // Actualizar duración
            $this->db->update('programa_dias', 
                ['duracion_estancia' => $nuevaDuracion], 
                'id = ?', 
                [$diaId]
            );
            
            // Recalcular números de días
            $this->recalcularNumerosDias($dia['solicitud_id']);
            return [
                'success' => true,
                'message' => 'Estancia actualizada correctamente'
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error al cambiar estancia: ' . $e->getMessage());
        }
    }

    private function recalcularNumerosDias($solicitudId) {
        // Obtener todos los días ordenados
        $dias = $this->db->fetchAll(
            "SELECT id, duracion_estancia FROM programa_dias 
            WHERE solicitud_id = ? ORDER BY dia_numero ASC", 
            [$solicitudId]
        );
        
        $diaActual = 1;
        foreach ($dias as $dia) {
            $this->db->update('programa_dias', 
                ['dia_numero' => $diaActual], 
                'id = ?', 
                [$dia['id']]
            );
            $diaActual += (int)($dia['duracion_estancia'] ?? 1);
        }
    }
    private function updateComidas($diaId, $data) {
        try {
            $diaId = (int)$diaId;
            $user_id = $_SESSION['user_id'];
            
            if ($diaId <= 0) {
                throw new Exception('ID de día no válido');
            }
            
            // Verificar permisos
            $dia = $this->db->fetch(
                "SELECT pd.*, ps.user_id 
                FROM programa_dias pd 
                JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado');
            }
            
            $comidasIncluidas = (int)($data['comidas_incluidas'] ?? 0);
            $desayuno = (int)($data['desayuno'] ?? 0);
            $almuerzo = (int)($data['almuerzo'] ?? 0);
            $cena = (int)($data['cena'] ?? 0);
            
            // Si no incluye comidas, poner todo en 0
            if ($comidasIncluidas == 0) {
                $desayuno = $almuerzo = $cena = 0;
            }
            
            // Actualizar
            $this->db->update('programa_dias', [
                'comidas_incluidas' => $comidasIncluidas,
                'desayuno' => $desayuno,
                'almuerzo' => $almuerzo,
                'cena' => $cena
            ], 'id = ?', [$diaId]);
            
            return [
                'success' => true,
                'message' => 'Comidas actualizadas correctamente'
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error actualizando comidas: ' . $e->getMessage());
        }
    }

    private function getComidas($diaId) {
        try {
            $diaId = (int)$diaId;
            $user_id = $_SESSION['user_id'];
            
            if ($diaId <= 0) {
                throw new Exception('ID de día no válido');
            }
            
            // Verificar permisos y obtener datos
            $dia = $this->db->fetch(
                "SELECT pd.comidas_incluidas, pd.desayuno, pd.almuerzo, pd.cena, ps.user_id 
                FROM programa_dias pd 
                JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                WHERE pd.id = ? AND ps.user_id = ?", 
                [$diaId, $user_id]
            );
            
            if (!$dia) {
                throw new Exception('Día no encontrado');
            }
            
            return [
                'success' => true,
                'data' => [
                    'comidas_incluidas' => (int)$dia['comidas_incluidas'],
                    'desayuno' => (int)$dia['desayuno'],
                    'almuerzo' => (int)$dia['almuerzo'],
                    'cena' => (int)$dia['cena']
                ]
            ];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo comidas: ' . $e->getMessage());
        }
    }
}

// Instanciar y ejecutar API
$api = new ProgramaDiasAPI();
$api->handleRequest();