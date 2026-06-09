<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/admin.php';

global $conn;
function count_table(mysqli $conn, string $sql): int
{
    $result = mysqli_query($conn, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}
$total_users = count_table($conn, "SELECT COUNT(*) AS total FROM users WHERE role='peminjam'");
$total_items = count_table($conn, 'SELECT COUNT(*) AS total FROM items');
$total_available = count_table($conn, "SELECT COUNT(*) AS total FROM items WHERE status='available' AND stock > 0");
$total_pending = count_table($conn, "SELECT COUNT(*) AS total FROM borrow_requests WHERE status='pending'");
$total_active = count_table($conn, "SELECT COUNT(*) AS total FROM borrow_requests WHERE status='approved'");
$total_returned = count_table($conn, "SELECT COUNT(*) AS total FROM borrow_requests WHERE status='returned'");
$total_rejected = count_table($conn, "SELECT COUNT(*) AS total FROM borrow_requests WHERE status IN ('rejected','cancelled')");
$recent_borrows = mysqli_query($conn, "
    SELECT br.id, br.request_code, br.status, br.created_at, u.name AS user_name,
           GROUP_CONCAT(CONCAT(i.name, ' (', bd.quantity, ')') SEPARATOR ', ') AS item_names
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id
    LEFT JOIN items i ON bd.item_id = i.id
    GROUP BY br.id
    ORDER BY br.created_at DESC
    LIMIT 7
");
$pageTitle = 'Dashboard Admin';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div class="header-row">
                <div>
                    <h1>Dashboard Admin</h1>
                    <p>Ringkasan inventaris dan permintaan peminjaman barang.</p>
                </div>
                <div class="action-row">
                    <a href="<?= e(base_url('admin/items/create.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Barang</a>
                    <a href="<?= e(base_url('admin/borrow/index.php')); ?>" class="btn btn-warning"><i class="fas fa-clipboard-list"></i> Lihat Permintaan</a>
                </div>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card info"><div class="stat-card-body"><div class="stat-content"><h5>Peminjam</h5><div class="stat-number"><?= $total_users; ?></div><div class="stat-change">Akun peminjam terdaftar</div></div><div class="stat-icon"><i class="fas fa-users"></i></div></div></div>
            <div class="stat-card success"><div class="stat-card-body"><div class="stat-content"><h5>Barang Tersedia</h5><div class="stat-number"><?= $total_available; ?></div><div class="stat-change">Dari <?= $total_items; ?> barang inventaris</div></div><div class="stat-icon"><i class="fas fa-box-open"></i></div></div></div>
            <div class="stat-card warning"><div class="stat-card-body"><div class="stat-content"><h5>Menunggu</h5><div class="stat-number"><?= $total_pending; ?></div><div class="stat-change">Perlu diproses admin</div></div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div></div>
            <div class="stat-card danger"><div class="stat-card-body"><div class="stat-content"><h5>Sedang Dipinjam</h5><div class="stat-number"><?= $total_active; ?></div><div class="stat-change">Belum dikembalikan</div></div><div class="stat-icon"><i class="fas fa-hand-holding"></i></div></div></div>
        </div>
        <div class="stats-grid">
            <div class="panel-card"><div class="card-header-clean"><h3>Dikembalikan</h3></div><div class="card-body-clean"><div class="stat-number"><?= $total_returned; ?></div><p class="mb-0 text-muted">Peminjaman selesai.</p></div></div>
            <div class="panel-card"><div class="card-header-clean"><h3>Ditolak / Batal</h3></div><div class="card-body-clean"><div class="stat-number"><?= $total_rejected; ?></div><p class="mb-0 text-muted">Permintaan tidak dilanjutkan.</p></div></div>
            <div class="panel-card"><div class="card-header-clean"><h3>Kelola Data</h3></div><div class="card-body-clean action-row"><a href="<?= e(base_url('admin/items/index.php')); ?>" class="btn btn-primary">Barang</a><a href="<?= e(base_url('admin/categories/index.php')); ?>" class="btn btn-secondary">Kategori</a></div></div>
            <div class="panel-card"><div class="card-header-clean"><h3>Aktivitas</h3></div><div class="card-body-clean"><a href="<?= e(base_url('admin/logs/index.php')); ?>" class="btn btn-info"><i class="fas fa-clock-rotate-left"></i> Lihat Aktivitas</a></div></div>
        </div>
        <div class="table-wrapper">
            <div class="table-header"><h3>Permintaan Terbaru</h3><a href="<?= e(base_url('admin/borrow/index.php')); ?>" class="btn btn-primary btn-sm">Lihat Semua</a></div>
            <div class="table-body">
                <?php if ($recent_borrows && mysqli_num_rows($recent_borrows) > 0) : ?>
                    <table>
                        <thead><tr><th>Kode</th><th>Peminjam</th><th>Barang</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_borrows)) : ?>
                            <?php $status=$row['status']; $badge=['pending'=>'badge-warning','approved'=>'badge-success','returned'=>'badge-info','rejected'=>'badge-danger','cancelled'=>'badge-secondary'][$status] ?? 'badge-primary'; ?>
                            <tr>
                                <td><code><?= e($row['request_code']); ?></code></td>
                                <td><?= e($row['user_name']); ?></td>
                                <td><?= e($row['item_names'] ?: '-'); ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                                <td><span class="badge <?= $badge; ?>"><?= e(ucfirst($status)); ?></span></td>
                                <td><a href="<?= e(base_url('admin/borrow/index.php')); ?>" class="btn btn-primary btn-sm">Detail</a></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada permintaan peminjaman.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
