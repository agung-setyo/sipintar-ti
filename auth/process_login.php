<?php

session_start();

include_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$query = "SELECT * FROM users WHERE email='$email' LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

if ($user) {

    // CEK PASSWORD
    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['identity_type'] = $user['identity_type'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../peminjam/dashboard.php");
        }
        exit;

    } else {

        echo "PASSWORD SALAH";
    }

} else {

    echo "EMAIL TIDAK DITEMUKAN";
}
?>