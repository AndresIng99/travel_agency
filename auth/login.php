<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    App::redirect('/login');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Por favor complete todos los campos';
    App::redirect('/login');
}

try {
    $db = Database::getInstance();
    
    $user = $db->fetch(
        "SELECT id, username, password, full_name, role, active FROM users WHERE username = ? AND active = 1",
        [$username]
    );

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

    if ($user['role'] === 'admin') {
        App::redirect('/dashboard'); // Admin también va al dashboard primero
    } else {
        App::redirect('/dashboard');
    }
    } else {
        $_SESSION['error'] = 'Usuario o contraseña incorrectos';
        App::redirect('/login');
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Error del sistema. Intente nuevamente.';
    App::redirect('/login');
}
?>
