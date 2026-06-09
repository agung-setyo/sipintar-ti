<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../helpers/log_helper.php';

global $conn;
$currentRole = $_SESSION['current_role'] ?? $_SESSION['role'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    save_log(
        $conn,
        $userId,
        'LOGOUT',
        'users',
        $userId,
        'User logout'
    );
}

// Initialize logins array if not exists
if (!isset($_SESSION['logins'])) {
    $_SESSION['logins'] = [];
}

// Remove only the current role from logins
if ($currentRole && isset($_SESSION['logins'][$currentRole])) {
    unset($_SESSION['logins'][$currentRole]);
}

// Check if there are other logged-in roles
$remainingLogins = $_SESSION['logins'] ?? [];

if (empty($remainingLogins)) {
    // No more logins, destroy entire session
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
    redirect_to('auth/login.php');
} else {
    // Switch to next available role
    $nextRole = key($remainingLogins);
    $_SESSION['current_role'] = $nextRole;
    $_SESSION['user_id'] = $remainingLogins[$nextRole]['user_id'];
    $_SESSION['name'] = $remainingLogins[$nextRole]['name'];
    $_SESSION['role'] = $nextRole;
    $_SESSION['identity_type'] = $remainingLogins[$nextRole]['identity_type'] ?? '';
    
    // Redirect to main login page showing remaining logins
    redirect_to('auth/login.php');
}
exit;
?>
