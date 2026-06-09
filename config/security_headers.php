<?php
if (!function_exists('send_security_headers')) {
    function send_security_headers(bool $allowCache = false): void
    {
        header_remove('X-Powered-By');

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Permissions-Policy: accelerometer=(), autoplay=(), camera=(), clipboard-read=(), clipboard-write=(self), display-capture=(), encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), usb=(), xr-spatial-tracking=()');

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "script-src 'self' https://cdn.jsdelivr.net",
            "script-src-elem 'self' https://cdn.jsdelivr.net",
            "script-src-attr 'none'",
            "style-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src-elem 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src-attr 'none'",
            "font-src 'self' data: https://cdnjs.cloudflare.com",
            "img-src 'self' data:",
            "connect-src 'self'",
            "media-src 'self'",
            "manifest-src 'self'",
            "worker-src 'self'",
            "frame-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            'upgrade-insecure-requests',
        ];
        header('Content-Security-Policy: ' . implode('; ', $csp));

        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

        if ($allowCache) {
            header('Cache-Control: public, max-age=86400');
        } else {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    send_security_headers(false);
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('403 Forbidden');
}
?>
