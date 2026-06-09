<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(403); exit('403 Forbidden'); }

if (!defined('APP_NAME')) {
    define('APP_NAME', 'SIPINTAR-TI');
}

if (file_exists(__DIR__ . '/../includes/sentry.php')) {
    include_once __DIR__ . '/../includes/sentry.php';
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $knownDirs = ['/admin/', '/auth/', '/peminjam/', '/includes/', '/config/', '/middleware/', '/helpers/', '/assets/'];

        foreach ($knownDirs as $dir) {
            $pos = strpos($script, $dir);
            if ($pos !== false) {
                return rtrim(substr($script, 0, $pos), '/');
            }
        }

        $scriptName = basename($script);
        if (in_array($scriptName, ['index.php', 'error.php', 'healthz.php', 'logout.php', 'robots.php', 'sitemap.php'], true)) {
            $dir = str_replace('\\', '/', dirname($script));
            if ($dir === '/' || $dir === '.' || $dir === '\\') {
                return '';
            }
            return rtrim($dir, '/');
        }

        return '';
    }
}

if (!function_exists('public_route_aliases')) {
    function public_route_aliases(): array
    {
        return [
            'auth/login.php' => 'masuk',
            'auth/admin_login.php' => 'masuk-admin',
            'auth/user_login.php' => 'masuk-peminjam',
            'auth/register.php' => 'daftar',
            'auth/process_login.php' => 'proses-masuk',
            'auth/process_register.php' => 'proses-daftar',
            'auth/change_password.php' => 'ubah-password',
            'auth/process_change_password.php' => 'proses-ubah-password',
            'auth/logout.php' => 'keluar',
            'logout.php' => 'keluar',

            'admin/dashboard.php' => 'panel',
            'admin/borrow/index.php' => 'permintaan',
            'admin/borrow/approve.php' => 'permintaan/setujui',
            'admin/borrow/reject.php' => 'permintaan/tolak',
            'admin/borrow/return.php' => 'permintaan/kembali',
            'admin/items/index.php' => 'inventaris',
            'admin/items/create.php' => 'inventaris/tambah',
            'admin/items/edit.php' => 'inventaris/edit',
            'admin/items/delete.php' => 'inventaris/hapus',
            'admin/categories/index.php' => 'kategori',
            'admin/categories/create.php' => 'kategori/tambah',
            'admin/categories/edit.php' => 'kategori/edit',
            'admin/categories/delete.php' => 'kategori/hapus',
            'admin/logs/index.php' => 'aktivitas',
            'admin/security-events/index.php' => 'notifikasi-sistem',

            'peminjam/dashboard.php' => 'beranda',
            'peminjam/items.php' => 'katalog',
            'peminjam/borrow.php' => 'ajukan',
            'peminjam/history.php' => 'riwayat',
            'peminjam/cancel.php' => 'batalkan',
        ];
    }
}

if (!function_exists('public_route')) {
    function public_route(string $path): string
    {
        $path = ltrim($path, '/');
        $fragment = '';
        $query = '';

        $fragmentPos = strpos($path, '#');
        if ($fragmentPos !== false) {
            $fragment = substr($path, $fragmentPos);
            $path = substr($path, 0, $fragmentPos);
        }

        $queryPos = strpos($path, '?');
        if ($queryPos !== false) {
            $query = substr($path, $queryPos);
            $path = substr($path, 0, $queryPos);
        }

        $path = trim($path, '/');
        $aliases = public_route_aliases();

        return ($aliases[$path] ?? $path) . $query . $fragment;
    }
}

if (!function_exists('public_path_for_alias')) {
    function public_path_for_alias(string $alias): ?string
    {
        $alias = trim($alias, '/');
        $routes = array_flip(public_route_aliases());

        return $routes[$alias] ?? null;
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = app_base_path();
        $path = public_route($path);

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
        header('Location: ' . base_url($path), true, 303);
        exit;
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('enforce_canonical_public_url')) {
    function enforce_canonical_public_url(): void
    {
        if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return;
        }

        $base = app_base_path();
        $script = trim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base !== '') {
            $baseTrimmed = trim($base, '/');
            if ($script === $baseTrimmed) {
                $script = '';
            } elseif (strpos($script, $baseTrimmed . '/') === 0) {
                $script = substr($script, strlen($baseTrimmed) + 1);
            }
        }

        if ($script === '' || !isset(public_route_aliases()[$script])) {
            return;
        }

        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $relativeRequest = trim($requestPath, '/');

        if ($base !== '') {
            $baseTrimmed = trim($base, '/');
            if ($relativeRequest === $baseTrimmed) {
                $relativeRequest = '';
            } elseif (strpos($relativeRequest, $baseTrimmed . '/') === 0) {
                $relativeRequest = substr($relativeRequest, strlen($baseTrimmed) + 1);
            }
        }

        $canonical = public_route_aliases()[$script];
        if (trim($relativeRequest, '/') === trim($canonical, '/')) {
            return;
        }

        $query = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . base_url($canonical) . ($query !== '' ? '?' . $query : ''), true, 301);
        exit;
    }
}

enforce_canonical_public_url();
?>
