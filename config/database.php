<?php
/**
 * Database connection.
 *
 * Credentials are read from environment variables first so secrets do not need
 * to be hardcoded in source code. Local defaults are kept for XAMPP/testing.
 */
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'sipintar_ti';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn && mysqli_connect_errno() === 1049) {
    $conn = mysqli_connect($host, $user, $pass);
    if ($conn) {
        $safe_db = mysqli_real_escape_string($conn, $db);
        mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `{$safe_db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        mysqli_select_db($conn, $db);
    }
}

if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

mysqli_set_charset($conn, 'utf8mb4');
?>
