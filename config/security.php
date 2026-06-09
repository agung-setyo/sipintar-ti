<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('403 Forbidden');
}

include_once __DIR__ . '/security_headers.php';
send_security_headers(false);
?>
