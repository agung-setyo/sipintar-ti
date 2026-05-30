<?php

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

header(
    "Content-Security-Policy: "
    . "default-src 'self'; "
    . "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; "
    . "style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
    . "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
    . "img-src 'self' data:; "
    . "connect-src 'self'; "
    . "frame-ancestors 'none'; "
    . "form-action 'self';"
);

header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

?>
