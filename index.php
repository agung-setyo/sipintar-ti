<?php
include_once __DIR__ . '/config/session.php';
include_once __DIR__ . '/config/app.php';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260520-app-v2">
    <style>
        .landing-nav{position:fixed;top:0;left:0;right:0;z-index:10;background:rgba(255,255,255,.86);backdrop-filter:blur(16px);border-bottom:1px solid rgba(226,232,240,.9)}
        .landing-nav .inner{max-width:1180px;margin:0 auto;padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;gap:1rem}.landing-actions{display:flex;gap:.6rem;flex-wrap:wrap}.btn-main{display:inline-flex;align-items:center;gap:.45rem;border-radius:14px;padding:.72rem 1rem;font-weight:900;text-decoration:none}.btn-main.primary{background:var(--primary);color:#fff}.btn-main.secondary{background:#fff;color:var(--primary);border:1px solid var(--border)}.landing-hero{min-height:100vh;display:flex;align-items:center;padding:8rem 1.25rem 4rem}.landing-container{max-width:1180px;margin:0 auto;display:grid;grid-template-columns:1fr .85fr;gap:3rem;align-items:center}.landing-title{font-size:clamp(2.5rem,6vw,5rem);line-height:.98;letter-spacing:-.07em;font-weight:950;margin:1rem 0}.landing-copy{color:var(--muted);font-size:1.1rem;line-height:1.75;max-width:620px}.landing-card{background:#fff;border:1px solid var(--border);border-radius:30px;padding:1.4rem;box-shadow:var(--shadow)}.preview-box{border-radius:22px;background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;padding:1.3rem}.preview-list{display:grid;gap:.8rem;margin-top:1rem}.preview-list div{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);border-radius:16px;padding:.9rem}.landing-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:2rem}.landing-stat{background:rgba(255,255,255,.76);border:1px solid var(--border);border-radius:20px;padding:1rem}.landing-stat strong{display:block;font-size:2rem}.landing-stat span{color:var(--muted)}@media(max-width:900px){.landing-container{grid-template-columns:1fr}.landing-card{display:none}.landing-stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <nav class="landing-nav">
        <div class="inner">
            <a class="brand-lockup" href="<?= e(base_url()); ?>" style="margin:0;text-decoration:none;color:inherit">
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
                <div class="landing-actions" style="margin-top:1.6rem">
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
                    <h3 style="font-weight:900;margin:0">Alur Peminjaman</h3>
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
