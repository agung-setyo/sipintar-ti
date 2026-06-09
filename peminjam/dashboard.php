<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/peminjam.php';

global $conn;
$user_id = (int)$_SESSION['user_id'];
function user_count(mysqli $conn, int $user_id, string $statusSql): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM borrow_requests WHERE user_id = ? $statusSql");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['total'];
}
$total_all = user_count($conn, $user_id, '');
$total_pending = user_count($conn, $user_id, "AND status = 'pending'");
$total_approved = user_count($conn, $user_id, "AND status = 'approved'");
$total_returned = user_count($conn, $user_id, "AND status = 'returned'");
$available = mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE status='available' AND stock > 0");
$total_available = $available ? (int)mysqli_fetch_assoc($available)['total'] : 0;
$recent = $conn->prepare("SELECT br.id, br.request_code, br.status, br.borrow_date, br.return_date, br.created_at, GROUP_CONCAT(CONCAT(i.name, ' x', bd.quantity) SEPARATOR ', ') AS item_names FROM borrow_requests br LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id LEFT JOIN items i ON bd.item_id = i.id WHERE br.user_id = ? GROUP BY br.id ORDER BY br.created_at DESC LIMIT 6");
$recent->bind_param('i', $user_id);
$recent->execute();
$recent_result = $recent->get_result();
$pageTitle = 'Dashboard Peminjam';
$statusLabel = ['pending'=>'Menunggu','approved'=>'Disetujui','returned'=>'Dikembalikan','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
$statusBadge = ['pending'=>'badge-warning','approved'=>'badge-success','returned'=>'badge-info','rejected'=>'badge-danger','cancelled'=>'badge-secondary'];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div class="header-row">
                <div><h1>Halo, <?= e($_SESSION['name'] ?? 'Peminjam'); ?></h1><p>Pantau peminjaman dan ajukan barang yang tersedia.</p></div>
                <div class="action-row"><a href="<?= e(base_url('peminjam/borrow.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Pinjaman</a><a href="<?= e(base_url('peminjam/items.php')); ?>" class="btn btn-secondary"><i class="fas fa-boxes-stacked"></i> Lihat Barang</a></div>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card info"><div class="stat-card-body"><div class="stat-content"><h5>Total Pengajuan</h5><div class="stat-number"><?= $total_all; ?></div><div class="stat-change">Semua riwayat peminjaman</div></div><div class="stat-icon"><i class="fas fa-clipboard-list"></i></div></div></div>
            <div class="stat-card warning"><div class="stat-card-body"><div class="stat-content"><h5>Menunggu</h5><div class="stat-number"><?= $total_pending; ?></div><div class="stat-change">Sedang ditinjau admin</div></div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div></div>
            <div class="stat-card success"><div class="stat-card-body"><div class="stat-content"><h5>Disetujui</h5><div class="stat-number"><?= $total_approved; ?></div><div class="stat-change">Barang sedang dipinjam</div></div><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div></div>
            <div class="stat-card"><div class="stat-card-body"><div class="stat-content"><h5>Barang Tersedia</h5><div class="stat-number"><?= $total_available; ?></div><div class="stat-change">Siap diajukan</div></div><div class="stat-icon"><i class="fas fa-box-open"></i></div></div></div>
        </div>
        <div class="table-wrapper">
            <div class="table-header"><h3>Riwayat Terbaru</h3><a href="<?= e(base_url('peminjam/history.php')); ?>" class="btn btn-primary btn-sm">Lihat Semua</a></div>
            <div class="table-body">
                <?php if ($recent_result->num_rows > 0): ?>
                    <table>
                        <thead><tr><th>Kode</th><th>Barang</th><th>Jadwal</th><th>Status</th><th>Tanggal Pengajuan</th></tr></thead>
                        <tbody>
                            <?php while ($row = $recent_result->fetch_assoc()): ?>
                                <tr><td><code><?= e($row['request_code']); ?></code></td><td><?= e($row['item_names'] ?: '-'); ?></td><td><?= e(date('d/m/Y', strtotime($row['borrow_date']))); ?> - <?= e(date('d/m/Y', strtotime($row['return_date']))); ?></td><td><span class="badge <?= e($statusBadge[$row['status']] ?? 'badge-primary'); ?>"><?= e($statusLabel[$row['status']] ?? ucfirst($row['status'])); ?></span></td><td><?= e(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada peminjaman. Mulai ajukan barang dari katalog.</p><a href="<?= e(base_url('peminjam/borrow.php')); ?>" class="btn btn-primary">Ajukan Sekarang</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
