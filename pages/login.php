<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        .company-name {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 8px;
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
            margin-bottom: 5px;
            color: #4a5568;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .error-message {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #e53e3e;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
        }
        .login-footer {
            margin-top: 20px;
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">TA</div>
        <h1 class="company-name"><?= APP_NAME ?></h1>
        <p class="company-subtitle">Sistema de Gestión de Viajes</p>

        <form action="<?= APP_URL ?>/auth/login" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required placeholder="Ingrese su usuario">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="Ingrese su contraseña">
            </div>

            <button type="submit" class="login-btn">Iniciar Sesión</button>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </form>

        <div class="login-footer">
            <p>Usuarios de prueba:<br>
            <strong>admin</strong> / password<br>
            <strong>agente1</strong> / password</p>
        </div>
    </div>
</body>
</html>
