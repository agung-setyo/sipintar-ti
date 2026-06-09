<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
include_once __DIR__ . '/../../helpers/log_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('admin/borrow/index.php');
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) redirect_to('admin/borrow/index.php?error=' . urlencode('Sesi tidak valid. Silakan coba lagi.'));
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$admin_id = (int)($_SESSION['user_id'] ?? 0);

$conn->begin_transaction();
try {
    $req = $conn->prepare("SELECT id FROM borrow_requests WHERE id = ? AND status = 'approved' FOR UPDATE");
    $req->bind_param('i', $id);
    $req->execute();
    if ($req->get_result()->num_rows === 0) {
        throw new Exception('Permintaan tidak ditemukan atau tidak sedang dipinjam.');
    }
    $stmt = $conn->prepare('SELECT item_id, quantity FROM borrow_details WHERE borrow_request_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $item_id = (int)$row['item_id'];
        $qty = (int)$row['quantity'];
        $upd = $conn->prepare("UPDATE items SET stock = stock + ?, status = 'available' WHERE id = ?");
        $upd->bind_param('ii', $qty, $item_id);
        if (!$upd->execute()) throw new Exception('Gagal mengembalikan stok.');
    }
    $update = $conn->prepare("UPDATE borrow_requests SET status = 'returned', returned_at = NOW(), updated_at = NOW() WHERE id = ? AND status = 'approved'");
    $update->bind_param('i', $id);
    if (!$update->execute() || $update->affected_rows !== 1) throw new Exception('Gagal menyelesaikan peminjaman.');
    save_log($conn, $admin_id, 'RETURN', 'borrow_requests', $id, 'Barang peminjaman dikembalikan');
    $conn->commit();
    redirect_to('admin/borrow/index.php?success=' . urlencode('Peminjaman berhasil diselesaikan.'));
} catch (Exception $e) {
    $conn->rollback();
    redirect_to('admin/borrow/index.php?error=' . urlencode($e->getMessage()));
}
