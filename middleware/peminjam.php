<?php
include_once __DIR__ . '/auth.php';

if (($_SESSION['role'] ?? '') !== 'peminjam') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    redirect_to('auth/user_login.php?error=' . urlencode('Silakan login menggunakan akun peminjam.'));
}
?>
