<?php

include '../config/session.php';
include_once __DIR__ . '/../config/database.php';
include '../helpers/log_helper.php';

global $conn;

if (isset($_SESSION['user_id'])) {
    save_log(
        $conn,
        $_SESSION['user_id'],
        "LOGOUT",
        "users",
        $_SESSION['user_id'],
        "User logout"
    );
}

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>