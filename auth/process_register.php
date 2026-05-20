<?php

include '../config/session.php';
include_once __DIR__ . '/../config/database.php';
include '../config/security.php';

include '../helpers/csrf_helper.php';
include '../helpers/log_helper.php';

global $conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("CSRF Token tidak valid");
}

$name = htmlspecialchars(trim($_POST['name'] ?? ''));
$email = strtolower(htmlspecialchars(trim($_POST['email'] ?? '')));
$password = $_POST['password'] ?? '';

$identity_type = htmlspecialchars(trim($_POST['identity_type'] ?? ''));
$identity_number = htmlspecialchars(trim($_POST['identity_number'] ?? ''));

if (
    empty($name) ||
    empty($email) ||
    empty($password) ||
    empty($identity_type) ||
    empty($identity_number)
) {

    die("Semua field wajib diisi");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die("Format email tidak valid");
}

if (strlen($password) < 8) {

    die("Password minimal 8 karakter");
}

$allowed_identity_types = ['dosen', 'mahasiswa'];

if (!in_array($identity_type, $allowed_identity_types, true)) {

    die("Jenis identitas tidak valid");
}

$check = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE email=?"
);

mysqli_stmt_bind_param($check, "s", $email);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {

    die("Email sudah digunakan");
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

    header("Location: login.php?success=1");
    exit;
} else {
    die("Register gagal");
}
?>