<?php
include_once __DIR__ . '/config/security.php';
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status' => 'ok', 'time' => date('c')]);
?>
