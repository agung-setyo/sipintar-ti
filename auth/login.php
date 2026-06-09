<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
$logins = $_SESSION['logins'] ?? [];
$adminLoggedIn = isset($logins['admin']);
$peminjamLoggedIn = isset($logins['peminjam']);
$currentRole = $_SESSION['current_role'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Login | SIPINTAR-TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLndnUkP4OYlT6DkL4kSVV8Vsl5W0RXp2Pl3T/jCGX0gLexyO3J54+lZ7c2tXj4w==" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-split-login">
</head>
<body>
    <main class="login-choice-shell">
        <section class="login-choice-card">
            <a class="choice-brand" href="<?= e(base_url()); ?>">
                <span class="brand-icon"><i class="fas fa-box-open"></i></span>
                <span class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></span>
            </a>
            <div class="choice-kicker"><i class="fas fa-right-to-bracket"></i> Pilih Akses Login</div>
            <h1>Masuk sesuai jenis akun Anda.</h1>
            <p class="choice-subtitle">Gunakan pintu masuk yang sesuai agar dashboard, menu, dan hak akses tampil dengan benar.</p>

            <?php if ($adminLoggedIn || $peminjamLoggedIn) : ?>
                <div class="alert alert-success" role="status" aria-live="polite" id="active-logins"><i class="fas fa-check-circle"></i> <strong>Login Aktif:</strong>
                    <?php if ($adminLoggedIn) : ?>
                        <span class="badge bg-primary me-2"><i class="fas fa-user-tie"></i> Admin: <?= e($logins['admin']['name']); ?></span>
                    <?php endif; ?>
                    <?php if ($peminjamLoggedIn) : ?>
                        <span class="badge bg-success"><i class="fas fa-user-graduate"></i> Peminjam: <?= e($logins['peminjam']['name']); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])) : ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> Registrasi berhasil. Silakan login sebagai peminjam.</div><?php endif; ?>
            <?php if (isset($_GET['error'])) : ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= e($_GET['error']); ?></div><?php endif; ?>
            <?php if (isset($_SESSION['flash'])) : ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= e($_SESSION['flash']); unset($_SESSION['flash']); ?></div><?php endif; ?>

            <div class="choice-grid">
                <a class="choice-option admin-option" href="<?= e(base_url('auth/admin_login.php')); ?>">
                    <span class="choice-icon"><i class="fas fa-user-tie"></i></span>
                    <span class="choice-content">
                        <strong>Login Admin</strong>
                        <small>Kelola barang, kategori, stok, dan permintaan peminjaman.</small>
                        <?php if ($adminLoggedIn) : ?><span class="badge bg-primary position-absolute top-0 end-0">Sudah Login</span><?php endif; ?>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a class="choice-option user-option" href="<?= e(base_url('auth/user_login.php')); ?>">
                    <span class="choice-icon"><i class="fas fa-user-graduate"></i></span>
                    <span class="choice-content">
                        <strong>Login Peminjam</strong>
                        <small>Ajukan peminjaman barang dan pantau status permintaan.</small>
                        <?php if ($peminjamLoggedIn) : ?><span class="badge bg-success position-absolute top-0 end-0">Sudah Login</span><?php endif; ?>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="choice-footer">
                Belum punya akun peminjam? <a href="<?= e(base_url('auth/register.php')); ?>">Daftar di sini</a>
            </div>
        </section>
    </main>
</body>
</html>
