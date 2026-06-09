<?php
include_once __DIR__ . '/auth.php';

<<<<<<< HEAD
if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect_to('auth/admin_login.php?error=' . urlencode('Silakan login menggunakan akun admin.'));
}
=======
// Check if admin role is logged in (allow switching between roles)
if (!isset($_SESSION['logins']['admin'])) {
    // Only destroy session if trying to access admin area without any admin login
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    redirect_to('auth/admin_login.php?error=' . urlencode('Silakan login menggunakan akun admin.'));
}

// Switch context to admin if accessing admin area
if (($_SESSION['current_role'] ?? '') !== 'admin' && isset($_SESSION['logins']['admin'])) {
    $_SESSION['current_role'] = 'admin';
    $_SESSION['user_id'] = $_SESSION['logins']['admin']['user_id'];
    $_SESSION['name'] = $_SESSION['logins']['admin']['name'];
    $_SESSION['role'] = 'admin';
    $_SESSION['identity_type'] = $_SESSION['logins']['admin']['identity_type'] ?? '';
}
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
?>
