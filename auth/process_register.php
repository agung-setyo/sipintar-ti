<?php

include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/security.php';

include_once __DIR__ . '/../helpers/csrf_helper.php';
include_once __DIR__ . '/../helpers/log_helper.php';

global $conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('auth/register.php');
}
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    save_security_event($conn, 'CSRF_INVALID_REGISTER', 'high', null, 'Token CSRF register tidak valid');
    redirect_to('auth/register.php?error=' . urlencode('Token sesi (CSRF) tidak valid. Muat ulang halaman dan coba lagi.'));
}

include_once __DIR__ . '/../helpers/validation_helper.php';

$post = sanitize_post();

$name = $post['name'] ?? '';
$email = strtolower($post['email'] ?? '');
$password = $_POST['password'] ?? '';

$identity_type = $post['identity_type'] ?? '';
$identity_number = $post['identity_number'] ?? '';

if (
    empty($name) ||
    empty($email) ||
    empty($password) ||
    empty($identity_type) ||
    empty($identity_number)
) {

    redirect_to('auth/register.php?error=' . urlencode('Semua field wajib diisi.'));
}

if (!validate_email($email)) {
    redirect_to('auth/register.php?error=' . urlencode('Format email tidak valid.'));
}

if (!validate_password($password)) {
    redirect_to('auth/register.php?error=' . urlencode('Password minimal 8 karakter.'));
}

$allowed_identity_types = ['dosen', 'mahasiswa'];

if (!in_array($identity_type, $allowed_identity_types, true)) {

    redirect_to('auth/register.php?error=' . urlencode('Jenis identitas tidak valid.'));
}

$check = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE email=?"
);

mysqli_stmt_bind_param($check, "s", $email);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {

    redirect_to('auth/register.php?error=' . urlencode('Email sudah digunakan.'));
}

$password_hash = password_hash(
    $password,
    PASSWORD_BCRYPT
);

$role = "peminjam";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users
    (
        name,
        email,
        password,
        role,
        identity_type,
        identity_number
    )
    VALUES (?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $name,
    $email,
    $password_hash,
    $role,
    $identity_type,
    $identity_number
);

if (mysqli_stmt_execute($stmt)) {
    save_log(
        $conn,
        null,
        "REGISTER",
        "users",
        null,
        "User baru berhasil register"
    );

    redirect_to('auth/user_login.php?success=1');
    exit;
} else {
    redirect_to('auth/register.php?error=' . urlencode('Register gagal. Silakan coba lagi.'));
}
?>