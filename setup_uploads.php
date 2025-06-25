<?php
// =====================================
// ARCHIVO: setup_uploads_programa.php
// Ejecutar UNA VEZ para crear las carpetas necesarias
// =====================================

echo "🚀 Configurando carpetas para uploads de programas...\n\n";

// Definir carpetas necesarias
$baseDir = __DIR__ . '/assets/uploads/programa/';
$currentYear = date('Y');
$currentMonth = date('m');

$directories = [
    'assets',
    'assets/uploads',
    'assets/uploads/programa',
    "assets/uploads/programa/{$currentYear}",
    "assets/uploads/programa/{$currentYear}/{$currentMonth}"
];

// Crear carpetas
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Carpeta creada: {$dir}\n";
        } else {
            echo "❌ Error creando carpeta: {$dir}\n";
        }
    } else {
        echo "✅ Carpeta ya existe: {$dir}\n";
    }
}

// Crear archivo .htaccess para seguridad
$htaccessContent = '# Configuración de seguridad para uploads de programa
Options -Indexes
DirectoryIndex disabled

# Permitir solo imágenes
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Denegar archivos ejecutables
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi|exe)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Denegar archivos de configuración
<FilesMatch "\.(htaccess|htpasswd|ini|log|sql|conf)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Headers de seguridad
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
</IfModule>';

$htaccessPath = 'assets/uploads/programa/.htaccess';
if (file_put_contents($htaccessPath, $htaccessContent)) {
    echo "✅ Archivo .htaccess creado: {$htaccessPath}\n";
} else {
    echo "❌ Error creando .htaccess: {$htaccessPath}\n";
}

// Crear archivo index.php para evitar listado de directorios
$indexContent = '<?php
// Archivo de protección - No eliminar
header("HTTP/1.1 403 Forbidden");
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Acceso Denegado</title>
</head>
<body>
    <h1>403 - Acceso No Autorizado</h1>
    <p>No tienes permisos para acceder a este directorio.</p>
</body>
</html>';

$indexPath = 'assets/uploads/programa/index.php';
if (file_put_contents($indexPath, $indexContent)) {
    echo "✅ Archivo de protección creado: {$indexPath}\n";
} else {
    echo "❌ Error creando index.php: {$indexPath}\n";
}

// Crear archivo de configuración para uploads
$configContent = '<?php
// Configuración de uploads para programas
define("PROGRAMA_UPLOAD_DIR", __DIR__);
define("PROGRAMA_MAX_FILE_SIZE", 5 * 1024 * 1024); // 5MB
define("PROGRAMA_ALLOWED_TYPES", ["image/jpeg", "image/png", "image/gif", "image/webp"]);
define("PROGRAMA_ALLOWED_EXTENSIONS", ["jpg", "jpeg", "png", "gif", "webp"]);

// Función para validar archivos
function validarArchivoPrograma($file) {
    if (!in_array($file["type"], PROGRAMA_ALLOWED_TYPES)) {
        return "Tipo de archivo no permitido";
    }
    
    if ($file["size"] > PROGRAMA_MAX_FILE_SIZE) {
        return "Archivo demasiado grande";
    }
    
    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($extension, PROGRAMA_ALLOWED_EXTENSIONS)) {
        return "Extensión no permitida";
    }
    
    return true;
}
?>';

$configPath = 'assets/uploads/programa/config.php';
if (file_put_contents($configPath, $configContent)) {
    echo "✅ Archivo de configuración creado: {$configPath}\n";
} else {
    echo "❌ Error creando config.php: {$configPath}\n";
}

// Verificar permisos
echo "\n📋 Verificando permisos...\n";

$testDirs = [
    'assets/uploads/programa',
    "assets/uploads/programa/{$currentYear}",
    "assets/uploads/programa/{$currentYear}/{$currentMonth}"
];

foreach ($testDirs as $dir) {
    if (is_writable($dir)) {
        echo "✅ {$dir} - Escribible\n";
    } else {
        echo "⚠️  {$dir} - No escribible (chmod 755 requerido)\n";
    }
}

echo "\n🎉 Configuración completada!\n";
echo "📁 Las imágenes se guardarán en: assets/uploads/programa/YYYY/MM/\n";
echo "🔒 Carpetas protegidas con .htaccess\n";
echo "📋 Configuración guardada en: assets/uploads/programa/config.php\n\n";

echo "⚠️  IMPORTANTE:\n";
echo "1. Ejecuta este script solo UNA VEZ\n";
echo "2. Verifica que las carpetas tengan permisos 755\n";
echo "3. Puedes eliminar este archivo después de ejecutarlo\n";
echo "4. Las URLs de imágenes serán: " . (defined('APP_URL') ? APP_URL : 'TU_DOMINIO') . "/assets/uploads/programa/YYYY/MM/archivo.jpg\n\n";

// Test de creación de archivo
echo "🧪 Realizando test de escritura...\n";
$testFile = "assets/uploads/programa/{$currentYear}/{$currentMonth}/test_" . time() . ".txt";
if (file_put_contents($testFile, "Test de escritura - " . date('Y-m-d H:i:s'))) {
    echo "✅ Test de escritura exitoso: {$testFile}\n";
    unlink($testFile); // Eliminar archivo de test
    echo "✅ Archivo de test eliminado\n";
} else {
    echo "❌ Error en test de escritura\n";
}

echo "\n✅ ¡Todo listo para subir imágenes de programas!\n";
?>