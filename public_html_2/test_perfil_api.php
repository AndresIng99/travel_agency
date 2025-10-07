<?php
// =====================================
// ARCHIVO: test_perfil_api.php - ELIMINAR DESPUÉS DE USAR
// =====================================

echo "<h2>🔍 Prueba de API de Perfil</h2>";

// Verificar que el archivo existe
$apiFile = 'perfil/api.php';
if (file_exists($apiFile)) {
    echo "✅ Archivo perfil/api.php encontrado<br>";
} else {
    echo "❌ Archivo perfil/api.php NO encontrado<br>";
    exit;
}

// Verificar permisos
if (is_readable($apiFile)) {
    echo "✅ Archivo es legible<br>";
} else {
    echo "❌ Archivo NO es legible<br>";
}

// Simular acceso directo a la API
echo "<h3>📡 Probando acceso directo a la API:</h3>";

try {
    // Incluir dependencias
    require_once 'config/database.php';
    require_once 'config/app.php';
    
    // Inicializar
    App::init();
    
    echo "✅ App inicializada correctamente<br>";
    
    // Verificar si hay sesión activa
    if (App::isLoggedIn()) {
        $user = App::getUser();
        echo "✅ Usuario logueado: " . htmlspecialchars($user['name']) . "<br>";
        echo "📋 Rol: " . htmlspecialchars($user['role']) . "<br>";
        
        if ($user['role'] === 'agent') {
            echo "✅ Usuario es agente - acceso permitido<br>";
        } else {
            echo "❌ Usuario no es agente - acceso denegado<br>";
        }
    } else {
        echo "❌ No hay sesión activa<br>";
    }
    
    // Verificar base de datos
    $db = Database::getInstance();
    echo "✅ Conexión a base de datos exitosa<br>";
    
} catch(Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h3>🌐 Información del Servidor:</h3>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NO_DEFINIDO') . "<br>";
echo "CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'NO_DEFINIDO') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NO_DEFINIDO') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO_DEFINIDO') . "<br>";

echo "<h3>🔗 URLs para probar:</h3>";
$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
$currentPath = dirname($_SERVER['REQUEST_URI']);
echo "URL base: " . $baseUrl . "<br>";
echo "Perfil: <a href='" . $baseUrl . $currentPath . "/perfil'>" . $baseUrl . $currentPath . "/perfil</a><br>";
echo "API: " . $baseUrl . $currentPath . "/perfil/api<br>";

echo "<br><strong>📝 Instrucciones:</strong><br>";
echo "1. Asegúrate de estar logueado como agente<br>";
echo "2. Ve a la página de perfil usando el enlace de arriba<br>";
echo "3. Abre las herramientas de desarrollador (F12)<br>";
echo "4. Intenta cambiar la contraseña y revisa la consola<br>";
echo "5. Copia el 'Texto de respuesta' que aparece en la consola<br>";
echo "6. <strong>Elimina este archivo cuando termines</strong><br>";
?>