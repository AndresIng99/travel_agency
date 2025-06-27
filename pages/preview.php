<?php
// ====================================================================
// ARCHIVO: pages/preview.php - VISTA PREVIA TIPO LANDING PAGE
// ====================================================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

// Obtener ID del programa desde URL
$programa_id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

if (!$programa_id) {
    header('Location: ' . APP_URL . '/itinerarios');
    exit;
}

try {
    ConfigManager::init();
    $company_name = ConfigManager::getCompanyName();
} catch(Exception $e) {
    $company_name = 'Travel Agency';
}

// Cargar datos del programa
try {
    $db = Database::getInstance();
    
    // Obtener datos básicos del programa
    $programa = $db->fetch(
        "SELECT ps.*, pp.titulo_programa, pp.foto_portada 
         FROM programa_solicitudes ps 
         LEFT JOIN programa_personalizacion pp ON ps.id = pp.solicitud_id 
         WHERE ps.id = ?", 
        [$programa_id]
    );
    
    if (!$programa) {
        throw new Exception('Programa no encontrado');
    }
    
    // Obtener días del programa
    $dias = $db->fetchAll(
        "SELECT * FROM programa_dias WHERE solicitud_id = ? ORDER BY dia_numero ASC", 
        [$programa_id]
    );
    
    // Calcular duración
    $duracion = 'N/A';
    if ($programa['fecha_llegada'] && $programa['fecha_salida']) {
        $llegada = new Date($programa['fecha_llegada']);
        $salida = new Date($programa['fecha_salida']);
        $dias_duracion = (strtotime($programa['fecha_salida']) - strtotime($programa['fecha_llegada'])) / (60*60*24);
        $duracion = $dias_duracion > 0 ? $dias_duracion . ' días' : '1 día';
    }
    
} catch(Exception $e) {
    error_log("Error cargando programa para preview: " . $e->getMessage());
    header('Location: ' . APP_URL . '/itinerarios');
    exit;
}

// Datos para la vista
$titulo_programa = $programa['titulo_programa'] ?: 'Mi Viaje a ' . $programa['destino'];
$nombre_viajero = $programa['nombre_viajero'] . ' ' . $programa['apellido_viajero'];
$imagen_portada = $programa['foto_portada'] ?: APP_URL . '/assets/images/default-travel.jpg';
$destino = $programa['destino'];
$num_dias = count($dias);
$num_pasajeros = $programa['numero_pasajeros'];

// URL única para compartir
$share_url = APP_URL . '/preview?id=' . $programa_id . '&token=' . md5($programa_id . 'travel_secret');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_programa) ?> - Vista Previa</title>
    
    <!-- Meta tags para compartir -->
    <meta property="og:title" content="<?= htmlspecialchars($titulo_programa) ?>">
    <meta property="og:description" content="Programa de viaje personalizado para <?= htmlspecialchars($nombre_viajero) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($imagen_portada) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($share_url) ?>">
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        /* Fondo con imagen full screen */
        .hero-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-image: url('<?= htmlspecialchars($imagen_portada) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -2;
            
            /* Efecto zoom sutil */
            animation: subtle-zoom 20s ease-in-out infinite alternate;
        }
        
        @keyframes subtle-zoom {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.05);
            }
        }
        
        /* Overlay oscuro para mejor legibilidad */
        .hero-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(
                135deg,
                rgba(0, 0, 0, 0.7) 0%,
                rgba(0, 0, 0, 0.4) 50%,
                rgba(0, 0, 0, 0.6) 100%
            );
            z-index: -1;
        }
        
        /* Container principal */
        .preview-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px;
            position: relative;
        }
        
        /* Barra lateral con información */
        .info-sidebar {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.95) 0%, 
                rgba(255, 255, 255, 0.90) 100%
            );
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 10;
        }
        
        /* Efectos de glassmorphism */
        .info-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 100%
            );
            border-radius: 24px;
            z-index: -1;
        }
        
        /* Encabezado del programa */
        .program-header {
            margin-bottom: 32px;
        }
        
        .program-subtitle {
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .program-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        
        .program-for {
            font-size: 18px;
            color: #4b5563;
            font-weight: 400;
        }
        
        .traveler-name {
            color: #059669;
            font-weight: 600;
        }
        
        /* Detalles del viaje */
        .trip-details {
            margin-bottom: 40px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .detail-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .detail-icon {
            font-size: 24px;
            color: #059669;
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .detail-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Información adicional */
        .trip-summary {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 32px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #4b5563;
        }
        
        .summary-item:last-child {
            margin-bottom: 0;
        }
        
        .summary-item i {
            color: #059669;
            width: 16px;
        }
        
        /* Botón principal */
        .discover-button {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 18px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 
                0 10px 25px rgba(5, 150, 105, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .discover-button:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 15px 35px rgba(5, 150, 105, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }
        
        .discover-button:active {
            transform: translateY(0);
        }
        
        /* Logo de la agencia */
        .agency-logo {
            position: absolute;
            top: 40px;
            right: 40px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Efectos de partículas (opcional) */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px);
                opacity: 0.6;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .preview-container {
                padding: 20px;
            }
            
            .info-sidebar {
                padding: 32px 24px;
                max-width: 100%;
            }
            
            .program-title {
                font-size: 2rem;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .agency-logo {
                top: 20px;
                right: 20px;
                padding: 8px 16px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .program-title {
                font-size: 1.75rem;
            }
            
            .info-sidebar {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Fondo con imagen -->
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    
    <!-- Elementos flotantes decorativos -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>
    
    <!-- Logo de la agencia -->
    <div class="agency-logo">
        <i class="fas fa-plane"></i>
        <?= htmlspecialchars($company_name) ?>
    </div>
    
    <!-- Container principal -->
    <div class="preview-container">
        <div class="info-sidebar">
            <!-- Encabezado del programa -->
            <div class="program-header">
                <div class="program-subtitle">Mi viaje a</div>
                <h1 class="program-title"><?= htmlspecialchars($destino) ?></h1>
                <div class="program-for">
                    para <span class="traveler-name"><?= htmlspecialchars($nombre_viajero) ?></span>
                </div>
            </div>
            
            <!-- Detalles del viaje -->
            <div class="trip-details">
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="detail-value"><?= $duracion ?></div>
                        <div class="detail-label">Duración</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="detail-value"><?= $num_pasajeros ?></div>
                        <div class="detail-label"><?= $num_pasajeros == 1 ? 'Viajero' : 'Viajeros' ?></div>
                    </div>
                </div>
                
                <!-- Resumen del viaje -->
                <div class="trip-summary">
                    <div class="summary-item">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Itinerario personalizado de <?= $num_dias ?> días</span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-heart"></i>
                        <span>Pensado especialmente para ti</span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Viaje seguro e inolvidable</span>
                    </div>
                </div>
            </div>
            
            <!-- Botón principal -->
            <button class="discover-button" onclick="window.location.href='<?= APP_URL ?>/programa?id=<?= $programa_id ?>'">
                <i class="fas fa-compass"></i>
                Descubrir mi programa
            </button>
        </div>
    </div>
    
    <script>
        // Efecto parallax sutil al hacer scroll
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const background = document.querySelector('.hero-background');
            background.style.transform = `translateY(${scrolled * 0.5}px)`;
        });
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.querySelector('.info-sidebar');
            sidebar.style.opacity = '0';
            sidebar.style.transform = 'translateX(-50px)';
            
            setTimeout(() => {
                sidebar.style.transition = 'all 1s ease';
                sidebar.style.opacity = '1';
                sidebar.style.transform = 'translateX(0)';
            }, 300);
        });
    </script>
</body>
</html>