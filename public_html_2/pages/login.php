<?php
// =====================================
// ARCHIVO: pages/login.php - Login Moderno con Configuración Personalizada
// =====================================

// Incluir ConfigManager para acceso completo a configuración
require_once 'config/config_functions.php';

// Obtener configuración de colores y empresa
$loginColors = App::getLoginColors();
$companyName = App::getCompanyName();
$logo = App::getLogo();
$defaultLanguage = App::getDefaultLanguage();

// Obtener imagen de fondo desde la base de datos
ConfigManager::init();
$backgroundImage = ConfigManager::get('background_image');
?>
<!DOCTYPE html>
<html lang="<?= $defaultLanguage ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- Google Translate -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: '<?= $defaultLanguage ?>',
                includedLanguages: 'en,fr,pt,it,de,es',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        
        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
        }
        
        /* Lado izquierdo - Hero Section con colores dinámicos */
        .left-side {
            flex: 1;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?> 0%, <?= $loginColors['secondary'] ?> 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px;
            color: white;
            overflow: hidden;
        }
        
        /* Imagen de fondo desde base de datos */
        <?php if ($backgroundImage): ?>
        .left-side {
            background-image: url('<?= htmlspecialchars($backgroundImage) ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .left-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?>CC 0%, <?= $loginColors['secondary'] ?>CC 100%);
            z-index: 1;
        }
        <?php else: ?>
        .left-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.15);
            z-index: 1;
        }
        <?php endif; ?>
        
        /* Patrón decorativo si no hay imagen de fondo */
        <?php if (!$backgroundImage): ?>
        .left-side::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: 0;
        }
        <?php endif; ?>
        
        .left-content {
            position: relative;
            z-index: 2;
            max-width: 500px;
        }
        
        .hello-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            line-height: 1.6;
            animation: slideInLeft 1s ease-out 0.2s both;
            margin-top: 40px;
            <?php if ($backgroundImage): ?>
            text-shadow: 0 2px 6px rgba(0,0,0,0.6);
            background: rgba(0,0,0,0.25);
            padding: 25px 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            <?php endif; ?>
        }
        
        .company-logo {
            margin: 25px 0 0 0;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1s ease-out 0.5s both;
        }
        
        .company-logo img {
            max-width: 250px;
            max-height: 125px;
            object-fit: contain;
            filter: none;
        }
        
        .company-logo-text {
            color: #2d3748;
            font-size: 42px;
            font-weight: 600;
            text-shadow: none;
        }
        
        /* Lado derecho - Formulario */
        .right-side {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }
        
        /* Google Translate en la esquina */
        /* ===== MEJORAR EL SELECTOR DE GOOGLE TRANSLATE ===== */

        /* Contenedor principal */
        .translate-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Caja del widget */
        #google_translate_element {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            padding: 8px 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #google_translate_element:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        /* Ocultar el icono de Google */
        .goog-te-gadget-icon {
            display: none !important;
        }

        /* Contenedor del gadget */
        .goog-te-gadget-simple {
            background: transparent !important;
            border: none !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }

        /* El enlace principal */
        .VIpgJd-ZVi9od-xl07Ob-lTBxed {
            background: transparent !important;
            border: none !important;
            color: #2d3748 !important;
            text-decoration: none !important;
            font-family: inherit !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            padding: 4px 8px !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .VIpgJd-ZVi9od-xl07Ob-lTBxed:hover {
            background: rgba(102, 126, 234, 0.1) !important;
            color: #667eea !important;
        }

        /* El texto "Seleccionar idioma" */
        .VIpgJd-ZVi9od-xl07Ob-lTBxed span:first-child {
            color: inherit !important;
            font-weight: inherit !important;
        }

        /* Ocultar las imágenes separadoras */
        .VIpgJd-ZVi9od-xl07Ob-lTBxed img {
            display: none !important;
        }

        /* Ocultar el separador */
        .VIpgJd-ZVi9od-xl07Ob-lTBxed span[style*="border-left"] {
            display: none !important;
        }

        /* Mejorar la flecha */
        .VIpgJd-ZVi9od-xl07Ob-lTBxed span[aria-hidden="true"] {
            color: #6b7280 !important;
            font-size: 12px !important;
            margin-left: 4px !important;
            transition: all 0.2s ease !important;
        }

        .VIpgJd-ZVi9od-xl07Ob-lTBxed:hover span[aria-hidden="true"] {
            color: #667eea !important;
            transform: translateY(1px) !important;
        }
        .VIpgJd-ZVi9od-ORHb-OEVmcd {
            left: 0;
            display: none !important;
            top: 0;
        }

        /* Menú desplegable cuando aparece */
        .goog-te-menu-frame {
            border: none !important;
            border-radius: 10px !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
            backdrop-filter: blur(10px) !important;
            overflow: hidden !important;
            margin-top: 4px !important;
        }

        .goog-te-menu2 {
            background: rgba(255, 255, 255, 0.98) !important;
            border: none !important;
            padding: 8px 0 !important;
        }

        /* Items de la lista */
        .goog-te-menu2-item {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            padding: 10px 16px !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            border: none !important;
            margin: 0 6px !important;
            border-radius: 6px !important;
        }

        .goog-te-menu2-item:hover {
            background: rgba(102, 126, 234, 0.1) !important;
            color: #667eea !important;
            transform: translateX(2px) !important;
        }

        .goog-te-menu2-item:active {
            transform: translateX(2px) scale(0.98) !important;
        }

        .goog-te-menu2-item-selected {
            background: #667eea !important;
            color: white !important;
            font-weight: 600 !important;
        }

        /* Ocultar banner azul */
        .goog-te-banner-frame.skiptranslate { 
            display: none !important; 
        }

        body { 
            top: 0px !important; 
        }

        /* Responsive */
        @media (max-width: 768px) {
            .translate-container {
                top: 10px;
                right: 10px;
            }
            
            #google_translate_element {
                padding: 6px 10px;
            }
            
            .VIpgJd-ZVi9od-xl07Ob-lTBxed {
                font-size: 12px !important;
                padding: 3px 6px !important;
            }
            
            .goog-te-menu2-item {
                font-size: 12px !important;
                padding: 8px 14px !important;
            }
        }
        
        .goog-te-gadget img {
            vertical-align: middle;
            border: none;
            display: none;
        }



        .form-container {
            width: 100%;
            max-width: 400px;
            animation: slideInRight 1s ease-out;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-title {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-subtitle {
            color: #718096;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: <?= $loginColors['primary'] ?>;
            background: white;
            box-shadow: 0 0 0 3px <?= $loginColors['primary'] ?>20;
        }
        
        .form-group input::placeholder {
            color: #a0aec0;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?> 0%, <?= $loginColors['secondary'] ?> 100%);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px <?= $loginColors['primary'] ?>40;
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
            display: none;
        }
        
        .social-buttons {
            display: none;
        }
        
        .social-btn {
            display: none;
        }
        
        .social-btn.facebook {
            display: none;
        }
        
        .social-btn.twitter {
            display: none;
        }
        
        .social-btn.google {
            display: none;
        }
        
        .error-message {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #e53e3e;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .success-message {
            background: #c6f6d5;
            border: 1px solid #9ae6b4;
            color: #2f855a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .demo-accounts {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid <?= $loginColors['primary'] ?>;
        }
        
        .demo-accounts h4 {
            color: #4a5568;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .demo-account {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            padding: 8px 12px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        
        .demo-account .username {
            color: <?= $loginColors['primary'] ?>;
            font-weight: bold;
        }
        
        .demo-account .password {
            color: #718096;
        }
        
        /* Loading spinner */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Animaciones */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Session expired message */
        .session-expired {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #e53e3e;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideInRight 0.5s ease-out;
        }
        
        /* Floating elements con colores dinámicos */
        .floating-elements {
            position: absolute;
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
            animation: float 15s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 70%;
            animation-delay: 5s;
        }
        
        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 40%;
            animation-delay: 10s;
        }

        @keyframes popupShow {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .left-side {
                flex: 0 0 40%;
                padding: 40px 30px;
                text-align: center;
                align-items: center;
            }
            
            .hello-title {
                font-size: 2.5rem;
            }
            
            .right-side {
                flex: 1;
                padding: 30px 20px;
            }
            
            .translate-container {
                top: 10px;
                right: 10px;
            }
            
            .social-buttons {
                flex-direction: column;
            }
            
            .social-btn {
                justify-content: center;
            }
            
            .company-logo {
                position: relative;
                top: auto;
                left: auto;
                margin: 20px 0;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .left-side {
                flex: 0 0 30%;
                padding: 20px;
            }
            
            .hello-title {
                font-size: 2rem;
            }
            
            .form-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Lado Izquierdo - Hero Section -->
        <div class="left-side">
            <div class="floating-elements">
                <div class="floating-element"></div>
                <div class="floating-element"></div>
                <div class="floating-element"></div>
            </div>
            
            <div class="left-content">
                <p class="hello-subtitle">
                    Bienvenido a <?= htmlspecialchars($companyName) ?>. 
                    Sistema integral de gestión de viajes corporativos que optimiza 
                    tus procesos y mejora la experiencia de tus colaboradores.
                </p>
            </div>
        </div>
        
        <!-- Lado Derecho - Formulario -->
        <div class="right-side">
            <!-- Google Translate -->
            <div class="translate-container">
                <div id="google_translate_element"></div>
            </div>
            
            <div class="form-container">
                <!-- Header del formulario -->
                <div class="form-header">
                    <h2 class="form-title">Acceso</h2>
                    <p class="form-subtitle">
                        Ingresa tus credenciales para acceder al sistema
                    </p>
                    
                    <!-- Logo de la empresa -->
                    <div class="company-logo">
                        <?php if ($logo): ?>
                            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($companyName) ?>">
                        <?php else: ?>
                            <div class="company-logo-text"><?= htmlspecialchars($companyName) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Session expired message -->
                <?php if (isset($_SESSION['session_expired'])): ?>
                    <div class="session-expired">
                        ⏰ Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.
                    </div>
                    <?php unset($_SESSION['session_expired']); ?>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="<?= APP_URL ?>/auth/login" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">USUARIO</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Ingrese su usuario" autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">CONTRASEÑA</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="• • • • • •" autocomplete="current-password">
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        INICIAR SESIÓN
                        <span class="loading" id="loading"></span>
                    </button>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error-message">
                            🚫 <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success-message">
                            ✅ <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                </form>

                <!-- Demo Accounts -->
                <div class="demo-accounts">
                    <h4>👥 Cuentas de Demostración:</h4>
                    <div class="demo-account">
                        <span class="username">admin</span>
                        <span class="password">password</span>
                    </div>
                    <div class="demo-account">
                        <span class="username">agente1</span>
                        <span class="password">123456Aa*</span>
                    </div>
                </div>
                
                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #718096; font-size: 13px; text-align: center;">
                    <p>© <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Configuración de idioma por defecto
        const DEFAULT_LANGUAGE = '<?= $defaultLanguage ?>';
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            initializeGoogleTranslate();
            
            // Auto-focus en el primer campo
            document.getElementById('username').focus();
        });

        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            initializeGoogleTranslate();
            checkMaintenanceMode(); // ← NUEVO
            
            // Auto-focus en el primer campo
            document.getElementById('username').focus();
        });

        // ✅ FUNCIÓN PARA VERIFICAR MODO MANTENIMIENTO
        async function checkMaintenanceMode() {
            try {
                const response = await fetch('<?= APP_URL ?>/check_maintenance.php');
                const data = await response.json();
                
                if (data.maintenance_mode) {
                    window.maintenanceModeActive = true;
                }
            } catch(e) {
                // No hacer nada si hay error
            }
        }

        // Inicializar formulario
        function initializeForm() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');

            form.addEventListener('submit', function(e) {
                // ✅ VERIFICAR MANTENIMIENTO ANTES DE ENVIAR
                if (window.maintenanceModeActive) {
                    const username = document.getElementById('username').value.trim();
                    
                    // Si no es admin, mostrar popup y bloquear
                    if (username !== 'admin') {
                        e.preventDefault();
                        showMaintenancePopup();
                        return;
                    }
                }
                
                // Mostrar loading
                loginBtn.disabled = true;
                loading.style.display = 'inline-block';
                loginBtn.style.opacity = '0.8';
            });

            // Validación en tiempo real
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateField(this);
                });

                input.addEventListener('blur', function() {
                    validateField(this);
                });
            });

            // Enter key navigation
            document.getElementById('username').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });

            document.getElementById('password').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }

        // Validar campo
        function validateField(field) {
            if (field.value.trim() === '') {
                field.style.borderColor = '#e53e3e';
                field.style.boxShadow = '0 0 0 3px #e53e3e20';
            } else {
                field.style.borderColor = '#9ae6b4';
                field.style.boxShadow = '0 0 0 3px #9ae6b420';
            }
        }

       // ✅ POPUP SÚPER SIMPLE
        function showMaintenancePopup() {
            document.body.innerHTML += `
                <div id="popup" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;">
                    <div style="background:white;padding:40px;border-radius:20px;text-align:center;max-width:500px;width:90%;">
                        <div style="font-size:4rem;margin-bottom:20px;">🚧</div>
                        <h2 style="color:#e53e3e;margin-bottom:20px;">Sitio en Mantenimiento</h2>
                        <p style="color:#666;margin-bottom:30px;">La aplicación está temporalmente en modo mantenimiento.<br>Solo los administradores pueden acceder.</p>
                        <button onclick="document.getElementById('popup').remove(); document.getElementById('loginForm').reset(); document.getElementById('username').focus();" style="background:#e53e3e;color:white;border:none;padding:15px 30px;border-radius:10px;cursor:pointer;font-size:1rem;">
                            Entendido
                        </button>
                    </div>
                </div>
            `;
        }


        // Google Translate
        function initializeGoogleTranslate() {
            // Aplicar idioma por defecto
            setTimeout(() => {
                applyDefaultLanguage();
            }, 1000);

            // Configurar eventos de cambio de idioma
            setTimeout(function() {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.addEventListener('change', function() {
                        if (this.value) {
                            saveLanguage(this.value);
                        }
                    });
                }
            }, 2000);
        }

        function applyDefaultLanguage() {
            // Cargar idioma guardado o usar el por defecto del sistema
            const savedLang = sessionStorage.getItem('language') || 
                             localStorage.getItem('preferredLanguage') || 
                             DEFAULT_LANGUAGE;
            
            if (savedLang && savedLang !== DEFAULT_LANGUAGE) {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.value = savedLang;
                    select.dispatchEvent(new Event('change'));
                }
            }
        }

        function saveLanguage(lang) {
            sessionStorage.setItem('language', lang);
            localStorage.setItem('preferredLanguage', lang);
        }

        // Social media login handlers
        // Funcionalidad removida - solo login nativo

        // Efectos adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Animaciones de entrada escalonadas
            const elements = document.querySelectorAll('.form-group, .login-btn, .demo-accounts');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 150 * (index + 1));
            });
        });

        // Manejar errores de conexión
        window.addEventListener('offline', function() {
            const form = document.getElementById('loginForm');
            const message = document.createElement('div');
            message.className = 'error-message';
            message.innerHTML = '🌐 Sin conexión a internet. Verifica tu conexión.';
            message.style.display = 'block';
            form.appendChild(message);
        });

        window.addEventListener('online', function() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                if (msg.textContent.includes('Sin conexión')) {
                    msg.remove();
                }
            });
        });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>