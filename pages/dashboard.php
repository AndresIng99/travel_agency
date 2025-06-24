<?php
// =====================================
// ARCHIVO: pages/dashboard.php - VERSIÓN CORREGIDA CON ROLES DIFERENCIADOS
// =====================================
?>
<?php 
$user = App::getUser(); 
// Inicializar conexión a base de datos para las estadísticas
try {
    $db = Database::getInstance();
} catch(Exception $e) {
    $db = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Header diferenciado por rol */
        .header {
            background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #e53e3e 0%, #fd746c 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Google Translate */
        #google_translate_element {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
        }

        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0px !important; }

        /* Sidebar diferenciado */
        .sidebar {
            position: fixed;
            left: -280px;
            top: 70px;
            width: 280px;
            height: calc(100vh - 70px);
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%)' : 'linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%)' ?>;
        }

        .company-logo {
            width: 40px;
            height: 40px;
            background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #e53e3e 0%, #fd746c 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background-color: #f7fafc;
            color: <?= $user['role'] === 'admin' ? '#e53e3e' : '#667eea' ?>;
            border-left-color: <?= $user['role'] === 'admin' ? '#e53e3e' : '#667eea' ?>;
        }

        .menu-icon {
            font-size: 18px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            margin-top: 70px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-open {
            margin-left: 280px;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid <?= $user['role'] === 'admin' ? '#e53e3e' : '#667eea' ?>;
        }

        .welcome-title {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: #718096;
            font-size: 16px;
        }

        /* Role Badge */
        .role-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            <?php if ($user['role'] === 'admin'): ?>
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #e53e3e;
            <?php else: ?>
            background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
            color: #2f855a;
            <?php endif; ?>
        }

        /* Quick Actions - Diferenciadas por rol */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #e53e3e 0%, #fd746c 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        /* Iconos diferenciados por rol */
        .admin-primary { background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%); color: white; }
        .admin-secondary { background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%); color: #e53e3e; }
        .agent-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .agent-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }

        .action-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .action-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Stats Section */
        .stats-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stats-title {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: background-color 0.3s ease;
        }

        .stat-item:hover {
            background-color: #f7fafc;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: <?= $user['role'] === 'admin' ? '#e53e3e' : '#667eea' ?>;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .main-content {
                padding: 20px;
            }

            .main-content.sidebar-open {
                margin-left: 0;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <h2><?= APP_NAME ?></h2>
        </div>
        
        <div class="header-center">
            <div id="google_translate_element"></div>
        </div>

        <div class="header-right">
            <div class="user-info" onclick="toggleUserMenu()">
                <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
                <div>
                    <div style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($user['name']) ?></div>
                    <div style="font-size: 12px; opacity: 0.8;"><?= $user['role'] === 'admin' ? 'Administrador' : 'Agente de Viajes' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="company-logo">TA</div>
            <h3><?= APP_NAME ?></h3>
            <p style="color: #718096; font-size: 14px;">
                <?= $user['role'] === 'admin' ? 'Panel de Administración' : 'Sistema de Gestión' ?>
            </p>
        </div>

        <nav class="sidebar-menu">
            <a href="<?= APP_URL ?>/dashboard" class="menu-item active">
                <div class="menu-icon">🏠</div>
                Dashboard
            </a>
            
            <?php if ($user['role'] === 'admin'): ?>
            <!-- Menú del Administrador -->
            <a href="<?= APP_URL ?>/administrador" class="menu-item">
                <div class="menu-icon">👥</div>
                Gestión de Usuarios
            </a>
            <a href="<?= APP_URL ?>/administrador/configuracion" class="menu-item">
                <div class="menu-icon">⚙️</div>
                Configuración Sistema
            </a>
            <a href="<?= APP_URL ?>/biblioteca" class="menu-item">
                <div class="menu-icon">📚</div>
                Supervisar Biblioteca
            </a>
            <a href="<?= APP_URL ?>/programa" class="menu-item">
                <div class="menu-icon">✈️</div>
                Supervisar Programas
            </a>
            <a href="<?= APP_URL ?>/reportes" class="menu-item">
                <div class="menu-icon">📊</div>
                Reportes del Sistema
            </a>
            <?php else: ?>
            <!-- Menú del Agente -->
            <a href="<?= APP_URL ?>/programa" class="menu-item">
                <div class="menu-icon">✈️</div>
                Mi Programa
            </a>
            <a href="<?= APP_URL ?>/biblioteca" class="menu-item">
                <div class="menu-icon">📚</div>
                Biblioteca
            </a>
            <a href="<?= APP_URL ?>/reportes" class="menu-item">
                <div class="menu-icon">📊</div>
                Mis Reportes
            </a>
            <a href="<?= APP_URL ?>/perfil" class="menu-item">
                <div class="menu-icon">👤</div>
                Mi Perfil
            </a>
            <?php endif; ?>
            
            <a href="<?= APP_URL ?>/auth/logout" class="menu-item">
                <div class="menu-icon">🚪</div>
                Cerrar Sesión
            </a>
        </nav>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="role-badge">
                <?= $user['role'] === 'admin' ? '👑 Administrador del Sistema' : '✈️ Agente de Viajes' ?>
            </div>
            <h1 class="welcome-title">¡Bienvenido<?= $user['role'] === 'admin' ? '' : 'a' ?>, <?= htmlspecialchars($user['name']) ?>!</h1>
            <p class="welcome-subtitle">
                <?php if ($user['role'] === 'admin'): ?>
                    Administra el sistema, gestiona usuarios, supervisa operaciones y configura la plataforma desde este panel de control.
                <?php else: ?>
                    Gestiona tus programas de viaje, crea solicitudes de viajeros y administra recursos desde este panel de control.
                <?php endif; ?>
            </p>
        </div>

        <!-- Quick Actions diferenciadas por rol -->
        <div class="quick-actions">
            <?php if ($user['role'] === 'admin'): ?>
            <!-- Acciones para Administrador -->
            <div class="action-card" onclick="goTo('/administrador')">
                <div class="action-icon admin-primary">👥</div>
                <h3 class="action-title">Gestión de Usuarios</h3>
                <p class="action-description">Administra usuarios del sistema, crea nuevos agentes, gestiona permisos y supervisa la actividad de todos los usuarios.</p>
            </div>

            <div class="action-card" onclick="goTo('/administrador/configuracion')">
                <div class="action-icon admin-secondary">⚙️</div>
                <h3 class="action-title">Configuración del Sistema</h3>
                <p class="action-description">Configura colores, logos, integraciones, políticas de seguridad y parámetros generales del sistema.</p>
            </div>

            <div class="action-card" onclick="goTo('/biblioteca')">
                <div class="action-icon admin-primary">📚</div>
                <h3 class="action-title">Supervisar Biblioteca</h3>
                <p class="action-description">Supervisa y administra todos los recursos de la biblioteca: días, alojamientos, actividades y transportes de todos los agentes.</p>
            </div>

            <div class="action-card" onclick="goTo('/programa')">
                <div class="action-icon admin-secondary">✈️</div>
                <h3 class="action-title">Supervisar Programas</h3>
                <p class="action-description">Revisa y supervisa todos los programas de viaje y solicitudes creadas por los agentes del sistema.</p>
            </div>

            <?php else: ?>
            <!-- Acciones para Agente -->
            <div class="action-card" onclick="goTo('/programa')">
                <div class="action-icon agent-primary">✈️</div>
                <h3 class="action-title">Mi Programa de Viajes</h3>
                <p class="action-description">Crea nuevas solicitudes de viajero y gestiona programas personalizados con destinos, fechas y acompañamiento específico.</p>
            </div>

            <div class="action-card" onclick="goTo('/biblioteca')">
                <div class="action-icon agent-secondary">📚</div>
                <h3 class="action-title">Mi Biblioteca de Recursos</h3>
                <p class="action-description">Administra tus días, alojamientos, actividades y transportes. Crea y edita recursos para usar en tus programas de viaje.</p>
            </div>

            <?php endif; ?>
        </div>

        <!-- Stats Section diferenciada por rol -->
        <div class="stats-section">
            <h2 class="stats-title">
                <?= $user['role'] === 'admin' ? 'Estadísticas del Sistema' : 'Resumen de Mi Actividad' ?>
            </h2>
            <div class="stats-grid">
                <?php if ($user['role'] === 'admin'): ?>
                <!-- Stats para Administrador -->
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $db = Database::getInstance();
                            $count = $db->fetch("SELECT COUNT(*) as total FROM users WHERE active = 1");
                            echo $count['total'];
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM programa_solicitudes");
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Programas Totales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $dias = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_dias WHERE activo = 1")['total'] ?? 0;
                            $alojamientos = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_alojamientos WHERE activo = 1")['total'] ?? 0;
                            $actividades = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_actividades WHERE activo = 1")['total'] ?? 0;
                            $transportes = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_transportes WHERE activo = 1")['total'] ?? 0;
                            echo $dias + $alojamientos + $actividades + $transportes;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Recursos Biblioteca</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Sesiones Activas</div>
                </div>
                <?php else: ?>
                <!-- Stats para Agente -->
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM programa_solicitudes WHERE user_id = ?", [$user['id']]);
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Mis Programas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_dias WHERE user_id = ? AND activo = 1", [$user['id']]);
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Días Creados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_alojamientos WHERE user_id = ? AND activo = 1", [$user['id']]);
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Alojamientos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        try {
                            $count = $db->fetch("SELECT COUNT(*) as total FROM biblioteca_actividades WHERE user_id = ? AND activo = 1", [$user['id']]);
                            echo $count['total'] ?? 0;
                        } catch(Exception $e) {
                            echo "0";
                        }
                    ?></div>
                    <div class="stat-label">Actividades</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let sidebarOpen = false;

        // Sidebar functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');
            
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.add('open');
                overlay.classList.add('show');
                if (window.innerWidth > 768) {
                    mainContent.classList.add('sidebar-open');
                }
            } else {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                mainContent.classList.remove('sidebar-open');
            }
        }

        function closeSidebar() {
            if (sidebarOpen) {
                toggleSidebar();
            }
        }

        // Navigation
        function goTo(path) {
            window.location.href = '<?= APP_URL ?>' + path;
        }

        function toggleUserMenu() {
            if (confirm('¿Desea cerrar sesión?')) {
                window.location.href = '<?= APP_URL ?>/auth/logout';
            }
        }

        // Google Translate
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es',
                includedLanguages: 'en,fr,pt,it,de,es',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');

            setTimeout(loadSavedLanguage, 1000);
        }

        function saveLanguage(lang) {
            sessionStorage.setItem('language', lang);
            localStorage.setItem('preferredLanguage', lang);
        }

        function loadSavedLanguage() {
            const saved = sessionStorage.getItem('language') || localStorage.getItem('preferredLanguage');
            if (saved && saved !== 'es') {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.value = saved;
                    select.dispatchEvent(new Event('change'));
                }
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Detectar cambios de idioma
            setTimeout(function() {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.addEventListener('change', function() {
                        if (this.value) saveLanguage(this.value);
                    });
                }
            }, 2000);

            // Responsive behavior
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768 && sidebarOpen) {
                    document.getElementById('mainContent').classList.remove('sidebar-open');
                } else if (window.innerWidth > 768 && sidebarOpen) {
                    document.getElementById('mainContent').classList.add('sidebar-open');
                }
            });
        });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>