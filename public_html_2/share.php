<?php
require_once 'config/app.php';

$token = $_GET['t'] ?? null;
$type = $_GET['type'] ?? 'preview';

if (!$token) {
    die('Enlace inválido');
}

// Decodificar token
$decoded = base64_decode($token);
if (!$decoded || !strpos($decoded, '_')) {
    die('Token inválido');
}

$parts = explode('_', $decoded);
$programa_id = $parts[0] ?? null;

if (!$programa_id || !is_numeric($programa_id)) {
    die('Programa no encontrado');
}

// Establecer variables para que las páginas no pidan login
$_SESSION['temp_public_access'] = true;
$_GET['id'] = $programa_id;

// Redirigir a la página correcta
if ($type === 'itinerary') {
    header('Location: ' . APP_URL . '/itinerary?id=' . $programa_id . '&public=1');
} else {
    header('Location: ' . APP_URL . '/preview?id=' . $programa_id . '&public=1');
}
exit;
?>