<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
include_once __DIR__ . '/../../helpers/log_helper.php';
$error = '';
$allowed_conditions = ['baik', 'rusak_ringan', 'rusak'];
$allowed_statuses = ['available', 'unavailable'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $error = 'Sesi tidak valid. Silakan coba lagi.';
    else {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $item_code = trim($_POST['item_code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $stock = (int)($_POST['stock'] ?? 0);
        $item_condition = $_POST['item_condition'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $status = $_POST['status'] ?? '';
        if ($category_id < 1 || $item_code === '' || $name === '' || $stock < 0 || !in_array($item_condition, $allowed_conditions, true) || !in_array($status, $allowed_statuses, true)) $error = 'Input barang belum lengkap atau tidak valid.';
        else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO items (category_id, item_code, name, description, stock, item_condition, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'isssisss', $category_id, $item_code, $name, $description, $stock, $item_condition, $location, $status);
            if (mysqli_stmt_execute($stmt)) {
                save_log($conn, $_SESSION['user_id'], 'CREATE', 'items', mysqli_insert_id($conn), 'Menambahkan barang');
                redirect_to('admin/items/index.php?success=' . urlencode('Barang berhasil ditambahkan.'));
            }
            $error = 'Gagal menyimpan barang. Pastikan kode barang belum digunakan.';
        }
    }
}
$categories = mysqli_query($conn, 'SELECT id, name FROM categories ORDER BY name ASC');
$pageTitle = 'Tambah Barang';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Tambah Barang</h1><p>Lengkapi data barang baru yang akan masuk inventaris.</p></div><a href="<?= e(base_url('admin/items/index.php')); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a></div></div>
        <div class="form-card max-w-920"><div class="card-header-clean"><h3>Data Barang</h3></div><div class="card-body-clean">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
            <form method="POST"><?= csrf_input_field(); ?>
                <div class="form-grid"><div class="form-group"><label for="category_id">Kategori</label><select id="category_id" name="category_id" class="form-select" required><option value="">Pilih kategori</option><?php while ($cat = mysqli_fetch_assoc($categories)) : ?><option value="<?= (int)$cat['id']; ?>"><?= e($cat['name']); ?></option><?php endwhile; ?></select></div><div class="form-group"><label for="item_code">Kode Barang</label><input type="text" id="item_code" name="item_code" class="form-control" maxlength="50" placeholder="Contoh: PRJ-001" required></div></div>
                <div class="form-group"><label for="name">Nama Barang</label><input type="text" id="name" name="name" class="form-control" maxlength="100" required></div>
                <div class="form-group"><label for="description">Deskripsi</label><textarea id="description" name="description" class="form-control" rows="4"></textarea></div>
                <div class="form-grid"><div class="form-group"><label for="stock">Stok</label><input type="number" id="stock" name="stock" class="form-control" min="0" required></div><div class="form-group"><label for="item_condition">Kondisi</label><select id="item_condition" name="item_condition" class="form-select" required><option value="">Pilih kondisi</option><option value="baik">Baik</option><option value="rusak_ringan">Rusak Ringan</option><option value="rusak">Rusak</option></select></div></div>
                <div class="form-grid"><div class="form-group"><label for="location">Lokasi</label><input type="text" id="location" name="location" class="form-control" maxlength="100" placeholder="Contoh: Lab Multimedia"></div><div class="form-group"><label for="status">Status</label><select id="status" name="status" class="form-select" required><option value="available">Tersedia</option><option value="unavailable">Tidak tersedia</option></select></div></div>
                <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="<?= e(base_url('admin/items/index.php')); ?>" class="btn btn-secondary">Batal</a></div>
            </form>
        </div></div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
