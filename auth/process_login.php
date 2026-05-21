<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';

global $conn;

function login_page_for_role(?string $loginAs): string
{
    return $loginAs === 'admin' ? 'auth/admin_login.php' : 'auth/user_login.php';
}

function redirect_login_error(string $message, ?string $loginAs = null): void
{
    redirect_to(login_page_for_role($loginAs) . '?error=' . urlencode($message));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('auth/login.php');
}

$loginAs = $_POST['login_as'] ?? '';
if (!in_array($loginAs, ['admin', 'peminjam'], true)) {
    redirect_to('auth/login.php?error=' . urlencode('Pilih halaman login yang sesuai.'));
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    save_security_event($conn, 'CSRF_INVALID_LOGIN', 'high', null, 'Token CSRF login tidak valid');
    redirect_login_error('Sesi tidak valid. Silakan coba lagi.', $loginAs);
}

$ip = client_ip();
$attempt_key = 'login_attempts_' . $loginAs . '_' . hash('sha256', $ip);
$now = time();

if (!isset($_SESSION[$attempt_key])) {
    $_SESSION[$attempt_key] = ['count' => 0, 'first_attempt' => $now, 'locked_until' => 0];
}

if ($_SESSION[$attempt_key]['locked_until'] > $now) {
    save_security_event($conn, 'LOGIN_RATE_LIMITED', 'high', null, 'Login diblokir sementara karena terlalu banyak percobaan gagal');
    redirect_login_error('Terlalu banyak percobaan login. Coba lagi beberapa menit lagi.', $loginAs);
}

if (($now - $_SESSION[$attempt_key]['first_attempt']) > 300) {
    $_SESSION[$attempt_key] = ['count' => 0, 'first_attempt' => $now, 'locked_until' => 0];
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    redirect_login_error('Email atau password tidak valid.', $loginAs);
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT id, name, email, password, role, identity_type, is_active FROM users WHERE email = ? LIMIT 1'
);

if (!$stmt) {
    error_log('Login prepare failed: ' . mysqli_error($conn));
    redirect_login_error('Terjadi kesalahan. Silakan coba lagi.', $loginAs);
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$validPassword = $user && password_verify($password, $user['password']);
$activeUser = $user && (int)$user['is_active'] === 1;
$roleMatches = $user && $user['role'] === $loginAs;

if (!$validPassword || !$activeUser || !$roleMatches) {
    $_SESSION[$attempt_key]['count']++;

    save_log($conn, $user['id'] ?? null, 'LOGIN_FAILED', 'users', $user['id'] ?? null, 'Percobaan login gagal');

    if ($_SESSION[$attempt_key]['count'] >= 5) {
        $_SESSION[$attempt_key]['locked_until'] = $now + 300;
        save_security_event($conn, 'LOGIN_FAILED_LIMIT', 'high', $user['id'] ?? null, 'Percobaan login gagal >= 5 kali dalam 5 menit');
    }

    if ($validPassword && $activeUser && !$roleMatches) {
        $roleName = $loginAs === 'admin' ? 'admin' : 'peminjam';
        redirect_login_error('Akun ini bukan akun ' . $roleName . '.', $loginAs);
    }

    redirect_login_error('Email atau password salah.', $loginAs);
}

$old_attempt = $_SESSION[$attempt_key] ?? null;
$_SESSION = [];
if ($old_attempt !== null) {
    $_SESSION[$attempt_key] = $old_attempt;
}
session_regenerate_id(true);
unset($_SESSION[$attempt_key]);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['identity_type'] = $user['identity_type'];
$_SESSION['last_activity'] = time();

save_log($conn, (int)$user['id'], 'LOGIN_SUCCESS', 'users', (int)$user['id'], 'User berhasil login');

if ($user['role'] === 'admin') {
    redirect_to('admin/dashboard.php');
} else {
    redirect_to('peminjam/dashboard.php');
}
exit;
?>
