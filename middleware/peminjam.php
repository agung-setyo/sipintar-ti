<?php
include_once __DIR__ . '/auth.php';

// Check if peminjam role is logged in (allow switching between roles)
if (!isset($_SESSION['logins']['peminjam'])) {
    // Only destroy session if trying to access peminjam area without any peminjam login
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    redirect_to('auth/user_login.php?error=' . urlencode('Silakan login menggunakan akun peminjam.'));
}

// Switch context to peminjam if accessing peminjam area
if (($_SESSION['current_role'] ?? '') !== 'peminjam' && isset($_SESSION['logins']['peminjam'])) {
    $_SESSION['current_role'] = 'peminjam';
    $_SESSION['user_id'] = $_SESSION['logins']['peminjam']['user_id'];
    $_SESSION['name'] = $_SESSION['logins']['peminjam']['name'];
    $_SESSION['role'] = 'peminjam';
    $_SESSION['identity_type'] = $_SESSION['logins']['peminjam']['identity_type'] ?? '';
}
?>
