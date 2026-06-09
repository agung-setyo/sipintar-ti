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
    <title>Login Peminjam | SIPINTAR-TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLndnUkP4OYlT6DkL4kSVV8Vsl5W0RXp2Pl3T/jCGX0gLexyO3J54+lZ7c2tXj4w==" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-split-login">
</head>
<body>
    <main class="auth-shell compact-auth">
        <section class="auth-hero">
            <a class="brand-lockup" href="<?= e(base_url()); ?>">
                <div class="brand-icon"><i class="fas fa-box-open"></i></div>
                <div class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></div>
            </a>
            <div class="hero-badge"><i class="fas fa-user-graduate"></i> Akses Peminjam</div>
            <h1>Ajukan peminjaman barang dengan mudah.</h1>
            <p>Masuk sebagai peminjam untuk melihat katalog barang, mengajukan peminjaman, dan memantau status permintaan.</p>
        </section>
        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-header">
                    <h2>Login Peminjam</h2>
                    <p>Gunakan akun peminjam yang sudah terdaftar.</p>
                </div>
                <?php if (isset($_GET['success'])) : ?><div class="alert alert-success" role="status" aria-live="polite" id="form-success"><i class="fas fa-check-circle"></i> Registrasi berhasil. Silakan login.</div><?php endif; ?>
                <?php if (isset($_GET['error'])) : ?><div class="alert alert-danger" role="alert" aria-live="assertive" id="form-error"><i class="fas fa-circle-exclamation"></i> <?= e($_GET['error']); ?></div><?php endif; ?>
                <?php if (isset($_SESSION['flash'])) : ?><div class="alert alert-danger" role="alert" aria-live="assertive" id="form-error"><i class="fas fa-circle-exclamation"></i> <?= e($_SESSION['flash']); unset($_SESSION['flash']); ?></div><?php endif; ?>
                <form action="<?= e(base_url('auth/process_login.php')); ?>" method="POST" autocomplete="on" novalidate aria-describedby="form-error form-success">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
                    <input type="hidden" name="login_as" value="peminjam">
                    <div class="form-group">
                        <label class="form-label" for="email"><i class="fas fa-envelope"></i> Email</label>
                        <div class="input-wrap"><i class="fas fa-at"></i><input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus aria-required="true"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password"><i class="fas fa-key"></i> Password</label>
                        <div class="input-wrap"><i class="fas fa-lock"></i><input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required aria-required="true"></div>
                    </div>
                        <label class="password-toggle"><input type="checkbox" id="show-password"> Tampilkan password</label>
                        <button type="submit" class="btn-auth"><i class="fas fa-right-to-bracket"></i> Masuk sebagai Peminjam</button>
                </form>
                <div class="auth-footer">Belum punya akun? <a href="<?= e(base_url('auth/register.php')); ?>">Daftar sebagai peminjam</a><br><a href="<?= e(base_url('auth/login.php')); ?>">Kembali ke pilihan login</a></div>
            </div>
        </section>
    </main>
    <script src="<?= e(asset_url('js/auth.js')); ?>?v=20260530-auth"></script>
</body>
</html>
