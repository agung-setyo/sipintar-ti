<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/peminjam.php';

global $conn;
$user_id = (int)$_SESSION['user_id'];
$error = '';
$success = '';
$prefill_item = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid. Silakan coba lagi.';
    } else {
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $borrow_date = trim($_POST['borrow_date'] ?? '');
        $return_date = trim($_POST['return_date'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $errors = [];
        if ($item_id < 1) $errors[] = 'Barang wajib dipilih.';
        if ($quantity < 1) $errors[] = 'Jumlah minimal 1.';
        if ($borrow_date === '') $errors[] = 'Tanggal pinjam wajib diisi.';
        if ($return_date === '') $errors[] = 'Tanggal kembali wajib diisi.';
        if (strlen($purpose) < 8) $errors[] = 'Keperluan minimal 8 karakter.';
        if ($borrow_date && $return_date) {
            $today = strtotime(date('Y-m-d'));
            $start = strtotime($borrow_date);
            $end = strtotime($return_date);
            if ($start === false || $end === false) $errors[] = 'Format tanggal tidak valid.';
            elseif ($start < $today) $errors[] = 'Tanggal pinjam tidak boleh sebelum hari ini.';
            elseif ($end < $start) $errors[] = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
            elseif ((($end - $start) / 86400) > 14) $errors[] = 'Durasi peminjaman maksimal 14 hari.';
        }
        if (!$errors) {
            $conn->begin_transaction();
            try {
                $check = $conn->prepare("SELECT id, name, stock, status FROM items WHERE id = ? FOR UPDATE");
                $check->bind_param('i', $item_id);
                $check->execute();
                $item = $check->get_result()->fetch_assoc();
                if (!$item || $item['status'] !== 'available' || (int)$item['stock'] <= 0) {
                    throw new Exception('Barang tidak tersedia.');
                }
                if ((int)$item['stock'] < $quantity) {
                    throw new Exception('Stok tidak cukup. Stok tersedia: ' . (int)$item['stock']);
                }
                $request_code = 'BRW-' . date('Ymd-His') . '-' . random_int(100, 999);
                $status = 'pending';
                $insert = $conn->prepare("INSERT INTO borrow_requests (user_id, request_code, borrow_date, return_date, purpose, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $insert->bind_param('isssss', $user_id, $request_code, $borrow_date, $return_date, $purpose, $status);
                if (!$insert->execute()) throw new Exception('Gagal membuat permintaan.');
                $borrow_id = $insert->insert_id;
                $detail = $conn->prepare("INSERT INTO borrow_details (borrow_request_id, item_id, quantity) VALUES (?, ?, ?)");
                $detail->bind_param('iii', $borrow_id, $item_id, $quantity);
                if (!$detail->execute()) throw new Exception('Gagal menyimpan detail barang.');
                save_log($conn, $user_id, 'BORROW_REQUEST', 'borrow_requests', $borrow_id, 'Peminjam mengajukan peminjaman');
                $conn->commit();
                redirect_to('peminjam/history.php?success=' . urlencode('Pengajuan berhasil dibuat. Kode: ' . $request_code));
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        } else {
            $error = implode(' ', $errors);
        }
    }
}
$items = mysqli_query($conn, "SELECT i.id, i.name, i.item_code, i.stock, c.name AS category_name FROM items i JOIN categories c ON i.category_id = c.id WHERE i.status='available' AND i.stock > 0 ORDER BY c.name ASC, i.name ASC");
$active = $conn->prepare("SELECT COUNT(*) AS total FROM borrow_requests WHERE user_id = ? AND status IN ('pending','approved')");
$active->bind_param('i', $user_id);
$active->execute();
$active_count = (int)$active->get_result()->fetch_assoc()['total'];
$pageTitle = 'Ajukan Pinjaman';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="admin-wrapper">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div class="header-row"><div><h1>Ajukan Pinjaman</h1><p>Pilih barang, tentukan jadwal, dan jelaskan keperluan peminjaman.</p></div><a href="<?= e(base_url('peminjam/items.php')); ?>" class="btn btn-secondary"><i class="fas fa-boxes-stacked"></i> Katalog Barang</a></div></div>
        <div class="stats-grid">
            <div class="stat-card warning"><div class="stat-card-body"><div class="stat-content"><h5>Pengajuan Aktif</h5><div class="stat-number"><?= $active_count; ?></div><div class="stat-change">Menunggu atau sedang dipinjam</div></div><div class="stat-icon"><i class="fas fa-clipboard-list"></i></div></div></div>
            <div class="stat-card info"><div class="stat-card-body"><div class="stat-content"><h5>Durasi Maksimal</h5><div class="stat-number">14</div><div class="stat-change">Hari peminjaman</div></div><div class="stat-icon"><i class="fas fa-calendar-days"></i></div></div></div>
            <div class="stat-card success"><div class="stat-card-body"><div class="stat-content"><h5>Status Awal</h5><div class="stat-number fs-4">Menunggu</div><div class="stat-change">Diproses oleh admin</div></div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div></div>
            <div class="stat-card"><div class="stat-card-body"><div class="stat-content"><h5>Riwayat</h5><div class="stat-number fs-4">Tersimpan</div><div class="stat-change">Pantau dari menu riwayat</div></div><div class="stat-icon"><i class="fas fa-clock-rotate-left"></i></div></div></div>
        </div>
        <div class="form-card max-w-900">
            <div class="card-header-clean"><h3>Form Pengajuan</h3></div>
            <div class="card-body-clean">
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
                <form method="POST">
                    <?= csrf_input_field(); ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="item_id"><i class="fas fa-box"></i> Barang</label>
                            <select id="item_id" name="item_id" class="form-select" required>
                                <option value="">Pilih barang</option>
                                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                    <option value="<?= (int)$item['id']; ?>" <?= $prefill_item === (int)$item['id'] ? 'selected' : ''; ?>><?= e($item['category_name'] . ' - ' . $item['name'] . ' | Stok: ' . $item['stock']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group"><label for="quantity"><i class="fas fa-hashtag"></i> Jumlah</label><input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required></div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label for="borrow_date"><i class="fas fa-calendar-plus"></i> Tanggal Pinjam</label><input type="date" id="borrow_date" name="borrow_date" class="form-control" min="<?= e(date('Y-m-d')); ?>" required></div>
                        <div class="form-group"><label for="return_date"><i class="fas fa-calendar-check"></i> Tanggal Kembali</label><input type="date" id="return_date" name="return_date" class="form-control" min="<?= e(date('Y-m-d')); ?>" required></div>
                    </div>
                    <div class="form-group"><label for="purpose"><i class="fas fa-align-left"></i> Keperluan</label><textarea id="purpose" name="purpose" class="form-control" rows="4" placeholder="Contoh: Kegiatan seminar, praktikum, dokumentasi acara, dan lainnya." required></textarea></div>
                    <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Pengajuan</button><a href="<?= e(base_url('peminjam/dashboard.php')); ?>" class="btn btn-secondary">Batal</a></div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
