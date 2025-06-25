<?php
// =====================================
// ARCHIVO: pages/itinerarios.php - VERSIÓN MEJORADA CON IMÁGENES Y MÁS INFO
// =====================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::requireLogin();
ConfigManager::init();

$user = App::getUser();
$config = ConfigManager::get();
$userColors = ConfigManager::getColorsForRole($user['role']);
$companyName = ConfigManager::getCompanyName();
?>
<!DOCTYPE html>
<html lang="<?= $config['default_language'] ?? 'es' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Itinerarios - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/global.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: <?= $userColors['primary'] ?>;
            --secondary-color: <?= $userColors['secondary'] ?>;
            --primary-gradient: linear-gradient(135deg, <?= $userColors['primary'] ?>, <?= $userColors['secondary'] ?>);
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-color: #1a202c;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --error-color: #f56565;
            --info-color: #4299e1;
            --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-large: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .itinerarios-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            box-shadow: var(--shadow-large);
            position: relative;
            overflow: hidden;
        }

        .itinerarios-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><path d="M0 20h100V0c-20 0-20 20-40 20S40 0 20 0 0 20 0 20z" fill="white" opacity="0.1"/></svg>') repeat-x bottom;
            background-size: 100px 20px;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .header-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-subtitle {
            opacity: 0.95;
            font-size: 1.1rem;
            font-weight: 300;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .header-user span {
            font-weight: 500;
        }

        .header-user a {
            color: white;
            text-decoration: none;
            opacity: 0.9;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
        }

        .header-user a:hover {
            opacity: 1;
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }

        /* Main Container */
        .itinerarios-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Navigation */
        .itinerarios-nav {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: 600;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .nav-link.secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        /* Lista de itinerarios */
        .itinerarios-list {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: center;
            justify-content: center;
        }

        .section-title i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Grid de programas - MINIMALISTA */
        .programas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
            margin-top: 1.5rem;
        }

        /* Cards de programas - COMPACTAS Y SIMÉTRICAS */
        .programa-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #f1f5f9;
            height: fit-content;
        }

        .programa-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-color: var(--primary-color);
        }

        /* Imagen de portada - COMPACTA */
        .programa-image {
            position: relative;
            height: 140px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            overflow: hidden;
        }

        .programa-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .programa-card:hover .programa-image img {
            transform: scale(1.05);
        }

        .programa-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #64748b;
            font-size: 1.8rem;
        }

        .programa-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,0.4));
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem;
        }

        .programa-badge-container {
            display: flex;
            justify-content: flex-end;
        }

        .programa-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(8px);
        }

        .badge-borrador { background: rgba(251, 191, 36, 0.9); color: #92400e; }
        .badge-activa { background: rgba(16, 185, 129, 0.9); color: white; }
        .badge-completada { background: rgba(59, 130, 246, 0.9); color: white; }
        .badge-cancelada { background: rgba(239, 68, 68, 0.9); color: white; }

        .programa-title-overlay {
            color: white;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .programa-title-overlay h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            line-height: 1.3;
        }

        .programa-title-overlay p {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        /* Contenido de la card - COMPACTO */
        .programa-content {
            padding: 1.2rem;
        }

        .programa-header {
            margin-bottom: 1rem;
            text-align: center;
        }

        .programa-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.3rem;
            line-height: 1.3;
        }

        .programa-subtitle {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .programa-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0.6rem;
            background: #f8fafc;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background: #f1f5f9;
        }

        .info-icon {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .info-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .info-value {
            font-size: 0.8rem;
            color: var(--text-color);
            font-weight: 600;
            line-height: 1.2;
        }

        .info-value.highlight {
            color: var(--primary-color);
        }

        /* Info adicional - MINIMALISTA */
        .programa-extra {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .extra-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .extra-item:last-child {
            margin-bottom: 0;
        }

        .extra-item i {
            width: 12px;
            color: var(--primary-color);
            font-size: 0.7rem;
        }

        /* Estados */
        .loading-state, .empty-state, .error-state {
            text-align: center;
            padding: 4rem;
            color: #6c757d;
        }

        .loading-state i, .empty-state i, .error-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .empty-state i {
            color: #9ca3af;
        }

        .error-state i {
            color: var(--error-color);
        }

        .loading-state i {
            animation: spin 1s linear infinite;
        }

        .empty-state h3, .error-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .empty-state p, .error-state p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Stats rapidas */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.8);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .itinerarios-main {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-title h1 {
                font-size: 2rem;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
            }

            .programas-grid {
                grid-template-columns: 1fr;
            }

            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .programa-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .programa-card {
                margin: 0 -0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="itinerarios-header">
        <div class="header-content">
            <div class="header-title">
                <h1><i class="fas fa-route"></i> Mis Programas de Viaje</h1>
                <p class="header-subtitle">Gestiona y visualiza todos tus itinerarios de manera elegante</p>
            </div>
            <div class="header-user">
                <span>
                    ¡Hola, <?= $user['role'] === 'admin' ? 'Administrador' : ($user['role'] === 'agent' ? 'Agente' : 'Usuario') ?> 
                    <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Usuario') ?>!
                </span>
                <a href="<?= APP_URL ?>/auth/logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="itinerarios-main">
        <!-- Navegación -->
        <nav class="itinerarios-nav">
            <div class="nav-links">
                <a href="<?= APP_URL ?>/programa" class="nav-link">
                    <i class="fas fa-plus"></i>
                    Crear Nuevo Programa
                </a>
                <a href="<?= APP_URL ?>/dashboard" class="nav-link secondary">
                    <i class="fas fa-arrow-left"></i>
                    Dashboard
                </a>
                <a href="<?= APP_URL ?>/biblioteca" class="nav-link secondary">
                    <i class="fas fa-book"></i>
                    Biblioteca
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= APP_URL ?>/administrador" class="nav-link secondary">
                    <i class="fas fa-cogs"></i>
                    Administración
                </a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Estadísticas rápidas -->
        <div class="quick-stats" id="quickStats" style="display: none;">
            <div class="stat-card">
                <div class="stat-number" id="totalProgramas">0</div>
                <div class="stat-label">Total Programas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="programasActivos">0</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="programasCompletados">0</div>
                <div class="stat-label">Completados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalViajeros">0</div>
                <div class="stat-label">Total Viajeros</div>
            </div>
        </div>

        <!-- Lista de Itinerarios -->
        <section class="itinerarios-list">
            <h2 class="section-title">
                <i class="fas fa-map-marked-alt"></i>
                Mis Programas
            </h2>
            
            <div id="itinerariosContainer">
                <div class="loading-state" id="loadingState">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Cargando programas...</h3>
                    <p>Por favor espera mientras obtenemos tus programas</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Variables globales
        let programas = [];
        let loading = false;

        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            cargarProgramas();
            
            // Si hay un ID de programa específico, redirigir a programa
            const urlParams = new URLSearchParams(window.location.search);
            const programaId = urlParams.get('id');
            if (programaId) {
                window.location.href = `<?= APP_URL ?>/programa?id=${programaId}`;
            }
        });

        // Cargar lista de programas
        async function cargarProgramas() {
            if (loading) return;
            loading = true;

            try {
                showLoadingState();
                
                const response = await fetch('<?= APP_URL ?>/programa/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=list'
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const data = await response.json();
                console.log('Respuesta API:', data);
                
                if (data.success) {
                    programas = data.data || [];
                    renderizarProgramas();
                    mostrarEstadisticas();
                } else {
                    showErrorState(data.error || 'Error cargando programas');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showErrorState('Error de conexión al cargar programas: ' + error.message);
            } finally {
                loading = false;
            }
        }

        // Mostrar estadísticas rápidas
        function mostrarEstadisticas() {
            const total = programas.length;
            const activos = programas.filter(p => p.estado === 'activa').length;
            const completados = programas.filter(p => p.estado === 'completada').length;
            const totalViajeros = programas.reduce((sum, p) => sum + parseInt(p.numero_pasajeros || p.numero_viajeros || 1), 0);

            document.getElementById('totalProgramas').textContent = total;
            document.getElementById('programasActivos').textContent = activos;
            document.getElementById('programasCompletados').textContent = completados;
            document.getElementById('totalViajeros').textContent = totalViajeros;
            
            if (total > 0) {
                document.getElementById('quickStats').style.display = 'grid';
            }
        }

        // Renderizar programas con diseño mejorado
        function renderizarProgramas() {
            const container = document.getElementById('itinerariosContainer');
            
            if (programas.length === 0) {
                showEmptyState();
                return;
            }

            container.innerHTML = `
                <div class="programas-grid">
                    ${programas.map(programa => `
                        <div class="programa-card" onclick="abrirPrograma(${programa.id})">
                            <!-- Imagen de portada -->
                            <div class="programa-image">
                                ${programa.foto_portada ? `
                                    <img src="${programa.foto_portada}" alt="${programa.titulo_programa || 'Programa de viaje'}" 
                                         onerror="this.parentElement.innerHTML='<div class=\\"programa-image-placeholder\\"><i class=\\"fas fa-map-marked-alt\\"></i></div>'">
                                ` : `
                                    <div class="programa-image-placeholder">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                `}
                                
                                <div class="programa-overlay">
                                    <div class="programa-badge-container">
                                        <span class="programa-badge badge-${programa.estado || 'borrador'}">
                                            ${getEstadoTexto(programa.estado || 'borrador')}
                                        </span>
                                    </div>
                                    
                                    <div class="programa-title-overlay">
                                        <h3>${programa.titulo_programa || `${programa.nombre_viajero} ${programa.apellido_viajero}`}</h3>
                                        <p><i class="fas fa-map-marker-alt"></i> ${programa.destino}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="programa-content">
                                <div class="programa-header">
                                    <h3 class="programa-title">
                                        ${programa.titulo_programa || `${programa.nombre_viajero} ${programa.apellido_viajero}`}
                                    </h3>
                                    <p class="programa-subtitle">
                                        ${programa.id_solicitud}
                                    </p>
                                </div>
                                
                                <div class="programa-info">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="info-label">Fechas</div>
                                        <div class="info-value highlight">
                                            ${programa.fecha_llegada_formatted?.split(' ')[0] || 'N/A'} - ${programa.fecha_salida_formatted?.split(' ')[0] || 'N/A'}
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="info-label">Viajeros</div>
                                        <div class="info-value">
                                            ${programa.numero_pasajeros || programa.numero_viajeros || 1}
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="info-label">Días</div>
                                        <div class="info-value">
                                            ${calcularDias(programa.fecha_llegada, programa.fecha_salida)}
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-label">Destino</div>
                                        <div class="info-value">
                                            ${programa.destino.length > 15 ? programa.destino.substring(0, 15) + '...' : programa.destino}
                                        </div>
                                    </div>
                                </div>
                                
                                ${programa.acompanamiento || programa.agente_nombre || programa.created_at_formatted ? `
                                <div class="programa-extra">
                                    ${programa.acompanamiento ? `
                                    <div class="extra-item">
                                        <i class="fas fa-info-circle"></i>
                                        <span>${programa.acompanamiento}</span>
                                    </div>
                                    ` : ''}
                                    
                                    ${programa.agente_nombre ? `
                                    <div class="extra-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Agente: ${programa.agente_nombre}</span>
                                    </div>
                                    ` : ''}
                                    
                                    <div class="extra-item">
                                        <i class="fas fa-calendar-plus"></i>
                                        <span>Creado: ${programa.created_at_formatted}</span>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Función para obtener texto del estado
        function getEstadoTexto(estado) {
            const estados = {
                'borrador': 'Borrador',
                'activa': 'Activo',
                'completada': 'Completado',
                'cancelada': 'Cancelado'
            };
            return estados[estado] || 'Borrador';
        }

        // Función auxiliar para calcular días
        function calcularDias(fechaLlegada, fechaSalida) {
            if (!fechaLlegada || !fechaSalida) return 0;
            
            const llegada = new Date(fechaLlegada);
            const salida = new Date(fechaSalida);
            const diffTime = Math.abs(salida - llegada);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return diffDays || 1;
        }

        // Abrir programa específico
        function abrirPrograma(id) {
            window.location.href = `<?= APP_URL ?>/programa?id=${id}`;
        }

        // Estados de carga
        function showLoadingState() {
            document.getElementById('itinerariosContainer').innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Cargando programas...</h3>
                    <p>Por favor espera mientras obtenemos tus programas</p>
                </div>
            `;
        }

        function showEmptyState() {
            document.getElementById('itinerariosContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>No hay programas creados</h3>
                    <p>¡Comienza creando tu primer programa de viaje personalizado!</p>
                    <a href="<?= APP_URL ?>/programa" class="nav-link" style="display: inline-flex; margin-top: 2rem;">
                        <i class="fas fa-plus"></i>
                        Crear Nuevo Programa
                    </a>
                </div>
            `;
        }

        function showErrorState(message) {
            document.getElementById('itinerariosContainer').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error al cargar</h3>
                    <p>${message}</p>
                    <button onclick="cargarProgramas()" class="nav-link" style="border: none; margin-top: 2rem;">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }

        // Funciones auxiliares para mejorar la experiencia
        function formatearFecha(fecha) {
            if (!fecha) return 'No definida';
            
            const opciones = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                weekday: 'long'
            };
            
            return new Date(fecha).toLocaleDateString('es-ES', opciones);
        }

        function obtenerIconoDestino(destino) {
            if (!destino) return 'fas fa-map-marker-alt';
            
            const destino_lower = destino.toLowerCase();
            
            if (destino_lower.includes('playa') || destino_lower.includes('caribe') || destino_lower.includes('cancún') || destino_lower.includes('riviera')) {
                return 'fas fa-umbrella-beach';
            } else if (destino_lower.includes('montaña') || destino_lower.includes('andes') || destino_lower.includes('cordillera')) {
                return 'fas fa-mountain';
            } else if (destino_lower.includes('europa') || destino_lower.includes('parís') || destino_lower.includes('roma') || destino_lower.includes('madrid')) {
                return 'fas fa-landmark';
            } else if (destino_lower.includes('aventura') || destino_lower.includes('safari') || destino_lower.includes('selva')) {
                return 'fas fa-hiking';
            } else if (destino_lower.includes('crucero') || destino_lower.includes('barco')) {
                return 'fas fa-ship';
            } else {
                return 'fas fa-map-marker-alt';
            }
        }

        // Funciones para mejorar la interactividad
        function mostrarDetallesPrograma(id) {
            // Función para mostrar un modal con más detalles (opcional)
            console.log('Mostrando detalles del programa:', id);
        }

        function filtrarProgramas(estado) {
            // Función para filtrar programas por estado (opcional)
            console.log('Filtrando por estado:', estado);
        }

        // Animaciones y efectos adicionales
        function animarCarga() {
            const cards = document.querySelectorAll('.programa-card');
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        // Mejorar la experiencia después de cargar
        function mejorarExperiencia() {
            // Animar las cards después de cargar
            setTimeout(animarCarga, 100);
            
            // Lazy loading para imágenes
            const imagenes = document.querySelectorAll('.programa-image img');
            imagenes.forEach(img => {
                img.loading = 'lazy';
            });
        }

        // Llamar después de renderizar
        function renderizarProgramasCompleto() {
            renderizarProgramas();
            setTimeout(mejorarExperiencia, 200);
        }

        // Actualizar la función principal
        async function cargarProgramasCompleto() {
            await cargarProgramas();
            renderizarProgramasCompleto();
        }
    </script>
</body>
</html>