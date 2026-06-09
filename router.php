<?php
include_once __DIR__ . '/config/security_headers.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$relativePath = ltrim(rawurldecode($path), '/');
$relativePath = str_replace('\\', '/', $relativePath);

$internalPattern = '#^(config|database|helpers|middleware|scripts|includes|docs|\.github)(/|$)#i';
if (preg_match($internalPattern, $relativePath)) {
    send_security_headers(false);
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '403 Forbidden';
    return true;
}

$documentRoot = realpath(__DIR__);
$file = realpath(__DIR__ . '/' . $relativePath);

if ($file !== false && $documentRoot !== false && strpos($file, $documentRoot . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $blockedExtensions = ['sql', 'md', 'bak', 'backup', 'zip', 'tar', 'gz', '7z', 'rar', 'log', 'ini', 'yml', 'yaml', 'lock'];

    if (basename($file)[0] === '.' || in_array($extension, $blockedExtensions, true)) {
        send_security_headers(false);
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '403 Forbidden';
        return true;
    }

    if ($extension !== 'php') {
        $staticBasename = strtolower(basename($file));
        $allowStaticCache = !in_array($staticBasename, ['robots.txt', 'sitemap.xml'], true);
        send_security_headers($allowStaticCache);
        $types = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'txt' => 'text/plain; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        return true;
    }
}

include __DIR__ . '/index.php';
return true;
?>
