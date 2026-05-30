<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/peminjam.php';

$search = trim($_GET['search'] ?? '');
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sql = "SELECT i.id, i.item_code, i.name, i.description, i.stock, i.item_condition, i.location, i.status, c.name AS category_name FROM items i JOIN categories c ON i.category_id = c.id WHERE 1=1";
$types = '';
$params = [];
if ($search !== '') {
    $sql .= " AND (i.name LIKE ? OR i.item_code LIKE ? OR i.description LIKE ? OR c.name LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}
if ($category > 0) {
    $sql .= ' AND i.category_id = ?';
    $params[] = $category;
    $types .= 'i';
}
$sql .= ' ORDER BY c.name ASC, i.name ASC';
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result();
$categories = mysqli_query($conn, 'SELECT id, name FROM categories ORDER BY name ASC');
$pageTitle = 'Katalog Barang';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Katalog Barang</h1><p>Pilih barang yang tersedia untuk diajukan peminjaman.</p></div><a href="<?= e(base_url('peminjam/borrow.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Pinjaman</a></div></div>
        <div class="filter-card panel-card">
            <form method="GET" class="action-row">
                <input type="text" name="search" value="<?= e($search); ?>" class="form-control max-w-320" placeholder="Cari nama, kode, atau kategori">
                <select name="category" class="form-select max-w-240">
                    <option value="0">Semua Kategori</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= (int)$cat['id']; ?>" <?= $category === (int)$cat['id'] ? 'selected' : ''; ?>><?= e($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <a href="<?= e(base_url('peminjam/items.php')); ?>" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <?php if ($items && $items->num_rows > 0): ?>
            <div class="catalog-grid">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <?php $available = $item['status'] === 'available' && (int)$item['stock'] > 0; ?>
                    <article class="catalog-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div><h3><?= e($item['name']); ?></h3><code><?= e($item['item_code']); ?></code></div>
                            <span class="badge <?= $available ? 'badge-success' : 'badge-danger'; ?>"><?= $available ? 'Tersedia' : 'Tidak tersedia'; ?></span>
                        </div>
                        <p class="catalog-desc"><?= e($item['description'] ?: 'Tidak ada deskripsi.'); ?></p>
                        <div class="catalog-meta"><span><i class="fas fa-layer-group"></i> <?= e($item['category_name']); ?></span><span><i class="fas fa-box"></i> Stok <?= (int)$item['stock']; ?></span><span><i class="fas fa-location-dot"></i> <?= e($item['location'] ?: '-'); ?></span></div>
                        <?php if ($available): ?>
                            <a href="<?= e(base_url('peminjam/borrow.php?item_id=' . (int)$item['id'])); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Pinjam Barang</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Tidak Tersedia</button>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-card"><div class="empty-state"><i class="fas fa-box-open"></i><p>Barang tidak ditemukan.</p></div></div>
        <?php endif; ?>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
