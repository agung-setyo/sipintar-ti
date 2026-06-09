<?php
include_once __DIR__ . '/../config/app.php';
$userName = $_SESSION['name'] ?? 'Pengguna';
$userRole = $_SESSION['role'] ?? 'guest';
$homePath = $userRole === 'admin' ? 'admin/dashboard.php' : 'peminjam/dashboard.php';
?>
<nav class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" type="button" data-action="toggle-sidebar" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
        <a class="brand" href="<?= e(base_url($homePath)); ?>">
            <span class="brand-mark"><i class="fas fa-box-open"></i></span>
            <span class="brand-copy">
                <strong>SIPINTAR-TI</strong>
                <small>Sistem Peminjaman Inventaris</small>
            </span>
        </a>
    </div>
    <div class="topbar-right">
        <div class="user-chip" aria-hidden="false">
            <span class="avatar" aria-hidden="true"><?= e(strtoupper(substr($userName, 0, 1))); ?></span>
            <span>
                <strong><?= e($userName); ?></strong>
                <small><?= e($userRole === 'admin' ? 'Admin' : 'Peminjam'); ?></small>
            </span>
        </div>
        <a href="<?= e(base_url('auth/change_password.php')); ?>" class="btn btn-outline-primary btn-sm" aria-label="Ubah password"><i class="fas fa-key"></i> Password</a>
        <a href="<?= e(base_url('auth/logout.php')); ?>" class="btn btn-outline-danger btn-sm" aria-label="Keluar dari akun"><i class="fas fa-right-from-bracket"></i> Keluar</a>
    </div>
</nav>
