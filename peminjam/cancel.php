<?php

include_once __DIR__ . '/../config/database.php';
include '../config/session.php';

global $conn;

// Validasi session
if (!isset($_SESSION['user_id'])) {
    die("Anda harus login terlebih dahulu");
}

// Validasi input
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID tidak valid'); window.location='history.php';</script>";
    exit;
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verifikasi bahwa borrow request milik user yang login
$verify = $conn->prepare("SELECT id FROM borrow_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
$verify->bind_param("ii", $id, $user_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Borrow request tidak ditemukan atau sudah dibatalkan'); window.location='history.php';</script>";
    exit;
}

// Update dengan prepared statement
$stmt = $conn->prepare("UPDATE borrow_requests SET status = 'cancelled' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);

if ($stmt->execute()) {
    echo "<script>alert('Borrow dibatalkan'); window.location='history.php';</script>";
} else {
    echo "<script>alert('Gagal membatalkan: " . $stmt->error . "'); window.location='history.php';</script>";
}
?>