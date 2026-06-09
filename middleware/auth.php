<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
<<<<<<< HEAD

$idleTimeout = 1800; // 30 menit

if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $idleTimeout) {
=======
// Session inactivity timeout (seconds)
$idleTimeout = 1800; // 30 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idleTimeout) {
    // Destroy session on timeout
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_unset();
    session_destroy();
    redirect_to('auth/login.php?error=' . urlencode('Sesi berakhir karena tidak aktif. Silakan login kembali.'));
}

<<<<<<< HEAD
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    redirect_to('auth/login.php');
}

=======
if (!isset($_SESSION['user_id'])) {
    redirect_to('auth/login.php');
}

// Update last activity timestamp
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
$_SESSION['last_activity'] = time();
?>
