<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../helpers/log_helper.php';

global $conn;
$previousRole = $_SESSION['role'] ?? null;

if (isset($_SESSION['user_id'])) {
    save_log(
        $conn,
        $_SESSION['user_id'],
        'LOGOUT',
        'users',
        $_SESSION['user_id'],
        'User logout'
    );
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_unset();
session_destroy();

if ($previousRole === 'admin') {
    redirect_to('auth/admin_login.php');
}
if ($previousRole === 'peminjam') {
    redirect_to('auth/user_login.php');
}
redirect_to('auth/login.php');
exit;
?>
