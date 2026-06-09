<?php
/**
 * Database connection.
 *
 * Untuk Hostinger, isi kredensial di environment variable atau ubah default di bawah.
 * File SQL import tidak lagi menggunakan CREATE DATABASE agar cocok dengan shared hosting.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('403 Forbidden');
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'sipintar_ti';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Koneksi database gagal. Periksa nama database, username, password, dan host database.');
}

mysqli_set_charset($conn, 'utf8mb4');
?>
