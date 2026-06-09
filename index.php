<?php
include_once __DIR__ . '/config/session.php';
include_once __DIR__ . '/config/app.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$routePath = trim($requestPath, '/');
$basePath = trim(app_base_path(), '/');
if ($basePath !== '') {
    if ($routePath === $basePath) {
        $routePath = '';
    } elseif (strpos($routePath, $basePath . '/') === 0) {
        $routePath = substr($routePath, strlen($basePath) + 1);
    }
}

$targetPath = public_path_for_alias($routePath);
if ($targetPath !== null) {
    $targetFile = __DIR__ . '/' . $targetPath;
    if (is_file($targetFile)) {
        include $targetFile;
        exit;
    }
}

include_once __DIR__ . '/config/security.php';
include_once __DIR__ . '/config/database.php';

if (isset($_SESSION['role'])) {
    redirect_to($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'peminjam/dashboard.php');
}

$total_items = 0;
$total_categories = 0;
$total_available = 0;
if (isset($conn)) {
    $res = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM items');
    if ($res) $total_items = (int)mysqli_fetch_assoc($res)['total'];
    $res = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM categories');
    if ($res) $total_categories = (int)mysqli_fetch_assoc($res)['total'];
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE status='available' AND stock > 0");
    if ($res) $total_available = (int)mysqli_fetch_assoc($res)['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR-TI | Sistem Peminjaman Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLndnUkP4OYlT6DkL4kSVV8Vsl5W0RXp2Pl3T/jCGX0gLexyO3J54+lZ7c2tXj4w==" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-app-v2">
</head>
<body>
    <nav class="landing-nav">
        <div class="inner">
            <a class="brand-lockup landing-brand" href="<?= e(base_url()); ?>">
                <span class="brand-icon"><i class="fas fa-box-open"></i></span>
                <span class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></span>
            </a>
            <div class="landing-actions">
                <a class="btn-main secondary" href="<?= e(base_url('auth/register.php')); ?>"><i class="fas fa-user-plus"></i> Daftar</a>
                <a class="btn-main secondary" href="<?= e(base_url('auth/admin_login.php')); ?>"><i class="fas fa-user-tie"></i> Admin</a>
                <a class="btn-main primary" href="<?= e(base_url('auth/user_login.php')); ?>"><i class="fas fa-user-graduate"></i> Peminjam</a>
            </div>
        </div>
    </nav>
    <main class="landing-hero">
        <div class="landing-container">
            <section>
                <div class="hero-badge"><i class="fas fa-building"></i> Inventaris Teknik Informatika</div>
                <h1 class="landing-title">Peminjaman barang jadi lebih praktis dan teratur.</h1>
                <p class="landing-copy">SIPINTAR-TI membantu peminjam mengajukan barang, melihat status permintaan, dan membantu admin mengelola inventaris dengan tampilan yang sederhana.</p>
                <div class="landing-actions landing-actions-main">
                    <a class="btn-main primary" href="<?= e(base_url('auth/user_login.php')); ?>"><i class="fas fa-user-graduate"></i> Login Peminjam</a>
                    <a class="btn-main secondary" href="<?= e(base_url('auth/admin_login.php')); ?>"><i class="fas fa-user-tie"></i> Login Admin</a>
                    <a class="btn-main secondary" href="<?= e(base_url('auth/register.php')); ?>"><i class="fas fa-user-plus"></i> Buat Akun</a>
                </div>
                <div class="landing-stats">
                    <div class="landing-stat"><strong><?= $total_items; ?></strong><span>Total barang</span></div>
                    <div class="landing-stat"><strong><?= $total_available; ?></strong><span>Barang tersedia</span></div>
                    <div class="landing-stat"><strong><?= $total_categories; ?></strong><span>Kategori</span></div>
                </div>
            </section>
            <aside class="landing-card">
                <div class="preview-box">
                    <h3>Alur Peminjaman</h3>
                    <div class="preview-list">
                        <div><i class="fas fa-search"></i> Pilih barang dari katalog inventaris.</div>
                        <div><i class="fas fa-calendar-days"></i> Tentukan tanggal pinjam dan kembali.</div>
                        <div><i class="fas fa-clipboard-check"></i> Admin meninjau dan memproses permintaan.</div>
                        <div><i class="fas fa-clock-rotate-left"></i> Riwayat peminjaman dapat dipantau kapan saja.</div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
