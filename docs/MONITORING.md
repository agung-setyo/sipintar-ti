# Monitoring & Alerting (Recommendations)

Options to collect logs and monitor errors:

- Sentry (error tracking): add `sentry/sentry-php` and initialize in bootstrap to capture exceptions.
- Log shipping: forward `audit_logs` and `security_events` to centralized logging (ELK/Graylog) via beats or API.
- Uptime monitoring: use services like UptimeRobot or Pingdom.

Basic Sentry init example (add to a common bootstrap file):

```php
// example only - requires sentry/sentry-php via composer
\Sentry\init(['dsn' => getenv('SENTRY_DSN')]);
\Sentry\captureMessage('Sentry initialized');
```

Store DSN and credentials as environment variables or secrets; do not commit them.

## Integrasi Sentry (langkah cepat)

1. Tambahkan package via Composer (di development / CI):

```bash
composer require sentry/sentry-php
```

2. Konfigurasi DSN di environment (mis. pada server set `SENTRY_DSN`).

3. Aktifkan inisialisasi Sentry di aplikasi. Contoh: include `includes/sentry.php` di bootstrap utama (mis. di `index.php` atau `includes/header.php`):

```php
// early in request lifecycle
if (file_exists(__DIR__ . '/includes/sentry.php')) {
	include_once __DIR__ . '/includes/sentry.php';
}
```

4. Tangkap exception global (contoh minimal di bootstrap):

```php
try {
	// app bootstrap / routing
} catch (\Throwable $e) {
	if (function_exists('\Sentry\\captureException')) {
		\Sentry\captureException($e);
	}
	throw $e;
}
```

## Log Forwarding / Centralized Logging

- Untuk volume besar, gunakan Filebeat/Fluentd untuk meneruskan log ke ELK/Graylog.
- Gunakan format JSON untuk log agar mudah diindeks.

## Uptime & Healthchecks

- Daftarkan endpoint health-check (mis. `/healthz`) yang mengembalikan 200 ketika service sehat.
- Gunakan UptimeRobot atau layanan serupa untuk pengecekan periodik.

