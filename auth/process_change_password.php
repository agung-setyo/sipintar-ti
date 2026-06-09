<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';
include_once __DIR__ . '/../helpers/validation_helper.php';

global $conn;

function change_password_redirect(string $message, bool $success = false): void
{
    $key = $success ? 'success' : 'error';
    redirect_to('auth/change_password.php?' . $key . '=' . urlencode($message));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('auth/change_password.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    save_security_event($conn, 'CSRF_INVALID_CHANGE_PASSWORD', 'high', $_SESSION['user_id'] ?? null, 'Token CSRF ubah password tidak valid');
    change_password_redirect('Token sesi tidak valid. Muat ulang halaman dan coba lagi.');
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($userId < 1 || $current === '' || $new === '' || $confirm === '') {
    change_password_redirect('Semua field wajib diisi.');
}

if ($new !== $confirm) {
    change_password_redirect('Konfirmasi password baru tidak sama.');
}

if (!validate_password($new)) {
    change_password_redirect('Password baru minimal 8 karakter.');
}

if (hash_equals($current, $new)) {
    change_password_redirect('Password baru tidak boleh sama dengan password lama.');
}

$stmt = mysqli_prepare($conn, 'SELECT id, password FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($current, $user['password'])) {
    save_security_event($conn, 'CHANGE_PASSWORD_FAILED', 'medium', $userId, 'Password lama salah saat ubah password');
    change_password_redirect('Password saat ini salah.');
}

$newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
$update = mysqli_prepare($conn, 'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
mysqli_stmt_bind_param($update, 'si', $newHash, $userId);
mysqli_stmt_execute($update);

save_log($conn, $userId, 'CHANGE_PASSWORD', 'users', $userId, 'User mengubah password sendiri');
change_password_redirect('Password berhasil diperbarui.', true);
?>
