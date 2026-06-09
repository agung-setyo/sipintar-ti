<?php
include_once __DIR__ . '/auth.php';

if (($_SESSION['role'] ?? '') !== 'peminjam') {
    redirect_to('auth/user_login.php?error=' . urlencode('Silakan login menggunakan akun peminjam.'));
}
?>
