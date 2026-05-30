<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/peminjam.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';

global $conn;
$user_id = (int)$_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$allowed_statuses = ['pending','approved','returned','rejected','cancelled'];
$sql = "SELECT br.id, br.request_code, br.borrow_date, br.return_date, br.purpose, br.status, br.created_at, br.approved_at, br.returned_at,
               GROUP_CONCAT(CONCAT(i.name, ' x', bd.quantity) SEPARATOR ', ') AS item_names
        FROM borrow_requests br
        LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id
        LEFT JOIN items i ON bd.item_id = i.id
        WHERE br.user_id = ?";
$types = 'i';
$params = [$user_id];
if (in_array($status_filter, $allowed_statuses, true)) {
    $sql .= ' AND br.status = ?';
    $types .= 's';
    $params[] = $status_filter;
}
if ($search !== '') {
    $sql .= ' AND (br.request_code LIKE ? OR br.purpose LIKE ? OR i.name LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' GROUP BY br.id ORDER BY br.created_at DESC';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$history = $stmt->get_result();
$pageTitle = 'Riwayat Peminjaman';
$statusLabel = ['pending'=>'Menunggu','approved'=>'Disetujui','returned'=>'Dikembalikan','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
$statusBadge = ['pending'=>'badge-warning','approved'=>'badge-success','returned'=>'badge-info','rejected'=>'badge-danger','cancelled'=>'badge-secondary'];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Riwayat Peminjaman</h1><p>Lihat status dan detail seluruh pengajuan peminjaman Anda.</p></div><a href="<?= e(base_url('peminjam/borrow.php')); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Baru</a></div></div>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?= e($_GET['success']); ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= e($_GET['error']); ?></div><?php endif; ?>
        <div class="filter-card panel-card">
            <form method="GET" class="action-row">
                <input type="text" name="search" value="<?= e($search); ?>" class="form-control max-w-320" placeholder="Cari kode, barang, atau keperluan">
                <select name="status" class="form-select max-w-240">
                    <option value="">Semua Status</option>
                    <?php foreach ($statusLabel as $key => $label): ?>
                        <option value="<?= e($key); ?>" <?= $status_filter === $key ? 'selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                <a href="<?= e(base_url('peminjam/history.php')); ?>" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="table-wrapper">
            <div class="table-header"><h3>Daftar Riwayat</h3></div>
            <div class="table-body">
                <?php if ($history->num_rows > 0): ?>
                    <table>
                        <thead><tr><th>Kode</th><th>Barang</th><th>Jadwal</th><th>Keperluan</th><th>Status</th><th>Diajukan</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php while ($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?= e($row['request_code']); ?></code></td>
                                    <td><?= e($row['item_names'] ?: '-'); ?></td>
                                    <td><?= e(date('d/m/Y', strtotime($row['borrow_date']))); ?> - <?= e(date('d/m/Y', strtotime($row['return_date']))); ?></td>
                                    <td><?= e($row['purpose']); ?></td>
                                    <td><span class="badge <?= e($statusBadge[$row['status']] ?? 'badge-primary'); ?>"><?= e($statusLabel[$row['status']] ?? ucfirst($row['status'])); ?></span></td>
                                    <td><?= e(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form action="<?= e(base_url('peminjam/cancel.php')); ?>" method="POST" data-confirm="Batalkan pengajuan ini?">
                                                <?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Batalkan</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Tidak ada aksi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i><p>Riwayat peminjaman belum ada.</p><a href="<?= e(base_url('peminjam/borrow.php')); ?>" class="btn btn-primary">Ajukan Pinjaman</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
