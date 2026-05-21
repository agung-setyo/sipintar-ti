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
$update = $conn->prepare("UPDATE borrow_requests SET status = 'rejected', updated_at = NOW() WHERE id = ? AND status = 'pending'");
$update->bind_param('i', $id);
$update->execute();
if ($update->affected_rows !== 1) redirect_to('admin/borrow/index.php?error=' . urlencode('Permintaan tidak ditemukan atau sudah diproses.'));
save_log($conn, $admin_id, 'REJECT', 'borrow_requests', $id, 'Permintaan peminjaman ditolak');
redirect_to('admin/borrow/index.php?success=' . urlencode('Permintaan berhasil ditolak.'));
