<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';

// Get the role to switch to
$role = $_GET['role'] ?? null;

if (!in_array($role, ['admin', 'peminjam'], true)) {
    redirect_to('auth/login.php');
}

// Check if the role is available in logins
if (!isset($_SESSION['logins'][$role])) {
    redirect_to('auth/login.php?error=' . urlencode('Role tidak tersedia atau session sudah berakhir.'));
}

// Switch to the requested role
$_SESSION['current_role'] = $role;
$_SESSION['user_id'] = $_SESSION['logins'][$role]['user_id'];
$_SESSION['name'] = $_SESSION['logins'][$role]['name'];
$_SESSION['role'] = $role;
$_SESSION['identity_type'] = $_SESSION['logins'][$role]['identity_type'] ?? '';

// Redirect to appropriate dashboard
if ($role === 'admin') {
    redirect_to('admin/dashboard.php');
} else {
    redirect_to('peminjam/dashboard.php');
}
exit;
?>
