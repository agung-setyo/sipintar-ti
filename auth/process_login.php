<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';
<<<<<<< HEAD
include_once __DIR__ . '/../helpers/login_rate_limit_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$loginAs = $_POST['login_as'] ?? '';
$targetLoginPage = $loginAs === 'admin' ? 'auth/admin_login.php' : 'auth/user_login.php';

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    save_security_event($conn, 'LOGIN_CSRF_INVALID', 'medium', null, 'Token CSRF login tidak valid.');
    $_SESSION['flash'] = 'Sesi tidak valid. Silakan coba lagi.';
    redirect_to($targetLoginPage);
}

$email = login_rate_limit_email($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!in_array($loginAs, ['admin', 'peminjam'], true)) {
    $_SESSION['flash'] = 'Role tidak valid.';
    redirect_to('auth/login.php');
}

if ($email === '' || $password === '') {
    $_SESSION['flash'] = 'Email dan password wajib diisi.';
    redirect_to($targetLoginPage);
}

$rateStatus = login_rate_limit_status($conn, $email, $loginAs);
if ($rateStatus['locked']) {
    $waitText = format_retry_after((int)$rateStatus['retry_after']);
    save_security_event(
        $conn,
        'LOGIN_BLOCKED_RATE_LIMIT',
        'high',
        null,
        'Login diblokir sementara karena terlalu banyak percobaan gagal untuk role ' . $loginAs . '.'
    );
    $_SESSION['flash'] = 'Terlalu banyak percobaan login gagal. Silakan coba kembali dalam ' . $waitText . '.';
    redirect_to($targetLoginPage);
}

$stmt = $conn->prepare("SELECT id, name, email, password, role, identity_type, is_active FROM users WHERE email=? AND role=? LIMIT 1");
if (!$stmt) {
    error_log('Login query prepare failed: ' . $conn->error);
    $_SESSION['flash'] = 'Login sedang bermasalah. Periksa struktur database.';
    redirect_to($targetLoginPage);
}

$stmt->bind_param('ss', $email, $loginAs);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$loginValid = $user
    && password_verify($password, $user['password'])
    && (int)$user['is_active'] === 1;

if (!$loginValid) {
    $failure = register_login_failure($conn, $email, $loginAs);
    $severity = $failure['locked'] ? 'high' : 'medium';
    $userId = $user ? (int)$user['id'] : null;

    save_security_event(
        $conn,
        'LOGIN_FAILED',
        $severity,
        $userId,
        'Percobaan login gagal untuk role ' . $loginAs . '. Sisa percobaan: ' . (int)$failure['remaining_attempts'] . '.'
    );

    if ($failure['locked']) {
        $_SESSION['flash'] = 'Login gagal 3 kali. Akses login dikunci sementara selama 15 menit.';
    } else {
        $_SESSION['flash'] = 'Email atau password salah. Sisa percobaan: ' . (int)$failure['remaining_attempts'] . '.';
    }

    redirect_to($targetLoginPage);
}

reset_login_attempts($conn, $email, $loginAs);
session_regenerate_id(true);

if (!isset($_SESSION['logins']) || !is_array($_SESSION['logins'])) {
    $_SESSION['logins'] = [];
}

$_SESSION['logins'][$user['role']] = [
    'user_id' => (int)$user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'identity_type' => $user['identity_type'] ?? '',
];

$_SESSION['current_role'] = $user['role'];
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['identity_type'] = $user['identity_type'] ?? '';
$_SESSION['last_activity'] = time();

save_log(
    $conn,
    (int)$user['id'],
    'LOGIN',
    'users',
    (int)$user['id'],
    'User login sebagai ' . $user['role']
);

if ($user['role'] === 'admin') {
    redirect_to('admin/dashboard.php');
}

redirect_to('peminjam/dashboard.php');
=======

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
    redirect_login_error('Token sesi (CSRF) tidak valid. Muat ulang halaman dan coba lagi.', $loginAs);
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

// Backup login attempts
$old_attempt = $_SESSION[$attempt_key] ?? null;
$csrf_token = $_SESSION['csrf_token'] ?? null;

// Initialize multi-login structure if not exists
if (!isset($_SESSION['logins'])) {
    $_SESSION['logins'] = [];
}

// Remove old attempts but preserve login data
$preserved_logins = $_SESSION['logins'];
$_SESSION = [];
$_SESSION['logins'] = $preserved_logins;
if ($old_attempt !== null) {
    $_SESSION[$attempt_key] = $old_attempt;
}
if ($csrf_token !== null) {
    $_SESSION['csrf_token'] = $csrf_token;
}

session_regenerate_id(true);
unset($_SESSION[$attempt_key]);

// Store login data for this role
$_SESSION['logins'][$loginAs] = [
    'user_id' => (int)$user['id'],
    'name' => $user['name'],
    'identity_type' => $user['identity_type']
];

// Set current active role
$_SESSION['current_role'] = $loginAs;
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $loginAs;
$_SESSION['identity_type'] = $user['identity_type'];
$_SESSION['last_activity'] = time();

save_log($conn, (int)$user['id'], 'LOGIN_SUCCESS', 'users', (int)$user['id'], 'User berhasil login');

if ($user['role'] === 'admin') {
    redirect_to('admin/dashboard.php');
} else {
    redirect_to('peminjam/dashboard.php');
}
exit;
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
?>
