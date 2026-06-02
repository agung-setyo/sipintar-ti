<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
include_once __DIR__ . '/../../helpers/log_helper.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$stmt = mysqli_prepare($conn, 'SELECT id, name, description FROM categories WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$row) redirect_to('admin/categories/index.php?error=' . urlencode('Kategori tidak ditemukan.'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $error = 'Sesi tidak valid. Silakan coba lagi.';
    else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '' || strlen($name) > 100) $error = 'Nama kategori wajib diisi dan maksimal 100 karakter.';
        else {
            $update = mysqli_prepare($conn, 'UPDATE categories SET name = ?, description = ? WHERE id = ?');
            mysqli_stmt_bind_param($update, 'ssi', $name, $description, $id);
            if (mysqli_stmt_execute($update)) {
                save_log($conn, $_SESSION['user_id'], 'UPDATE', 'categories', $id, 'Mengubah kategori');
                redirect_to('admin/categories/index.php?success=' . urlencode('Kategori berhasil diperbarui.'));
            }
            $error = 'Gagal memperbarui kategori.';
        }
    }
}
$pageTitle = 'Edit Kategori';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Edit Kategori</h1><p>Perbarui informasi kategori barang.</p></div><a href="<?= e(base_url('admin/categories/index.php')); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a></div></div>
        <div class="form-card max-w-760"><div class="card-header-clean"><h3>Data Kategori</h3></div><div class="card-body-clean"><?php if ($error): ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?><form method="POST"><?= csrf_input_field(); ?><div class="form-group"><label for="name">Nama Kategori</label><input type="text" id="name" name="name" value="<?= e($row['name']); ?>" class="form-control" maxlength="100" required></div><div class="form-group"><label for="description">Deskripsi</label><textarea id="description" name="description" class="form-control" rows="4"><?= e($row['description'] ?? ''); ?></textarea></div><div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Simpan</button><a href="<?= e(base_url('admin/categories/index.php')); ?>" class="btn btn-secondary">Batal</a></div></form></div></div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
