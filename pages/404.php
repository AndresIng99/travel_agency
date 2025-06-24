<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - <?= APP_NAME ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        .error-container { max-width: 500px; padding: 40px; }
        .error-code { font-size: 120px; font-weight: bold; margin-bottom: 20px; }
        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1>Página no encontrada</h1>
        <p>La página que buscas no existe.</p>
        <a href="<?= APP_URL ?>/dashboard" class="back-button">Volver</a>
    </div>
</body>
</html>

=====================================
ARCHIVO: pages/admin.php
=====================================
<?php $user = App::getUser(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-content {
            padding: 30px;
        }
        .admin-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Panel de Administrador</h2>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span><?= htmlspecialchars($user['name']) ?></span>
            <a href="<?= APP_URL ?>/dashboard" class="logout-btn">Dashboard</a>
            <a href="<?= APP_URL ?>/auth/logout" class="logout-btn">Salir</a>
        </div>
    </div>

    <div class="main-content">
        <div class="admin-section">
            <h1>Panel de Administración</h1>
            <p>Bienvenido al panel de administrador. Desde aquí puedes gestionar usuarios y configuración.</p>
        </div>
    </div>
</body>
</html>
