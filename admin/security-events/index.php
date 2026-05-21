<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
$query = mysqli_query($conn, "SELECT * FROM security_events ORDER BY created_at DESC LIMIT 200");
$pageTitle = 'Notifikasi Sistem';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Notifikasi Sistem</h1><p>Daftar notifikasi teknis yang perlu diperhatikan admin.</p></div><a href="<?= e(base_url('admin/dashboard.php')); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a></div></div>
        <div class="table-wrapper"><div class="table-header"><h3>Daftar Notifikasi</h3></div><div class="table-body">
            <?php if ($query && mysqli_num_rows($query) > 0): ?>
                <table><thead><tr><th>No</th><th>Tipe</th><th>Level</th><th>IP</th><th>Keterangan</th><th>Waktu</th></tr></thead><tbody>
                <?php $no=1; while($row=mysqli_fetch_assoc($query)): ?>
                    <?php $sev=strtolower($row['severity']); $badge=['critical'=>'badge-danger','high'=>'badge-warning','medium'=>'badge-info','low'=>'badge-success'][$sev] ?? 'badge-secondary'; ?>
                    <tr><td><?= $no++; ?></td><td><?= e($row['event_type']); ?></td><td><span class="badge <?= e($badge); ?>"><?= e(ucfirst($sev)); ?></span></td><td><code><?= e($row['ip_address'] ?? '-'); ?></code></td><td><?= e($row['description'] ?? '-'); ?></td><td><?= e(date('d/m/Y H:i:s', strtotime($row['created_at']))); ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada notifikasi.</p></div>
            <?php endif; ?>
        </div></div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
