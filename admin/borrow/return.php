<?php

include_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'];

mysqli_query(

    $conn,

    "UPDATE borrow_requests

    SET status='returned'

    WHERE id='$id'"
);

header(
    "Location: index.php"
);
?>