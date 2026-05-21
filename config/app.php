<?php
/**
 * Konfigurasi aplikasi dan helper URL.
 *
 * Helper URL aplikasi.
 */
if (!defined('APP_NAME')) {
    define('APP_NAME', 'SIPINTAR-TI');
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $knownDirs = ['/admin/', '/auth/', '/peminjam/', '/includes/', '/config/', '/middleware/', '/helpers/'];

        foreach ($knownDirs as $dir) {
            $pos = strpos($script, $dir);
            if ($pos !== false) {
                return rtrim(substr($script, 0, $pos), '/');
            }
        }

        $dir = str_replace('\\', '/', dirname($script));
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            return '';
        }

        return rtrim($dir, '/');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = app_base_path();
        $path = ltrim($path, '/');

        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }

        return ($base === '' ? '' : $base) . '/' . $path;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>
