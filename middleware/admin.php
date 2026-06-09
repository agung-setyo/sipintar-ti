<?php
include_once __DIR__ . '/auth.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect_to('auth/admin_login.php?error=' . urlencode('Silakan login menggunakan akun admin.'));
}
?>
