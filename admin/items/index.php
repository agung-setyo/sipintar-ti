<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
$query = mysqli_query($conn, "SELECT items.*, categories.name AS category_name FROM items JOIN categories ON items.category_id = categories.id ORDER BY items.created_at DESC");
$pageTitle = 'Data Barang';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Data Barang</h1><p>Kelola stok, lokasi, kondisi, dan status barang inventaris.</p></div><a href="<?= e(base_url('admin/items/create.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Barang</a></div></div>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?= e($_GET['success']); ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= e($_GET['error']); ?></div><?php endif; ?>
        <div class="table-wrapper">
            <div class="table-header"><h3>Daftar Barang</h3></div>
            <div class="table-body">
                <?php if ($query && mysqli_num_rows($query) > 0) : ?>
                    <table>
                        <thead><tr><th>No</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Kondisi</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php $no=1; while ($row = mysqli_fetch_assoc($query)) : ?>
                            <?php $available = $row['status'] === 'available' && (int)$row['stock'] > 0; ?>
                            <tr>
                                <td><?= $no++; ?></td><td><code><?= e($row['item_code']); ?></code></td><td><strong><?= e($row['name']); ?></strong></td><td><?= e($row['category_name']); ?></td><td><strong><?= (int)$row['stock']; ?></strong></td><td><?= e(str_replace('_',' ', ucfirst($row['item_condition']))); ?></td><td><?= e($row['location'] ?: '-'); ?></td><td><span class="badge <?= $available ? 'badge-success' : 'badge-danger'; ?>"><?= $available ? 'Tersedia' : 'Tidak tersedia'; ?></span></td>
                                <td><div class="action-row"><a href="<?= e(base_url('admin/items/edit.php?id=' . (int)$row['id'])); ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen"></i> Edit</a><form action="<?= e(base_url('admin/items/delete.php')); ?>" method="POST" data-confirm="Hapus barang ini?"><?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button></form></div></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state"><i class="fas fa-box-open"></i><p>Belum ada barang.</p><a href="<?= e(base_url('admin/items/create.php')); ?>" class="btn btn-primary">Tambah Barang</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
