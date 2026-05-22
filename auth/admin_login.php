<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
$csrf = generate_csrf_token();
$logins = $_SESSION['logins'] ?? [];
$adminLoggedIn = isset($logins['admin']);
$peminjamLoggedIn = isset($logins['peminjam']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | SIPINTAR-TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-split-login">
</head>
<body>
    <main class="auth-shell compact-auth">
        <section class="auth-hero">
            <a class="brand-lockup" href="<?= e(base_url()); ?>" style="text-decoration:none;color:inherit">
                <div class="brand-icon"><i class="fas fa-box-open"></i></div>
                <div class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></div>
            </a>
            <div class="hero-badge"><i class="fas fa-user-tie"></i> Akses Admin</div>
            <h1>Kelola inventaris dengan cepat dan rapi.</h1>
            <p>Halaman ini khusus pengelola inventaris untuk memproses permintaan, mengatur stok, dan memperbarui data barang.</p>
        </section>
        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-header">
                    <h2>Login Admin</h2>
                    <p>Masukkan akun admin untuk membuka dashboard pengelola.</p>
                </div>
                <?php if (isset($_GET['error'])) : ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= e($_GET['error']); ?></div><?php endif; ?>
                <form action="<?= e(base_url('auth/process_login.php')); ?>" method="POST" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
                    <input type="hidden" name="login_as" value="admin">
                    <div class="form-group">
                        <label class="form-label" for="email"><i class="fas fa-envelope"></i> Email Admin</label>
                        <div class="input-wrap"><i class="fas fa-at"></i><input type="email" id="email" name="email" class="form-control" placeholder="admin@email.com" required autofocus></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password"><i class="fas fa-key"></i> Password</label>
                        <div class="input-wrap"><i class="fas fa-lock"></i><input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required></div>
                    </div>
                    <label class="password-toggle"><input type="checkbox" onclick="togglePassword()"> Tampilkan password</label>
                    <button type="submit" class="btn-auth"><i class="fas fa-right-to-bracket"></i> Masuk sebagai Admin</button>
                </form>
                <div class="auth-footer"><a href="<?= e(base_url('auth/login.php')); ?>">Kembali ke pilihan login</a></div>
            </div>
        </section>
    </main>
    <script>function togglePassword(){const input=document.getElementById('password');input.type=input.type==='password'?'text':'password';}</script>
</body>
</html>
