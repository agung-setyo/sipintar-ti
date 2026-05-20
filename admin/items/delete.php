<?php

include_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM items WHERE id='$id'"
);

header(
    "Location: index.php"
);
exit;
?>
