<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
$result = mysqli_query($conn, 'SELECT c.*, COUNT(i.id) AS item_count FROM categories c LEFT JOIN items i ON c.id = i.category_id GROUP BY c.id ORDER BY c.created_at DESC');
$pageTitle = 'Kategori Barang';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Kategori Barang</h1><p>Kelola kelompok barang agar inventaris lebih mudah dicari.</p></div><a href="<?= e(base_url('admin/categories/create.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Kategori</a></div></div>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?= e($_GET['success']); ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= e($_GET['error']); ?></div><?php endif; ?>
        <div class="table-wrapper">
            <div class="table-header"><h3>Daftar Kategori</h3></div>
            <div class="table-body">
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <table>
                        <thead><tr><th>No</th><th>Nama</th><th>Deskripsi</th><th>Jumlah Barang</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php $no=1; while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= e($row['name']); ?></strong></td>
                                <td><?= e($row['description'] ?: '-'); ?></td>
                                <td><span class="badge badge-info"><?= (int)$row['item_count']; ?> barang</span></td>
                                <td><div class="action-row"><a href="<?= e(base_url('admin/categories/edit.php?id=' . (int)$row['id'])); ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen"></i> Edit</a><form action="<?= e(base_url('admin/categories/delete.php')); ?>" method="POST" data-confirm="Hapus kategori ini?"><?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button></form></div></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state"><i class="fas fa-layer-group"></i><p>Belum ada kategori.</p><a href="<?= e(base_url('admin/categories/create.php')); ?>" class="btn btn-primary">Tambah Kategori</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
