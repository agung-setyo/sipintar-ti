<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    redirect_to('auth/login.php');
}
?>
