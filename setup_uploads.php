<?php
// =====================================
// ARCHIVO: setup_uploads.php - Crear carpetas necesarias
// =====================================

// Crear este archivo en la raíz del proyecto y ejecutarlo una vez

$directories = [
    'assets/uploads',
    'assets/uploads/biblioteca',
    'assets/uploads/config'
];

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

// Crear archivo .htaccess para proteger uploads
$htaccessContent = '# Permitir solo imágenes
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Allow from all
</FilesMatch>

# Denegar todo lo demás
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$">
    Deny from all
</FilesMatch>';

file_put_contents('assets/uploads/.htaccess', $htaccessContent);
echo "✅ Archivo .htaccess creado para seguridad\n";

echo "\n🎉 Configuración de uploads completada!\n";
?>