<?php

include 'auth.php';

if ($_SESSION['role'] != 'peminjam') {

    http_response_code(403);

    die("403 Forbidden");
}
?>