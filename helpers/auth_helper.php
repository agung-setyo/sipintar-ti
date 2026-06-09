<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('403 Forbidden'); }
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect_to('auth/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        redirect_to('auth/admin_login.php?error=' . urlencode('Silakan login menggunakan akun admin.'));
    }
}

function require_peminjam(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'peminjam') {
        http_response_code(403);
        redirect_to('auth/user_login.php?error=' . urlencode('Silakan login menggunakan akun peminjam.'));
    }
}
?>
