<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/security.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';
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
?>
