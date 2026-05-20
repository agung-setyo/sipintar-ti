<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sipintar_ti";

// Check if MySQLi extension is loaded
if (!extension_loaded('mysqli')) {
    die("Fatal Error: MySQLi extension is not enabled. Please enable it in your php.ini file.");
}

$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    if (mysqli_connect_errno() === 1049) {
        $conn = mysqli_connect($host, $user, $pass);
        if ($conn) {
            mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            mysqli_select_db($conn, $db);
        }
    }
}

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>