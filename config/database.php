<?php
/**
 * Database connection.
 *
<<<<<<< HEAD
 * Untuk Hostinger, isi kredensial di environment variable atau ubah default di bawah.
 * File SQL import tidak lagi menggunakan CREATE DATABASE agar cocok dengan shared hosting.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('403 Forbidden');
}

=======
 * Credentials are read from environment variables first so secrets do not need
 * to be hardcoded in source code. Local defaults are kept for XAMPP/testing.
 */
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'sipintar_ti';

$conn = mysqli_connect($host, $user, $pass, $db);

<<<<<<< HEAD
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Koneksi database gagal. Periksa nama database, username, password, dan host database.');
=======
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
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
}

mysqli_set_charset($conn, 'utf8mb4');
?>
