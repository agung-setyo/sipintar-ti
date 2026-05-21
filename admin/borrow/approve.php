<?php
include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/app.php';
include_once __DIR__ . '/../../config/security.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../middleware/auth.php';
include_once __DIR__ . '/../../middleware/admin.php';
include_once __DIR__ . '/../../helpers/csrf_helper.php';
include_once __DIR__ . '/../../helpers/log_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('admin/borrow/index.php');
}
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_to('admin/borrow/index.php?error=' . urlencode('Sesi tidak valid. Silakan coba lagi.'));
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$admin_id = (int)($_SESSION['user_id'] ?? 0);

$conn->begin_transaction();
try {
    $req = $conn->prepare("SELECT id FROM borrow_requests WHERE id = ? AND status = 'pending' FOR UPDATE");
    $req->bind_param('i', $id);
    $req->execute();
    if ($req->get_result()->num_rows === 0) {
        throw new Exception('Permintaan tidak ditemukan atau sudah diproses.');
    }

    $stmt = $conn->prepare('SELECT item_id, quantity FROM borrow_details WHERE borrow_request_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        throw new Exception('Detail barang tidak ditemukan.');
    }

    while ($row = $res->fetch_assoc()) {
        $item_id = (int)$row['item_id'];
        $qty = (int)$row['quantity'];
        $check = $conn->prepare("SELECT name, stock, status FROM items WHERE id = ? FOR UPDATE");
        $check->bind_param('i', $item_id);
        $check->execute();
        $item = $check->get_result()->fetch_assoc();
        if (!$item || $item['status'] !== 'available') {
            throw new Exception('Barang tidak tersedia.');
        }
        if ((int)$item['stock'] < $qty) {
            throw new Exception('Stok tidak cukup untuk ' . $item['name'] . '. Stok tersedia: ' . (int)$item['stock']);
        }
        $upd = $conn->prepare("UPDATE items SET stock = stock - ?, status = CASE WHEN stock - ? <= 0 THEN 'unavailable' ELSE status END WHERE id = ?");
        $upd->bind_param('iii', $qty, $qty, $item_id);
        if (!$upd->execute()) throw new Exception('Gagal mengurangi stok.');
    }

    $update = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ? AND status = 'pending'");
    $update->bind_param('ii', $admin_id, $id);
    if (!$update->execute() || $update->affected_rows !== 1) {
        throw new Exception('Gagal memproses permintaan.');
    }
    save_log($conn, $admin_id, 'APPROVE', 'borrow_requests', $id, 'Permintaan peminjaman disetujui');
    $conn->commit();
    redirect_to('admin/borrow/index.php?success=' . urlencode('Permintaan berhasil disetujui.'));
} catch (Exception $e) {
    $conn->rollback();
    redirect_to('admin/borrow/index.php?error=' . urlencode($e->getMessage()));
}
