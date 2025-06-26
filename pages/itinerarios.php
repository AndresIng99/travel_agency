<?php
// ====================================================================
// ARCHIVO: pages/itinerarios.php - COMPLETAMENTE MEJORADO
// ====================================================================

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/app.php';

App::init();
App::requireLogin();

$user = App::getUser();
$company_name = defined('APP_NAME') ? APP_NAME : 'Travel Agency';

try {
    require_once dirname(__DIR__) . '/config/config_functions.php';
    ConfigManager::init();
    $company_name = ConfigManager::getCompanyName();
} catch(Exception $e) {
    // Usar valor por defecto si hay error
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Programas - <?= $company_name ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2d5a4a;
            --secondary-color: #4a7c59;
            --accent-color: #7db46c;
            --background-color: #f8fffe;
            --card-background: #ffffff;
            --text-color: #1a202c;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-large: 0 10px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Navegación superior */
        .top-nav {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-medium);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .top-nav .logo {
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            border-bottom: 2px solid white;
            padding-bottom: 2px;
        }

        .top-nav .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .top-nav .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
        }

        .top-nav .nav-links a:hover {
            color: var(--accent-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Container principal */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header de página */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Estadísticas rápidas */
        .stats-section {
            margin-bottom: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-background);
            padding: 24px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: block;
        }

        .stat-label {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .stat-icon {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 12px;
        }

        /* Acciones rápidas */
        .quick-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .action-btn.secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .action-btn.secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Sección de programas */
        .programs-section {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary-color);
        }

        /* Filtros y búsqueda */
        .filters-container {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 10px 16px 10px 40px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 14px;
            width: 250px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        /* Grid de programas */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        /* Tarjeta de programa */
        .program-card {
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .program-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-large);
            border-color: var(--primary-color);
        }

        .program-image {
            height: 160px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            position: relative;
            overflow: hidden;
        }

        .program-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .program-image .placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: white;
            font-size: 2rem;
        }

        .program-status {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            backdrop-filter: blur(10px);
        }

        .status-draft {
            background: rgba(156, 163, 175, 0.9);
            color: white;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.9);
            color: white;
        }

        .status-completed {
            background: rgba(59, 130, 246, 0.9);
            color: white;
        }

        .program-content {
            padding: 20px;
        }

        .program-header {
            margin-bottom: 16px;
        }

        .program-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .program-destination {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .program-traveler {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .program-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 16px 0;
            padding: 16px 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .detail-value.highlight {
            color: var(--primary-color);
        }

        .program-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-primary-sm {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary-sm:hover {
            background: var(--secondary-color);
            color: white;
        }

        .btn-outline-sm {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline-sm:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Estados de carga */
        .loading-state, .empty-state, .error-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .state-icon {
            font-size: 4rem;
            margin-bottom: 24px;
            color: var(--primary-color);
        }

        .loading-state .state-icon {
            animation: spin 1s linear infinite;
        }

        .empty-state .state-icon {
            color: var(--text-muted);
        }

        .error-state .state-icon {
            color: #ef4444;
        }

        .state-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-color);
        }

        .state-description {
            font-size: 1rem;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .programs-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 20px 15px;
            }

            .page-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .programs-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-container {
                justify-content: center;
            }

            .search-input {
                width: 100%;
                max-width: 300px;
            }

            .quick-actions {
                flex-direction: column;
                align-items: center;
            }

            .action-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .top-nav .nav-links {
                gap: 15px;
            }

            .user-info span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .program-details {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .programs-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navegación Superior -->
    <nav class="top-nav">
        <a href="<?= APP_URL ?>/dashboard" class="logo">Trip Planner</a>
        <div class="nav-links">
            <a href="<?= APP_URL ?>/programa">Mi Programa</a>
            <a href="<?= APP_URL ?>/biblioteca">Biblioteca</a>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($user['name'] ?? 'Usuario') ?></span>
            </div>
        </div>
    </nav>

    <!-- Container Principal -->
    <div class="main-container">
        <!-- Header de Página -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-route"></i>
                Mis Programas de Viaje
            </h1>
            <p class="page-subtitle">
                Gestiona y visualiza todos tus itinerarios de manera elegante y eficiente
            </p>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-plane stat-icon"></i>
                    <span class="stat-number" id="totalProgramas">0</span>
                    <div class="stat-label">Total Programas</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock stat-icon"></i>
                    <span class="stat-number" id="programasBorrador">0</span>
                    <div class="stat-label">En Borrador</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle stat-icon"></i>
                    <span class="stat-number" id="programasActivos">0</span>
                    <div class="stat-label">Activos</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <span class="stat-number" id="totalViajeros">0</span>
                    <div class="stat-label">Total Viajeros</div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="quick-actions">
                <a href="<?= APP_URL ?>/programa" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Crear Nuevo Programa
                </a>
                <button onclick="cargarProgramas()" class="action-btn secondary">
                    <i class="fas fa-sync"></i>
                    Actualizar Lista
                </button>
            </div>
        </div>

        <!-- Sección de Programas -->
        <div class="programs-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Mis Programas
                </h2>
                
                <div class="filters-container">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="Buscar programas..."
                            oninput="filtrarProgramas()"
                        >
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filtrarProgramas()">
                        <option value="">Todos los estados</option>
                        <option value="borrador">Borrador</option>
                        <option value="activo">Activo</option>
                        <option value="completado">Completado</option>
                    </select>
                </div>
            </div>

            <!-- Container de Programas -->
            <div id="programsContainer">
                <div class="loading-state">
                    <i class="fas fa-spinner state-icon"></i>
                    <h3 class="state-title">Cargando programas...</h3>
                    <p class="state-description">Por favor espera mientras obtenemos tus programas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ====================================================================
        // JAVASCRIPT MEJORADO PARA ITINERARIOS
        // ====================================================================
        
        let allProgramas = [];
        let filteredProgramas = [];
        
        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Iniciando página de itinerarios...');
            cargarProgramas();
        });

        // ============================================================
        // FUNCIONES DE CARGA DE DATOS
        // ============================================================
        
        async function cargarProgramas() {
            console.log('📥 Cargando programas...');
            
            showLoadingState();
            
            try {
                const response = await fetch('<?= APP_URL ?>/programa/api?action=list');
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('📋 Datos recibidos:', result);
                
                if (result.success) {
                    allProgramas = result.data || [];
                    filteredProgramas = [...allProgramas];
                    
                    actualizarEstadisticas();
                    mostrarProgramas();
                    
                    console.log(`✅ ${allProgramas.length} programas cargados`);
                } else {
                    throw new Error(result.error || 'Error al cargar programas');
                }
                
            } catch (error) {
                console.error('❌ Error cargando programas:', error);
                showErrorState(error.message);
            }
        }

        // ============================================================
        // FUNCIONES DE VISUALIZACIÓN
        // ============================================================
        
        function mostrarProgramas() {
            const container = document.getElementById('programsContainer');
            
            if (!filteredProgramas || filteredProgramas.length === 0) {
                showEmptyState();
                return;
            }
            
            const programsGrid = document.createElement('div');
            programsGrid.className = 'programs-grid';
            
            filteredProgramas.forEach(programa => {
                const card = crearTarjetaPrograma(programa);
                programsGrid.appendChild(card);
            });
            
            container.innerHTML = '';
            container.appendChild(programsGrid);
        }
        
        function crearTarjetaPrograma(programa) {
            const card = document.createElement('div');
            card.className = 'program-card';
            card.onclick = () => editarPrograma(programa.id);
            
            // Calcular duración
            let duracion = 'N/A';
            if (programa.fecha_llegada && programa.fecha_salida) {
                const llegada = new Date(programa.fecha_llegada);
                const salida = new Date(programa.fecha_salida);
                const dias = Math.ceil((salida - llegada) / (1000 * 60 * 60 * 24));
                duracion = dias > 0 ? `${dias} días` : '1 día';
            }
            
            // Determinar estado - adaptado para tu base de datos
            let estado = 'borrador';
            if (programa.estado) {
                estado = programa.estado;
            } else if (programa.id_solicitud) {
                estado = 'activo'; // Si tiene ID de solicitud, considerarlo activo
            }
            
            const estadoClass = {
                'borrador': 'status-draft',
                'activo': 'status-active', 
                'completado': 'status-completed'
            }[estado] || 'status-draft';
            
            const estadoText = {
                'borrador': 'Borrador',
                'activo': 'Activo',
                'completado': 'Completado'
            }[estado] || 'Borrador';
            
            // Usar foto_portada del programa_personalizacion si existe
            const imagenPortada = programa.foto_portada || null;
            
            card.innerHTML = `
                <div class="program-image">
                    ${imagenPortada ? 
                        `<img src="${imagenPortada}" alt="Portada del programa">` : 
                        `<div class="placeholder"><i class="fas fa-map-marked-alt"></i></div>`
                    }
                    <div class="program-status ${estadoClass}">${estadoText}</div>
                </div>
                
                <div class="program-content">
                    <div class="program-header">
                        <h3 class="program-title">
                            ${programa.titulo_programa || `Viaje a ${programa.destino}`}
                        </h3>
                        <div class="program-destination">
                            <i class="fas fa-map-marker-alt"></i>
                            ${programa.destino}
                        </div>
                        <div class="program-traveler">
                            <i class="fas fa-user"></i>
                            ${programa.nombre_viajero} ${programa.apellido_viajero}
                        </div>
                    </div>
                    
                    <div class="program-details">
                        <div class="detail-item">
                            <div class="detail-label">Duración</div>
                            <div class="detail-value highlight">${duracion}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Viajeros</div>
                            <div class="detail-value">${programa.numero_pasajeros}</div>
                        </div>
                    </div>
                    
                    <div class="program-actions">
                        <a href="<?= APP_URL ?>/programa?id=${programa.id}" class="btn-sm btn-primary-sm">
                            <i class="fas fa-edit"></i>
                            Editar
                        </a>
                        <button onclick="event.stopPropagation(); verDetalles(${programa.id})" class="btn-sm btn-outline-sm">
                            <i class="fas fa-eye"></i>
                            Ver
                        </button>
                    </div>
                </div>
            `;
            
            return card;
        }

        // ============================================================
        // FUNCIONES DE ESTADÍSTICAS
        // ============================================================
        
        function actualizarEstadisticas() {
            const total = allProgramas.length;
            
            // Contar borradores (sin id_solicitud o estado borrador)
            const borrador = allProgramas.filter(p => 
                !p.id_solicitud || p.estado === 'borrador'
            ).length;
            
            // Contar activos (con id_solicitud y no completados)
            const activos = allProgramas.filter(p => 
                p.id_solicitud && p.estado !== 'completado'
            ).length;
            
            const totalViajeros = allProgramas.reduce((sum, p) => 
                sum + (parseInt(p.numero_pasajeros) || 0), 0
            );
            
            // Animación de contadores
            animateCounter('totalProgramas', total);
            animateCounter('programasBorrador', borrador);
            animateCounter('programasActivos', activos);
            animateCounter('totalViajeros', totalViajeros);
        }
        
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const duration = 1000;
            const increment = targetValue / (duration / 16);
            
            let currentValue = startValue;
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    currentValue = targetValue;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(currentValue);
            }, 16);
        }

        // ============================================================
        // FUNCIONES DE FILTRADO Y BÚSQUEDA
        // ============================================================
        
        function filtrarProgramas() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value;
            
            filteredProgramas = allProgramas.filter(programa => {
                // Filtro de búsqueda
                const matchesSearch = !searchTerm || 
                    programa.destino.toLowerCase().includes(searchTerm) ||
                    programa.nombre_viajero.toLowerCase().includes(searchTerm) ||
                    programa.apellido_viajero.toLowerCase().includes(searchTerm) ||
                    (programa.titulo_programa && programa.titulo_programa.toLowerCase().includes(searchTerm)) ||
                    (programa.id_solicitud && programa.id_solicitud.toLowerCase().includes(searchTerm));
                
                // Filtro de estado
                let programaEstado = 'borrador';
                if (programa.estado) {
                    programaEstado = programa.estado;
                } else if (programa.id_solicitud) {
                    programaEstado = 'activo';
                }
                
                const matchesStatus = !statusFilter || programaEstado === statusFilter;
                
                return matchesSearch && matchesStatus;
            });
            
            mostrarProgramas();
            console.log(`🔍 Filtrado: ${filteredProgramas.length} de ${allProgramas.length} programas`);
        }

        // ============================================================
        // FUNCIONES DE INTERACCIÓN
        // ============================================================
        
        function editarPrograma(id) {
            console.log(`✏️ Editando programa ${id}`);
            window.location.href = `<?= APP_URL ?>/programa?id=${id}`;
        }
        
        function verDetalles(id) {
            console.log(`👁️ Viendo detalles del programa ${id}`);
            // Abrir en nueva ventana para vista previa
            window.open(`<?= APP_URL ?>/programa?id=${id}&preview=1`, '_blank');
        }
        
        function eliminarPrograma(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este programa?')) {
                return;
            }
            
            console.log(`🗑️ Eliminando programa ${id}`);
            
            // Mostrar estado de carga
            showNotification('Eliminando programa...', 'info');
            
            // Implementar eliminación via API
            fetch('<?= APP_URL ?>/programa/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete',
                    id: id
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('✅ Programa eliminado exitosamente', 'success');
                    cargarProgramas(); // Recargar lista
                } else {
                    showNotification('❌ Error al eliminar: ' + result.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error de conexión', 'error');
            });
        }

        // ============================================================
        // ESTADOS DE LA INTERFAZ
        // ============================================================
        
        function showLoadingState() {
            document.getElementById('programsContainer').innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner state-icon"></i>
                    <h3 class="state-title">Cargando programas...</h3>
                    <p class="state-description">Por favor espera mientras obtenemos tus programas</p>
                </div>
            `;
        }
        
        function showEmptyState() {
            const isFiltered = filteredProgramas.length !== allProgramas.length;
            
            document.getElementById('programsContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt state-icon"></i>
                    <h3 class="state-title">${isFiltered ? 'No se encontraron programas' : 'No hay programas'}</h3>
                    <p class="state-description">
                        ${isFiltered ? 
                            'No se encontraron programas que coincidan con los filtros aplicados.' :
                            '¡Comienza creando tu primer programa de viaje personalizado!'
                        }
                    </p>
                    ${isFiltered ? 
                        '<button onclick="limpiarFiltros()" class="action-btn">Limpiar Filtros</button>' :
                        '<a href="<?= APP_URL ?>/programa" class="action-btn"><i class="fas fa-plus"></i> Crear Nuevo Programa</a>'
                    }
                </div>
            `;
        }
        
        function showErrorState(message) {
            document.getElementById('programsContainer').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle state-icon"></i>
                    <h3 class="state-title">Error al cargar</h3>
                    <p class="state-description">${message}</p>
                    <button onclick="cargarProgramas()" class="action-btn">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }

        // ============================================================
        // FUNCIONES AUXILIARES
        // ============================================================
        
        function limpiarFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterStatus').value = '';
            filtrarProgramas();
        }
        
        function showNotification(message, type = 'info') {
            // Eliminar notificaciones existentes
            const existingNotifications = document.querySelectorAll('.custom-notification');
            existingNotifications.forEach(n => n.remove());
            
            // Crear notificación
            const notification = document.createElement('div');
            notification.className = `custom-notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: var(--shadow-large);
                backdrop-filter: blur(10px);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                font-weight: 500;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            `;
            
            // Estilos según el tipo
            if (type === 'success') {
                notification.style.background = 'rgba(34, 197, 94, 0.95)';
                notification.style.color = 'white';
                notification.style.border = '1px solid rgba(34, 197, 94, 1)';
            } else if (type === 'error') {
                notification.style.background = 'rgba(239, 68, 68, 0.95)';
                notification.style.color = 'white';
                notification.style.border = '1px solid rgba(239, 68, 68, 1)';
            } else {
                notification.style.background = 'rgba(59, 130, 246, 0.95)';
                notification.style.color = 'white';
                notification.style.border = '1px solid rgba(59, 130, 246, 1)';
            }
            
            notification.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 18px;
                    cursor: pointer;
                    padding: 0;
                    margin: 0;
                    opacity: 0.8;
                    transition: opacity 0.2s;
                " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">×</button>
            `;
            
            document.body.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        
        function formatDateRange(startDate, endDate) {
            if (!startDate || !endDate) return 'Fechas no definidas';
            
            const start = formatDate(startDate);
            const end = formatDate(endDate);
            
            return `${start} - ${end}`;
        }
        
        function calcularDuracion(fechaLlegada, fechaSalida) {
            if (!fechaLlegada || !fechaSalida) return 'N/A';
            
            const llegada = new Date(fechaLlegada);
            const salida = new Date(fechaSalida);
            const diferencia = salida.getTime() - llegada.getTime();
            const dias = Math.ceil(diferencia / (1000 * 3600 * 24));
            
            if (dias <= 0) return '1 día';
            if (dias === 1) return '1 día';
            if (dias < 7) return `${dias} días`;
            if (dias < 30) {
                const semanas = Math.floor(dias / 7);
                const diasExtra = dias % 7;
                if (diasExtra === 0) {
                    return semanas === 1 ? '1 semana' : `${semanas} semanas`;
                } else {
                    return `${semanas}s ${diasExtra}d`;
                }
            }
            
            const meses = Math.floor(dias / 30);
            return meses === 1 ? '1 mes' : `${meses} meses`;
        }

        // ============================================================
        // FUNCIONES DE EXPORTACIÓN Y UTILIDADES
        // ============================================================
        
        function exportarProgramas() {
            console.log('📤 Exportando programas...');
            
            if (allProgramas.length === 0) {
                showNotification('No hay programas para exportar', 'error');
                return;
            }
            
            // Crear CSV
            const headers = ['ID', 'Título', 'Destino', 'Viajero', 'Fechas', 'Pasajeros', 'Estado'];
            const csvData = allProgramas.map(programa => [
                programa.id_solicitud || programa.id,
                programa.titulo_programa || `Viaje a ${programa.destino}`,
                programa.destino,
                `${programa.nombre_viajero} ${programa.apellido_viajero}`,
                formatDateRange(programa.fecha_llegada, programa.fecha_salida),
                programa.numero_pasajeros,
                programa.estado || 'borrador'
            ]);
            
            const csvContent = [headers, ...csvData]
                .map(row => row.map(cell => `"${cell}"`).join(','))
                .join('\n');
            
            // Descargar archivo
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `programas_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('✅ Programas exportados exitosamente', 'success');
        }

        // ============================================================
        // FUNCIONES DE DEBUG
        // ============================================================
        
        function debugProgramas() {
            console.log('=== 🔍 DEBUG DE PROGRAMAS ===');
            console.log('Total programas:', allProgramas.length);
            console.log('Programas filtrados:', filteredProgramas.length);
            console.log('Datos completos:', allProgramas);
            console.log('=== ESTADÍSTICAS ===');
            
            const stats = {
                total: allProgramas.length,
                conId: allProgramas.filter(p => p.id_solicitud).length,
                sinId: allProgramas.filter(p => !p.id_solicitud).length,
                conTitulo: allProgramas.filter(p => p.titulo_programa).length,
                conImagen: allProgramas.filter(p => p.foto_portada).length
            };
            
            console.table(stats);
            console.log('=== FIN DEBUG ===');
            
            return stats;
        }
        
        function testAPI() {
            console.log('🧪 Probando conectividad con la API...');
            
            fetch('<?= APP_URL ?>/programa/api?action=list')
                .then(response => {
                    console.log('Estado de respuesta:', response.status);
                    console.log('Headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('✅ API funciona correctamente');
                    console.log('Datos recibidos:', data);
                })
                .catch(error => {
                    console.error('❌ Error en API:', error);
                });
        }
        
        // Hacer funciones disponibles globalmente
        window.cargarProgramas = cargarProgramas;
        window.filtrarProgramas = filtrarProgramas;
        window.editarPrograma = editarPrograma;
        window.verDetalles = verDetalles;
        window.eliminarPrograma = eliminarPrograma;
        window.limpiarFiltros = limpiarFiltros;
        window.debugProgramas = debugProgramas;
        window.testAPI = testAPI;
        window.exportarProgramas = exportarProgramas;
        
        console.log('✅ Script de itinerarios cargado completamente');
        console.log('💡 Funciones disponibles:');
        console.log('   - debugProgramas() - Ver estadísticas detalladas');
        console.log('   - testAPI() - Probar conectividad con la API');
        console.log('   - exportarProgramas() - Exportar a CSV');
        console.log('   - cargarProgramas() - Recargar datos manualmente');
        
    </script>
</body>
</html>