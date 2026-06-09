<?php
<<<<<<< HEAD
include_once __DIR__ . '/config/security.php';
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status' => 'ok', 'time' => date('c')]);
?>
=======
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'time' => time()]);
exit;
?>
>>>>>>> 4362cd300695d6297af8d50b612426b7fde8766d
