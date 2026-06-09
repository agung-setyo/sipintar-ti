<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | SIPINTAR-TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLndnUkP4OYlT6DkL4kSVV8Vsl5W0RXp2Pl3T/jCGX0gLexyO3J54+lZ7c2tXj4w==" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-app-v2">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-hero">
            <div class="brand-lockup">
                <div class="brand-icon"><i class="fas fa-box-open"></i></div>
                <div class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></div>
            </div>
            <div class="hero-badge"><i class="fas fa-user-plus"></i> Pendaftaran Peminjam</div>
            <h1>Buat akun untuk mulai meminjam barang.</h1>
            <p>Lengkapi identitas peminjam agar proses pengajuan barang dapat diverifikasi dan diproses oleh admin.</p>
            <div class="feature-row">
                <div class="feature-card"><i class="fas fa-id-card"></i><strong>Identitas Peminjam</strong><span>Data peminjam membantu admin memproses pengajuan dengan cepat.</span></div>
                <div class="feature-card"><i class="fas fa-calendar-check"></i><strong>Jadwal Pinjam</strong><span>Tentukan tanggal pinjam dan tanggal kembali sesuai kebutuhan.</span></div>
                <div class="feature-card"><i class="fas fa-history"></i><strong>Riwayat Tersimpan</strong><span>Cek kembali status dan riwayat peminjaman kapan saja.</span></div>
            </div>
        </section>
        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-header"><h2>Daftar Akun</h2><p>Isi data berikut untuk membuat akun peminjam.</p></div>
                <?php if (isset($_GET['error'])) : ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= e($_GET['error']); ?></div><?php endif; ?>
                <form action="<?= e(base_url('auth/process_register.php')); ?>" method="POST" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
                    <div class="form-group"><label class="form-label" for="name"><i class="fas fa-user"></i> Nama Lengkap</label><div class="input-wrap"><i class="fas fa-user"></i><input type="text" id="name" name="name" class="form-control" placeholder="Nama lengkap" required></div></div>
                    <div class="form-group"><label class="form-label" for="email"><i class="fas fa-envelope"></i> Email</label><div class="input-wrap"><i class="fas fa-at"></i><input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required></div></div>
                    <div class="form-group"><label class="form-label" for="password"><i class="fas fa-key"></i> Password</label><div class="input-wrap"><i class="fas fa-lock"></i><input type="password" id="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8"></div></div>
                    <div class="form-group"><label class="form-label" for="identity_type"><i class="fas fa-id-badge"></i> Jenis Identitas</label><select id="identity_type" name="identity_type" class="form-select" required><option value="">Pilih identitas</option><option value="dosen">Dosen</option><option value="mahasiswa">Mahasiswa</option></select></div>
                    <div class="form-group"><label class="form-label" for="identity_number"><i class="fas fa-hashtag"></i> NIM / NIP</label><div class="input-wrap"><i class="fas fa-fingerprint"></i><input type="text" id="identity_number" name="identity_number" class="form-control" placeholder="Masukkan NIM/NIP" required></div></div>
                    <button type="submit" class="btn-auth"><i class="fas fa-user-plus"></i> Daftar</button>
                </form>
                <div class="auth-footer">Sudah punya akun? <a href="<?= e(base_url('auth/user_login.php')); ?>">Login sekarang</a></div>
            </div>
        </section>
    </main>
</body>
</html>
