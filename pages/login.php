<?php
// =====================================
// ARCHIVO: pages/login.php - Login con Configuración Personalizada
// =====================================

// Obtener configuración de colores y empresa
$loginColors = App::getLoginColors();
$companyName = App::getCompanyName();
$logo = App::getLogo();
$defaultLanguage = App::getDefaultLanguage();
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?> 0%, <?= $loginColors['secondary'] ?> 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Google Translate en la esquina */
        .translate-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        #google_translate_element {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0px !important; }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?> 0%, <?= $loginColors['secondary'] ?> 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            font-weight: bold;
            overflow: hidden;
            position: relative;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }
        
        .company-name {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .company-subtitle {
            color: #718096;
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: <?= $loginColors['primary'] ?>;
            box-shadow: 0 0 0 3px <?= $loginColors['primary'] ?>20;
            transform: translateY(-1px);
        }
        
        .form-group input::placeholder {
            color: #a0aec0;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, <?= $loginColors['primary'] ?> 0%, <?= $loginColors['secondary'] ?> 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px <?= $loginColors['primary'] ?>40;
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .error-message {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #e53e3e;
            padding: 12px 16px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 14px;
            text-align: left;
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
            border-radius: 10px;
            margin-top: 15px;
            font-size: 14px;
            text-align: left;
        }
        
        .login-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 13px;
        }
        
        .demo-accounts {
            background: #f7fafc;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            text-align: left;
        }
        
        .demo-accounts h4 {
            color: #4a5568;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .demo-account {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .demo-account .username {
            color: #667eea;
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
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .translate-container {
                top: 10px;
                right: 10px;
            }
            
            .company-name {
                font-size: 20px;
            }
        }
        
        /* Session expired message */
        .session-expired {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #e53e3e;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- Google Translate -->
    <div class="translate-container">
        <div id="google_translate_element"></div>
    </div>

    <div class="login-container">
        <!-- Logo -->
        <div class="logo">
            <?php if ($logo): ?>
                <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($companyName) ?>">
            <?php else: ?>
                <?= strtoupper(substr($companyName, 0, 2)) ?>
            <?php endif; ?>
        </div>
        
        <!-- Company Info -->
        <h1 class="company-name"><?= htmlspecialchars($companyName) ?></h1>
        <p class="company-subtitle">Sistema de Gestión de Viajes</p>

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
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Ingrese su usuario" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ingrese su contraseña" autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Iniciar Sesión
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
        <div class="login-footer">
            <div class="demo-accounts">
                <h4>👥 Cuentas de Demostración:</h4>
                <div class="demo-account">
                    <span class="username">admin</span>
                    <span class="password">password</span>
                </div>
                <div class="demo-account">
                    <span class="username">agente1</span>
                    <span class="password">password</span>
                </div>
            </div>
            
            <p style="margin-top: 15px; font-size: 12px;">
                © <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. Todos los derechos reservados.
            </p>
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

        // Inicializar formulario
        function initializeForm() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');

            form.addEventListener('submit', function(e) {
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

        // Efectos adicionales
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada para elementos
            const elements = document.querySelectorAll('.form-group, .login-btn, .demo-accounts');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.4s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 100 * (index + 1));
            });

            // Partículas de fondo (opcional)
            createBackgroundParticles();
        });

        // Crear partículas de fondo sutiles
        function createBackgroundParticles() {
            const body = document.body;
            
            for (let i = 0; i < 6; i++) {
                const particle = document.createElement('div');
                particle.style.cssText = `
                    position: absolute;
                    width: ${Math.random() * 100 + 50}px;
                    height: ${Math.random() * 100 + 50}px;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 50%;
                    pointer-events: none;
                    animation: float ${Math.random() * 10 + 10}s infinite linear;
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                `;
                
                body.appendChild(particle);
            }
            
            // CSS para animación de flotación
            const style = document.createElement('style');
            style.textContent = `
                @keyframes float {
                    0% {
                        transform: translateY(0px) rotate(0deg);
                        opacity: 0;
                    }
                    50% {
                        opacity: 1;
                    }
                    100% {
                        transform: translateY(-100vh) rotate(360deg);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Detección de modo oscuro del sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // Ajustar colores si el usuario prefiere modo oscuro
            document.body.style.filter = 'brightness(0.9)';
        }

        // Manejar errores de conexión
        window.addEventListener('offline', function() {
            const form = document.getElementById('loginForm');
            const message = document.createElement('div');
            message.className = 'error-message';
            message.innerHTML = '🌐 Sin conexión a internet. Verifica tu conexión.';
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