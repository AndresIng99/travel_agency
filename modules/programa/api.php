<?php
// =====================================
// ARCHIVO: modules/programa/api.php - API COMPLETA DEL PROGRAMA
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
                case 'list':
                    $result = $this->listSolicitudes();
                    break;
                case 'create':
                    $result = $this->createSolicitud();
                    break;
                case 'update':
                    $result = $this->updateSolicitud();
                    break;
                case 'delete':
                    $result = $this->deleteSolicitud();
                    break;
                case 'get':
                    $result = $this->getSolicitud($_GET['id']);
                    break;
                case 'duplicate':
                    $result = $this->duplicateSolicitud($_POST['id']);
                    break;
                    
                // === ACCIONES ESPECÍFICAS DE PROGRAMA ===
                case 'get_programa_completo':
                    $result = $this->getProgramaCompleto($_GET['id']);
                    break;
                case 'save_personalizacion':
                    $result = $this->savePersonalizacion();
                    break;
                case 'get_biblioteca_items':
                    $result = $this->getBibliotecaItems($_GET['type']);
                    break;
                case 'save_dia':
                    $result = $this->saveDia();
                    break;
                case 'delete_dia':
                    $result = $this->deleteDia($_POST['id']);
                    break;
                case 'save_precios':
                    $result = $this->savePrecios();
                    break;
                case 'get_currencies':
                    $result = $this->getCurrencies();
                    break;
                    
                default:
                    throw new Exception('Acción no válida: ' . $action);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            
        } catch(Exception $e) {
            error_log("ProgramaAPI Error: " . $e->getMessage());
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
    
    // === GESTIÓN DE SOLICITUDES ===
    
    private function listSolicitudes() {
        try {
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Si es admin, puede ver todas las solicitudes, si no, solo las suyas
            if ($user['role'] === 'admin') {
                $sql = "SELECT s.*, u.full_name as agent_name,
                        p.titulo_programa, p.idioma_presupuesto
                        FROM programa_solicitudes s 
                        LEFT JOIN users u ON s.user_id = u.id
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        ORDER BY s.created_at DESC";
                $solicitudes = $this->db->fetchAll($sql);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_presupuesto
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.user_id = ? 
                        ORDER BY s.created_at DESC";
                $solicitudes = $this->db->fetchAll($sql, [$user_id]);
            }
            
            // Formatear fechas
            foreach($solicitudes as &$solicitud) {
                $solicitud['fecha_llegada_formatted'] = date('d/m/Y', strtotime($solicitud['fecha_llegada']));
                $solicitud['fecha_salida_formatted'] = date('d/m/Y', strtotime($solicitud['fecha_salida']));
                $solicitud['created_at_formatted'] = date('d/m/Y H:i', strtotime($solicitud['created_at']));
                
                // Calcular días del viaje
                $fecha_llegada = new DateTime($solicitud['fecha_llegada']);
                $fecha_salida = new DateTime($solicitud['fecha_salida']);
                $solicitud['dias_viaje'] = $fecha_llegada->diff($fecha_salida)->days;
            }
            
            return ['success' => true, 'data' => $solicitudes];
            
        } catch(Exception $e) {
            throw new Exception('Error listando solicitudes: ' . $e->getMessage());
        }
    }
    
    private function createSolicitud() {
        try {
            $user_id = $_SESSION['user_id'];
            
            // Generar ID único de solicitud
            $year = date('Y');
            $lastId = $this->db->fetch("SELECT id_solicitud FROM programa_solicitudes WHERE id_solicitud LIKE 'SOL{$year}%' ORDER BY id DESC LIMIT 1");
            
            if ($lastId) {
                $number = intval(substr($lastId['id_solicitud'], -3)) + 1;
            } else {
                $number = 1;
            }
            
            $id_solicitud = 'SOL' . $year . str_pad($number, 3, '0', STR_PAD_LEFT);
            
            $data = [
                'id_solicitud' => $id_solicitud,
                'nombre_viajero' => trim($_POST['nombre_viajero']),
                'apellido_viajero' => trim($_POST['apellido_viajero']),
                'destino' => trim($_POST['destino']),
                'fecha_llegada' => $_POST['fecha_llegada'],
                'fecha_salida' => $_POST['fecha_salida'],
                'numero_viajeros' => intval($_POST['numero_viajeros']),
                'acompanamiento' => trim($_POST['acompanamiento'] ?? ''),
                'user_id' => $user_id
            ];
            
            // Validar datos
            $this->validateSolicitudData($data);
            
            $solicitud_id = $this->db->insert('programa_solicitudes', $data);
            
            if (!$solicitud_id) {
                throw new Exception('Error al crear solicitud');
            }
            
            // Crear personalizacción por defecto
            $personalizacion_data = [
                'solicitud_id' => $solicitud_id,
                'titulo_programa' => 'Viaje a ' . $data['destino'],
                'idioma_presupuesto' => 'es'
            ];
            
            $this->db->insert('programa_personalizacion', $personalizacion_data);
            
            // Crear precios por defecto
            $precios_data = [
                'solicitud_id' => $solicitud_id,
                'moneda' => 'EUR',
                'condiciones_generales' => 'Condiciones generales estándar del viaje. Cancelación gratuita hasta 48 horas antes del viaje. No reembolsable después de la fecha límite.',
                'info_pasaportes_visados' => 'Se requiere pasaporte vigente con al menos 6 meses de validez. Verifique si necesita visa según su nacionalidad.',
                'info_seguros_viaje' => 'Se recomienda contratar seguro de viaje que cubra gastos médicos y cancelación. Consulte las opciones disponibles.'
            ];
            
            $this->db->insert('programa_precios', $precios_data);
            
            return ['success' => true, 'id' => $solicitud_id, 'id_solicitud' => $id_solicitud, 'message' => 'Solicitud creada correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error creando solicitud: ' . $e->getMessage());
        }
    }
    
    private function updateSolicitud() {
        try {
            $id = intval($_POST['id']);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para editar esta solicitud');
                }
            }
            
            $data = [
                'nombre_viajero' => trim($_POST['nombre_viajero']),
                'apellido_viajero' => trim($_POST['apellido_viajero']),
                'destino' => trim($_POST['destino']),
                'fecha_llegada' => $_POST['fecha_llegada'],
                'fecha_salida' => $_POST['fecha_salida'],
                'numero_viajeros' => intval($_POST['numero_viajeros']),
                'acompanamiento' => trim($_POST['acompanamiento'] ?? '')
            ];
            
            $this->validateSolicitudData($data);
            
            $result = $this->db->update('programa_solicitudes', $data, 'id = ?', [$id]);
            
            if (!$result) {
                throw new Exception('Error al actualizar solicitud');
            }
            
            return ['success' => true, 'message' => 'Solicitud actualizada correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error actualizando solicitud: ' . $e->getMessage());
        }
    }
    
    private function deleteSolicitud() {
        try {
            $id = intval($_POST['id']);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para eliminar esta solicitud');
                }
            }
            
            $result = $this->db->delete('programa_solicitudes', 'id = ?', [$id]);
            
            if (!$result) {
                throw new Exception('Error al eliminar solicitud');
            }
            
            return ['success' => true, 'message' => 'Solicitud eliminada correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error eliminando solicitud: ' . $e->getMessage());
        }
    }
    
    private function getSolicitud($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            if ($user['role'] === 'admin') {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_presupuesto, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.id = ?";
                $solicitud = $this->db->fetch($sql, [$id]);
            } else {
                $sql = "SELECT s.*, p.titulo_programa, p.idioma_presupuesto, p.foto_portada
                        FROM programa_solicitudes s 
                        LEFT JOIN programa_personalizacion p ON s.id = p.solicitud_id
                        WHERE s.id = ? AND s.user_id = ?";
                $solicitud = $this->db->fetch($sql, [$id, $user_id]);
            }
            
            if (!$solicitud) {
                throw new Exception('Solicitud no encontrada');
            }
            
            return ['success' => true, 'data' => $solicitud];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo solicitud: ' . $e->getMessage());
        }
    }
    
    // === FUNCIONES ESPECÍFICAS DE PROGRAMA ===
    
    private function getProgramaCompleto($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Obtener solicitud principal
            if ($user['role'] === 'admin') {
                $sql = "SELECT * FROM programa_solicitudes WHERE id = ?";
                $solicitud = $this->db->fetch($sql, [$id]);
            } else {
                $sql = "SELECT * FROM programa_solicitudes WHERE id = ? AND user_id = ?";
                $solicitud = $this->db->fetch($sql, [$id, $user_id]);
            }
            
            if (!$solicitud) {
                throw new Exception('Programa no encontrado');
            }
            
            // Obtener personalización
            $personalizacion = $this->db->fetch("SELECT * FROM programa_personalizacion WHERE solicitud_id = ?", [$id]);
            
            // Obtener días del itinerario
            $dias = $this->db->fetchAll("
                SELECT pd.*, bd.titulo as biblioteca_titulo, bd.imagen1 as biblioteca_imagen
                FROM programa_dias pd
                LEFT JOIN biblioteca_dias bd ON pd.biblioteca_dia_id = bd.id
                WHERE pd.solicitud_id = ?
                ORDER BY pd.dia_numero", [$id]);
            
            // Para cada día, obtener sus servicios
            foreach($dias as &$dia) {
                $servicios = $this->db->fetchAll("
                    SELECT pds.*, pds.tipo_servicio,
                           CASE 
                               WHEN pds.tipo_servicio = 'actividad' THEN ba.nombre
                               WHEN pds.tipo_servicio = 'transporte' THEN bt.titulo  
                               WHEN pds.tipo_servicio = 'alojamiento' THEN bal.nombre
                           END as nombre,
                           CASE 
                               WHEN pds.tipo_servicio = 'actividad' THEN ba.ubicacion
                               WHEN pds.tipo_servicio = 'transporte' THEN CONCAT(bt.lugar_salida, ' - ', bt.lugar_llegada)
                               WHEN pds.tipo_servicio = 'alojamiento' THEN bal.ubicacion
                           END as ubicacion,
                           CASE 
                               WHEN pds.tipo_servicio = 'actividad' THEN ba.imagen1
                               WHEN pds.tipo_servicio = 'transporte' THEN NULL
                               WHEN pds.tipo_servicio = 'alojamiento' THEN bal.imagen
                           END as imagen
                    FROM programa_dias_servicios pds
                    LEFT JOIN biblioteca_actividades ba ON pds.tipo_servicio = 'actividad' AND pds.biblioteca_item_id = ba.id
                    LEFT JOIN biblioteca_transportes bt ON pds.tipo_servicio = 'transporte' AND pds.biblioteca_item_id = bt.id
                    LEFT JOIN biblioteca_alojamientos bal ON pds.tipo_servicio = 'alojamiento' AND pds.biblioteca_item_id = bal.id
                    WHERE pds.programa_dia_id = ?
                    ORDER BY pds.tipo_servicio, pds.orden", [$dia['id']]);
                
                $dia['servicios'] = [
                    'actividades' => array_filter($servicios, fn($s) => $s['tipo_servicio'] === 'actividad'),
                    'transportes' => array_filter($servicios, fn($s) => $s['tipo_servicio'] === 'transporte'),
                    'alojamientos' => array_filter($servicios, fn($s) => $s['tipo_servicio'] === 'alojamiento')
                ];
            }
            
            // Obtener precios
            $precios = $this->db->fetch("SELECT * FROM programa_precios WHERE solicitud_id = ?", [$id]);
            
            $resultado = [
                'solicitud' => $solicitud,
                'personalizacion' => $personalizacion,
                'dias' => $dias,
                'precios' => $precios
            ];
            
            return ['success' => true, 'data' => $resultado];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo programa completo: ' . $e->getMessage());
        }
    }
    
    private function savePersonalizacion() {
        try {
            $solicitud_id = intval($_POST['solicitud_id']);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$solicitud_id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para editar este programa');
                }
            }
            
            $data = [
                'titulo_programa' => trim($_POST['titulo_programa']),
                'idioma_presupuesto' => trim($_POST['idioma_presupuesto'])
            ];
            
            // Procesar imagen de portada si se subió
            if (isset($_FILES['foto_portada']) && $_FILES['foto_portada']['error'] === UPLOAD_ERR_OK) {
                $url = $this->uploadImage($_FILES['foto_portada'], $solicitud_id, 'portada');
                $data['foto_portada'] = $url;
            }
            
            // Verificar si ya existe personalización
            $existing = $this->db->fetch("SELECT id FROM programa_personalizacion WHERE solicitud_id = ?", [$solicitud_id]);
            
            if ($existing) {
                $result = $this->db->update('programa_personalizacion', $data, 'solicitud_id = ?', [$solicitud_id]);
            } else {
                $data['solicitud_id'] = $solicitud_id;
                $result = $this->db->insert('programa_personalizacion', $data);
            }
            
            if (!$result) {
                throw new Exception('Error al guardar personalización');
            }
            
            return ['success' => true, 'message' => 'Personalización guardada correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error guardando personalización: ' . $e->getMessage());
        }
    }
    
    private function getBibliotecaItems($type) {
        try {
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            $allowedTypes = ['dias', 'actividades', 'transportes', 'alojamientos'];
            if (!in_array($type, $allowedTypes)) {
                throw new Exception('Tipo de item no válido');
            }
            
            $table = "biblioteca_" . $type;
            
            // Si es admin, puede ver todos los items, si no, solo los suyos
            if ($user['role'] === 'admin') {
                $sql = "SELECT *, CONCAT(u.full_name, ' (', u.username, ')') as created_by 
                        FROM `{$table}` b
                        LEFT JOIN users u ON b.user_id = u.id
                        WHERE activo = 1 
                        ORDER BY created_at DESC";
                $items = $this->db->fetchAll($sql);
            } else {
                $sql = "SELECT * FROM `{$table}` WHERE activo = 1 AND user_id = ? ORDER BY created_at DESC";
                $items = $this->db->fetchAll($sql, [$user_id]);
            }
            
            // Procesar URLs de imágenes
            foreach($items as &$item) {
                $imageFields = $this->getImageFields($type);
                foreach($imageFields as $field) {
                    if (!empty($item[$field])) {
                        if (strpos($item[$field], 'http') !== 0) {
                            $item[$field] = APP_URL . $item[$field];
                        }
                    }
                }
            }
            
            return ['success' => true, 'data' => $items];
            
        } catch(Exception $e) {
            throw new Exception('Error obteniendo items de biblioteca: ' . $e->getMessage());
        }
    }
    
    private function saveDia() {
        try {
            $solicitud_id = intval($_POST['solicitud_id']);
            $dia_id = intval($_POST['dia_id'] ?? 0);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$solicitud_id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para editar este programa');
                }
            }
            
            $data = [
                'solicitud_id' => $solicitud_id,
                'dia_numero' => intval($_POST['dia_numero']),
                'fecha' => $_POST['fecha'],
                'biblioteca_dia_id' => intval($_POST['biblioteca_dia_id']) ?: null,
                'titulo_jornada' => trim($_POST['titulo_jornada']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'desayuno_incluido' => isset($_POST['desayuno_incluido']) ? 1 : 0,
                'almuerzo_incluido' => isset($_POST['almuerzo_incluido']) ? 1 : 0,
                'cena_incluida' => isset($_POST['cena_incluida']) ? 1 : 0,
                'comidas_no_incluidas' => isset($_POST['comidas_no_incluidas']) ? 1 : 0
            ];
            
            if ($dia_id > 0) {
                // Actualizar día existente
                $result = $this->db->update('programa_dias', $data, 'id = ?', [$dia_id]);
                $programa_dia_id = $dia_id;
            } else {
                // Crear nuevo día
                $programa_dia_id = $this->db->insert('programa_dias', $data);
            }
            
            if (!$programa_dia_id) {
                throw new Exception('Error al guardar día');
            }
            
            // Procesar servicios (actividades, transportes, alojamientos)
            $this->procesarServicios($programa_dia_id, $_POST);
            
            return ['success' => true, 'message' => 'Día guardado correctamente', 'dia_id' => $programa_dia_id];
            
        } catch(Exception $e) {
            throw new Exception('Error guardando día: ' . $e->getMessage());
        }
    }
    
    private function procesarServicios($programa_dia_id, $postData) {
        try {
            // Eliminar servicios existentes
            $this->db->delete('programa_dias_servicios', 'programa_dia_id = ?', [$programa_dia_id]);
            
            $servicios_tipos = ['actividades', 'transportes', 'alojamientos'];
            
            foreach($servicios_tipos as $tipo) {
                $tipo_singular = rtrim($tipo, 's');
                if ($tipo_singular === 'actividade') $tipo_singular = 'actividad';
                if ($tipo_singular === 'transporte') $tipo_singular = 'transporte';
                if ($tipo_singular === 'alojamiento') $tipo_singular = 'alojamiento';
                
                if (isset($postData[$tipo]) && is_array($postData[$tipo])) {
                    $orden = 1;
                    foreach($postData[$tipo] as $item_id) {
                        if ($item_id > 0) {
                            $servicio_data = [
                                'programa_dia_id' => $programa_dia_id,
                                'tipo_servicio' => $tipo_singular,
                                'biblioteca_item_id' => intval($item_id),
                                'orden' => $orden++
                            ];
                            
                            $this->db->insert('programa_dias_servicios', $servicio_data);
                        }
                    }
                }
            }
            
        } catch(Exception $e) {
            throw new Exception('Error procesando servicios: ' . $e->getMessage());
        }
    }
    
    private function deleteDia($dia_id) {
        try {
            $dia_id = intval($dia_id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("
                    SELECT ps.user_id 
                    FROM programa_dias pd 
                    JOIN programa_solicitudes ps ON pd.solicitud_id = ps.id 
                    WHERE pd.id = ?", [$dia_id]);
                    
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para eliminar este día');
                }
            }
            
            $result = $this->db->delete('programa_dias', 'id = ?', [$dia_id]);
            
            if (!$result) {
                throw new Exception('Error al eliminar día');
            }
            
            return ['success' => true, 'message' => 'Día eliminado correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error eliminando día: ' . $e->getMessage());
        }
    }
    
    private function savePrecios() {
        try {
            $solicitud_id = intval($_POST['solicitud_id']);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$solicitud_id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para editar este programa');
                }
            }
            
            $data = [
                'moneda' => trim($_POST['moneda']),
                'precio_adulto' => floatval($_POST['precio_adulto'] ?? 0),
                'precio_adolescente' => floatval($_POST['precio_adolescente'] ?? 0),
                'precio_nino' => floatval($_POST['precio_nino'] ?? 0),
                'precio_bebe' => floatval($_POST['precio_bebe'] ?? 0),
                'noches_incluidas' => intval($_POST['noches_incluidas'] ?? 0),
                'precio_incluye' => trim($_POST['precio_incluye'] ?? ''),
                'precio_no_incluye' => trim($_POST['precio_no_incluye'] ?? ''),
                'condiciones_generales' => trim($_POST['condiciones_generales'] ?? ''),
                'apto_movilidad_reducida' => isset($_POST['apto_movilidad_reducida']) ? 1 : 0,
                'info_pasaportes_visados' => trim($_POST['info_pasaportes_visados'] ?? ''),
                'info_seguros_viaje' => trim($_POST['info_seguros_viaje'] ?? '')
            ];
            
            // Verificar si ya existen precios
            $existing = $this->db->fetch("SELECT id FROM programa_precios WHERE solicitud_id = ?", [$solicitud_id]);
            
            if ($existing) {
                $result = $this->db->update('programa_precios', $data, 'solicitud_id = ?', [$solicitud_id]);
            } else {
                $data['solicitud_id'] = $solicitud_id;
                $result = $this->db->insert('programa_precios', $data);
            }
            
            if (!$result) {
                throw new Exception('Error al guardar precios');
            }
            
            return ['success' => true, 'message' => 'Precios guardados correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error guardando precios: ' . $e->getMessage());
        }
    }
    
    private function getCurrencies() {
    try {
        $currencies = [
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'USD' => ['name' => 'Dólar Estadounidense', 'symbol' => '$'],
            'GBP' => ['name' => 'Libra Esterlina', 'symbol' => '£'],
            'COP' => ['name' => 'Peso Colombiano', 'symbol' => 'COP $'],
            'MXN' => ['name' => 'Peso Mexicano', 'symbol' => 'MX $'],
            'ARS' => ['name' => 'Peso Argentino', 'symbol' => 'AR $'],
            'BRL' => ['name' => 'Real Brasileño', 'symbol' => 'R$'],
            'CHF' => ['name' => 'Franco Suizo', 'symbol' => 'CHF'],
            'CAD' => ['name' => 'Dólar Canadiense', 'symbol' => 'CA $'],
            'JPY' => ['name' => 'Yen Japonés', 'symbol' => '¥'],
            'CNY' => ['name' => 'Yuan Chino', 'symbol' => '¥'],
            'AUD' => ['name' => 'Dólar Australiano', 'symbol' => 'AU $'],
            'PEN' => ['name' => 'Sol Peruano', 'symbol' => 'S/'],
            'CLP' => ['name' => 'Peso Chileno', 'symbol' => 'CL $'],
            'UYU' => ['name' => 'Peso Uruguayo', 'symbol' => 'UY $'],
            'BOB' => ['name' => 'Boliviano', 'symbol' => 'Bs'],
            'GTQ' => ['name' => 'Quetzal Guatemalteco', 'symbol' => 'Q'],
            'CRC' => ['name' => 'Colón Costarricense', 'symbol' => '₡'],
            'HNL' => ['name' => 'Lempira Hondureña', 'symbol' => 'L'],
            'NIO' => ['name' => 'Córdoba Nicaragüense', 'symbol' => 'C$'],
            'PAB' => ['name' => 'Balboa Panameño', 'symbol' => 'B/.'],
            'DOP' => ['name' => 'Peso Dominicano', 'symbol' => 'RD$'],
            'CUP' => ['name' => 'Peso Cubano', 'symbol' => '$'],
            'JMD' => ['name' => 'Dólar Jamaiquino', 'symbol' => 'J$'],
            'TTD' => ['name' => 'Dólar de Trinidad y Tobago', 'symbol' => 'TT$']
        ];
        
        return ['success' => true, 'data' => $currencies];
        
    } catch(Exception $e) {
        error_log("Error en getCurrencies: " . $e->getMessage());
        return ['success' => false, 'error' => 'Error al obtener monedas: ' . $e->getMessage()];
    }
}
    
    // === FUNCIONES AUXILIARES ===
    
    private function validateSolicitudData($data) {
        if (empty($data['nombre_viajero'])) {
            throw new Exception('El nombre del viajero es obligatorio');
        }
        
        if (empty($data['apellido_viajero'])) {
            throw new Exception('El apellido del viajero es obligatorio');
        }
        
        if (empty($data['destino'])) {
            throw new Exception('El destino es obligatorio');
        }
        
        if (empty($data['fecha_llegada']) || empty($data['fecha_salida'])) {
            throw new Exception('Las fechas de llegada y salida son obligatorias');
        }
        
        // Validar que la fecha de salida sea posterior a la de llegada
        if (strtotime($data['fecha_salida']) <= strtotime($data['fecha_llegada'])) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de llegada');
        }
        
        if ($data['numero_viajeros'] < 1) {
            throw new Exception('Debe haber al menos 1 viajero');
        }
    }
    
    private function duplicateSolicitud($id) {
        try {
            $id = intval($id);
            $user_id = $_SESSION['user_id'];
            $user = App::getUser();
            
            // Verificar permisos
            if ($user['role'] !== 'admin') {
                $existing = $this->db->fetch("SELECT user_id FROM programa_solicitudes WHERE id = ?", [$id]);
                if (!$existing || $existing['user_id'] != $user_id) {
                    throw new Exception('No tienes permisos para duplicar esta solicitud');
                }
            }
            
            // Obtener solicitud original
            $solicitud = $this->db->fetch("SELECT * FROM programa_solicitudes WHERE id = ?", [$id]);
            if (!$solicitud) {
                throw new Exception('Solicitud no encontrada');
            }
            
            // Generar nuevo ID de solicitud
            $year = date('Y');
            $lastId = $this->db->fetch("SELECT id_solicitud FROM programa_solicitudes WHERE id_solicitud LIKE 'SOL{$year}%' ORDER BY id DESC LIMIT 1");
            
            if ($lastId) {
                $number = intval(substr($lastId['id_solicitud'], -3)) + 1;
            } else {
                $number = 1;
            }
            
            $new_id_solicitud = 'SOL' . $year . str_pad($number, 3, '0', STR_PAD_LEFT);
            
            // Crear nueva solicitud
            unset($solicitud['id']);
            $solicitud['id_solicitud'] = $new_id_solicitud;
            $solicitud['nombre_viajero'] = 'Copia de ' . $solicitud['nombre_viajero'];
            $solicitud['estado'] = 'borrador';
            $solicitud['user_id'] = $user_id;
            
            $new_solicitud_id = $this->db->insert('programa_solicitudes', $solicitud);
            
            if (!$new_solicitud_id) {
                throw new Exception('Error al duplicar solicitud');
            }
            
            // Duplicar personalización si existe
            $personalizacion = $this->db->fetch("SELECT * FROM programa_personalizacion WHERE solicitud_id = ?", [$id]);
            if ($personalizacion) {
                unset($personalizacion['id']);
                $personalizacion['solicitud_id'] = $new_solicitud_id;
                $personalizacion['titulo_programa'] = 'Copia de ' . $personalizacion['titulo_programa'];
                $this->db->insert('programa_personalizacion', $personalizacion);
            }
            
            // Duplicar precios si existen
            $precios = $this->db->fetch("SELECT * FROM programa_precios WHERE solicitud_id = ?", [$id]);
            if ($precios) {
                unset($precios['id']);
                $precios['solicitud_id'] = $new_solicitud_id;
                $this->db->insert('programa_precios', $precios);
            }
            
            return ['success' => true, 'id' => $new_solicitud_id, 'message' => 'Solicitud duplicada correctamente'];
            
        } catch(Exception $e) {
            throw new Exception('Error duplicando solicitud: ' . $e->getMessage());
        }
    }
    
    private function uploadImage($file, $solicitud_id, $type) {
        // Validar archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
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
                throw new Exception('No se pudo crear directorio');
            }
        }
        
        // Generar nombre
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'programa_' . $solicitud_id . '_' . $type . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return APP_URL . '/assets/uploads/programa/' . $yearMonth . '/' . $fileName;
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
}

// Ejecutar API
try {
    $api = new ProgramaAPI();
    $api->handleRequest();
} catch(Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>