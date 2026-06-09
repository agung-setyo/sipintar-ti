<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/peminjam.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';

global $conn;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('peminjam/history.php');
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) redirect_to('peminjam/history.php?error=' . urlencode('Sesi tidak valid. Silakan coba lagi.'));
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE borrow_requests SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
if ($stmt->affected_rows !== 1) redirect_to('peminjam/history.php?error=' . urlencode('Pengajuan tidak ditemukan atau sudah diproses.'));
save_log($conn, $user_id, 'CANCEL', 'borrow_requests', $id, 'Peminjam membatalkan pengajuan');
redirect_to('peminjam/history.php?success=' . urlencode('Pengajuan berhasil dibatalkan.'));
