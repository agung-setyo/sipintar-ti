<?php
include_once __DIR__ . '/config/session.php';
include_once __DIR__ . '/config/app.php';
include_once __DIR__ . '/config/security.php';

$code = (int)($_SERVER['REDIRECT_STATUS'] ?? 404);
if (!in_array($code, [403,404,500], true)) $code = 404;
http_response_code($code);
$pageTitle = $code === 403 ? '403 Forbidden' : '404 Not Found';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> | SIPINTAR-TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLndnUkP4OYlT6DkL4kSVV8Vsl5W0RXp2Pl3T/jCGX0gLexyO3J54+lZ7c2tXj4w==" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/auth.css')); ?>?v=20260604-error-fixed">
</head>
<body>
<main class="error-page-shell">
    <section class="error-card-clean">
        <a class="brand-lockup" href="<?= e(base_url()); ?>">
            <div class="brand-icon"><i class="fas fa-box-open"></i></div>
            <div class="brand-text"><strong>SIPINTAR-TI</strong><small>Sistem Peminjaman Inventaris</small></div>
        </a>
        <div class="hero-badge"><i class="fas fa-shield-halved"></i> Akses Dilindungi</div>
        <h1><?= e($pageTitle); ?></h1>
        <p>Halaman tidak tersedia atau akses tidak diizinkan.</p>
        <a class="btn-auth btn-auth-link" href="<?= e(base_url()); ?>">Kembali</a>
    </section>
</main>
</body>
</html>
