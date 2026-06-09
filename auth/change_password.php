<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';

$csrf = generate_csrf_token();
$pageTitle = 'Ubah Password';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div class="header-row">
                <div>
                    <h1>Ubah Password</h1>
                    <p>Perbarui password akun Anda secara mandiri dan aman.</p>
                </div>
            </div>
        </div>
        <div class="panel-card max-w-720">
            <div class="card-header-clean"><h3><i class="fas fa-key"></i> Form Ubah Password</h3></div>
            <div class="card-body-clean">
                <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= e($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= e($success); ?></div><?php endif; ?>
                <form method="post" action="<?= e(base_url('auth/process_change_password.php')); ?>" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
                    <div class="form-group">
                        <label class="form-label" for="current_password"><i class="fas fa-lock"></i> Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required maxlength="128" autocomplete="current-password">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="new_password"><i class="fas fa-key"></i> Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" maxlength="128" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password"><i class="fas fa-check"></i> Konfirmasi Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" maxlength="128" autocomplete="new-password">
                        </div>
                    </div>
                    <p class="text-muted mb-3">Gunakan minimal 8 karakter. Jangan gunakan password lama atau password yang mudah ditebak.</p>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Password Baru</button>
                    <a href="<?= e(base_url(($_SESSION['role'] ?? '') === 'admin' ? 'admin/dashboard.php' : 'peminjam/dashboard.php')); ?>" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
