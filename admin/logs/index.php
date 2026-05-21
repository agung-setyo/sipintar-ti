<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
$query = mysqli_query($conn, "SELECT al.*, u.name AS user_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 200");
$pageTitle = 'Aktivitas Sistem';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Aktivitas Sistem</h1><p>Riwayat aktivitas penting yang terjadi di aplikasi.</p></div><a href="<?= e(base_url('admin/dashboard.php')); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a></div></div>
        <div class="table-wrapper"><div class="table-header"><h3>Daftar Aktivitas</h3></div><div class="table-body">
            <?php if ($query && mysqli_num_rows($query) > 0): ?>
                <table><thead><tr><th>No</th><th>Pengguna</th><th>Aksi</th><th>Data</th><th>IP</th><th>Keterangan</th><th>Waktu</th></tr></thead><tbody>
                    <?php $no=1; while($row=mysqli_fetch_assoc($query)): ?>
                        <tr><td><?= $no++; ?></td><td><?= e($row['user_name'] ?? 'Sistem'); ?></td><td><span class="badge badge-info"><?= e($row['action']); ?></span></td><td><?= e($row['entity']); ?> #<?= e($row['entity_id'] ?? '-'); ?></td><td><code><?= e($row['ip_address'] ?? '-'); ?></code></td><td><?= e($row['description'] ?? '-'); ?></td><td><?= e(date('d/m/Y H:i:s', strtotime($row['created_at']))); ?></td></tr>
                    <?php endwhile; ?>
                </tbody></table>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada aktivitas.</p></div>
            <?php endif; ?>
        </div></div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
