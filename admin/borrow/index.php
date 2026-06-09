<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','approved','returned','rejected','cancelled'];
$sql = "SELECT br.id, br.request_code, br.borrow_date, br.return_date, br.purpose, br.status, br.created_at,
               u.name AS user_name, u.identity_number,
               GROUP_CONCAT(CONCAT(i.name, ' x', bd.quantity) SEPARATOR ', ') AS item_names
        FROM borrow_requests br
        JOIN users u ON br.user_id = u.id
        LEFT JOIN borrow_details bd ON br.id = bd.borrow_request_id
        LEFT JOIN items i ON bd.item_id = i.id";
$params = [];
if (in_array($status_filter, $allowed, true)) {
    $sql .= ' WHERE br.status = ?';
    $params[] = $status_filter;
}
$sql .= ' GROUP BY br.id ORDER BY br.created_at DESC';
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param('s', $params[0]);
$stmt->execute();
$query = $stmt->get_result();
$pageTitle = 'Permintaan Peminjaman';
$statusLabel = ['pending'=>'Menunggu','approved'=>'Disetujui','returned'=>'Dikembalikan','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
$statusBadge = ['pending'=>'badge-warning','approved'=>'badge-success','returned'=>'badge-info','rejected'=>'badge-danger','cancelled'=>'badge-secondary'];
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div class="header-row">
                <div><h1>Permintaan Peminjaman</h1><p>Tinjau, setujui, tolak, dan selesaikan proses peminjaman barang.</p></div>
                <a href="<?= e(base_url('admin/dashboard.php')); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
            </div>
        </div>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?= e($_GET['success']); ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= e($_GET['error']); ?></div><?php endif; ?>
        <div class="filter-card panel-card">
            <form method="GET" class="action-row">
                <select name="status" class="form-select max-w-240">
                    <option value="">Semua Status</option>
                    <?php foreach ($statusLabel as $key => $label): ?>
                        <option value="<?= e($key); ?>" <?= $status_filter === $key ? 'selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                <a href="<?= e(base_url('admin/borrow/index.php')); ?>" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="table-wrapper">
            <div class="table-header"><h3>Daftar Permintaan</h3></div>
            <div class="table-body">
                <?php if ($query && $query->num_rows > 0) : ?>
                    <table>
                        <thead><tr><th>Kode</th><th>Peminjam</th><th>Barang</th><th>Jadwal</th><th>Keperluan</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php while ($row = $query->fetch_assoc()) : ?>
                                <?php $status=$row['status']; ?>
                                <tr>
                                    <td><code><?= e($row['request_code']); ?></code></td>
                                    <td><strong><?= e($row['user_name']); ?></strong><br><small><?= e($row['identity_number']); ?></small></td>
                                    <td><?= e($row['item_names'] ?: '-'); ?></td>
                                    <td><?= e(date('d/m/Y', strtotime($row['borrow_date']))); ?> - <?= e(date('d/m/Y', strtotime($row['return_date']))); ?></td>
                                    <td><?= e($row['purpose']); ?></td>
                                    <td><span class="badge <?= e($statusBadge[$status] ?? 'badge-primary'); ?>"><?= e($statusLabel[$status] ?? ucfirst($status)); ?></span></td>
                                    <td>
                                        <div class="action-row">
                                        <?php if ($status === 'pending') : ?>
                                            <form action="<?= e(base_url('admin/borrow/approve.php')); ?>" method="POST" data-confirm="Setujui permintaan ini?"><?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button class="btn btn-success btn-sm" type="submit"><i class="fas fa-check"></i> Setujui</button></form>
                                            <form action="<?= e(base_url('admin/borrow/reject.php')); ?>" method="POST" data-confirm="Tolak permintaan ini?"><?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-xmark"></i> Tolak</button></form>
                                        <?php elseif ($status === 'approved') : ?>
                                            <form action="<?= e(base_url('admin/borrow/return.php')); ?>" method="POST" data-confirm="Tandai barang sudah dikembalikan?"><?= csrf_input_field(); ?><input type="hidden" name="id" value="<?= (int)$row['id']; ?>"><button class="btn btn-info btn-sm" type="submit"><i class="fas fa-rotate-left"></i> Selesai</button></form>
                                        <?php else : ?>
                                            <span class="badge badge-secondary">Selesai</span>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada permintaan peminjaman.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
