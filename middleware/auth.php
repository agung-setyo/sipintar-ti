<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';

$idleTimeout = 1800; // 30 menit

if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $idleTimeout) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_unset();
    session_destroy();
    redirect_to('auth/login.php?error=' . urlencode('Sesi berakhir karena tidak aktif. Silakan login kembali.'));
}

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    redirect_to('auth/login.php');
}

$_SESSION['last_activity'] = time();
?>
