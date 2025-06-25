<?php
// =====================================
// ARCHIVO: setup_programa_uploads.php
// Ejecutar UNA VEZ para crear las carpetas necesarias
// =====================================

$directories = [
    'assets/uploads',
    'assets/uploads/programa',
    'assets/uploads/programa/' . date('Y'),
    'assets/uploads/programa/' . date('Y/m')
];

echo "🚀 Configurando carpetas para módulo Programa...\n\n";

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
$htaccessContent = '# Permitir solo imágenes en uploads de programa
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Denegar archivos ejecutables
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Denegar acceso a archivos de configuración
<FilesMatch "\.(htaccess|htpasswd|ini|log|sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>';

$htaccessPath = 'assets/uploads/programa/.htaccess';
if (file_put_contents($htaccessPath, $htaccessContent)) {
    echo "✅ Archivo .htaccess creado para seguridad: {$htaccessPath}\n";
} else {
    echo "❌ Error creando .htaccess: {$htaccessPath}\n";
}

// Crear archivo index.php para evitar listado de directorios
$indexContent = '<?php
// Archivo de protección - No eliminar
header("HTTP/1.1 403 Forbidden");
exit("Acceso no autorizado");
?>';

$indexPath = 'assets/uploads/programa/index.php';
if (file_put_contents($indexPath, $indexContent)) {
    echo "✅ Archivo de protección creado: {$indexPath}\n";
} else {
    echo "❌ Error creando index.php: {$indexPath}\n";
}

echo "\n🎉 Configuración de uploads para Programa completada!\n";
echo "📁 Las imágenes se guardarán en: assets/uploads/programa/YYYY/MM/\n";
echo "🔒 Carpetas protegidas con .htaccess\n";
echo "\n⚠️  IMPORTANTE: Ejecuta este script solo UNA VEZ\n";
echo "💡 Puedes eliminar este archivo después de ejecutarlo\n";
?>