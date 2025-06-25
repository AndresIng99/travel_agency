<?php
// =====================================
// ARCHIVO: pages/itinerarios.php - PÁGINA DE ITINERARIOS
// =====================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::requireLogin();
ConfigManager::init();

$user = App::getUser();
$config = ConfigManager::get();
$companyName = ConfigManager::getCompanyName();

// Obtener ID del programa si viene como parámetro
$programa_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= $config['default_language'] ?? 'es' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itinerarios - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- Estilos dinámicos del sistema -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dynamic-styles.php">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Importar fuente consistente */
        * {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Variables CSS dinámicas */
        :root {
            --primary-color: <?= $config['agent_primary_color'] ?? '#667eea' ?>;
            --secondary-color: <?= $config['agent_secondary_color'] ?? '#764ba2' ?>;
            --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        /* Estilos base */
        body {
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Header */
        .itinerarios-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
        }

        .header-user a {
            color: white;
            text-decoration: none;
            opacity: 0.9;
            transition: opacity 0.3s;
        }

        .header-user a:hover {
            opacity: 1;
        }

        /* Contenido principal */
        .itinerarios-main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Navegación */
        .itinerarios-nav {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .nav-link.secondary {
            background: #6c757d;
        }

        /* Lista de itinerarios */
        .itinerarios-list {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Estados */
        .loading-state, .empty-state, .error-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .loading-state i, .empty-state i, .error-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Tarjetas de itinerarios */
        .itinerario-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .itinerario-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .itinerario-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .itinerario-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 0.25rem 0;
        }

        .itinerario-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .itinerario-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-borrador { background: #fed7d7; color: #c53030; }
        .badge-activa { background: #c6f6d5; color: #2f855a; }
        .badge-completada { background: #bee3f8; color: #2b6cb0; }
        .badge-cancelada { background: #e2e8f0; color: #4a5568; }

        .itinerario-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--primary-color);
            width: 16px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .itinerarios-main {
                padding: 0 1rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-links {
                justify-content: center;
            }
            
            .itinerario-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .itinerario-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="itinerarios-header">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-route"></i> Gestión de Itinerarios
            </h1>
            <div class="header-user">
                <span>
                    <i class="fas fa-user"></i>
                    Bienvenid<?= $user['role'] === 'admin' ? 'o' : 'a' ?>, 
                    <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Usuario') ?>
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
                    Crear Nuevo Itinerario
                </a>
                <a href="<?= APP_URL ?>/dashboard" class="nav-link secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Dashboard
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

        <!-- Lista de Itinerarios -->
        <section class="itinerarios-list">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Mis Itinerarios
            </h2>
            
            <div id="itinerariosContainer">
                <div class="loading-state" id="loadingState">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Cargando itinerarios...</h3>
                    <p>Por favor espera mientras obtenemos tus programas</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Variables globales
        let itinerarios = [];
        let loading = false;

        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            cargarItinerarios();
            
            // Si hay un ID de programa específico, redirigir a programa
            const urlParams = new URLSearchParams(window.location.search);
            const programaId = urlParams.get('id');
            if (programaId) {
                window.location.href = `<?= APP_URL ?>/programa?continuar=${programaId}`;
            }
        });

        // Cargar lista de itinerarios
        async function cargarItinerarios() {
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

                const data = await response.json();
                
                if (data.success) {
                    itinerarios = data.data || [];
                    renderizarItinerarios();
                } else {
                    showErrorState(data.error || 'Error cargando itinerarios');
                }
            } catch (error) {
                console.error('Error:', error);
                showErrorState('Error de conexión al cargar itinerarios');
            } finally {
                loading = false;
            }
        }

        // Renderizar lista de itinerarios
        function renderizarItinerarios() {
            const container = document.getElementById('itinerariosContainer');
            
            if (itinerarios.length === 0) {
                showEmptyState();
                return;
            }

            container.innerHTML = itinerarios.map(itinerario => `
                <div class="itinerario-card" onclick="abrirItinerario(${itinerario.id})">
                    <div class="itinerario-header">
                        <div>
                            <h3 class="itinerario-title">
                                ${itinerario.titulo_programa || `${itinerario.nombre_viajero} ${itinerario.apellido_viajero}`}
                            </h3>
                            <p class="itinerario-subtitle">
                                ${itinerario.id_solicitud} - ${itinerario.destino}
                            </p>
                        </div>
                        <span class="itinerario-badge badge-${itinerario.estado}">
                            ${itinerario.estado}
                        </span>
                    </div>
                    
                    <div class="itinerario-info">
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>${itinerario.fecha_llegada_formatted} - ${itinerario.fecha_salida_formatted}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <span>${itinerario.numero_viajeros} viajero${itinerario.numero_viajeros > 1 ? 's' : ''}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>${itinerario.dias_viaje} día${itinerario.dias_viaje > 1 ? 's' : ''}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Creado: ${itinerario.created_at_formatted}</span>
                        </div>
                        ${itinerario.agent_name ? `
                        <div class="info-item">
                            <i class="fas fa-user-tie"></i>
                            <span>Agente: ${itinerario.agent_name}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        // Abrir itinerario específico
        function abrirItinerario(id) {
            window.location.href = `<?= APP_URL ?>/programa?continuar=${id}`;
        }

        // Estados de carga
        function showLoadingState() {
            document.getElementById('itinerariosContainer').innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Cargando itinerarios...</h3>
                    <p>Por favor espera mientras obtenemos tus programas</p>
                </div>
            `;
        }

        function showEmptyState() {
            document.getElementById('itinerariosContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>No hay itinerarios</h3>
                    <p>¡Comienza creando tu primer programa de viaje!</p>
                    <a href="<?= APP_URL ?>/programa" class="nav-link" style="display: inline-flex; margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        Crear Nuevo Itinerario
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
                    <button onclick="cargarItinerarios()" class="nav-link" style="border: none; margin-top: 1rem;">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>