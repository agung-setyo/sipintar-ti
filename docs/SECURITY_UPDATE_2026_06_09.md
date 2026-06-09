# Security Update 2026-06-09

## Perubahan Utama

1. Login admin dan peminjam dibatasi maksimal 3 kali percobaan gagal.
2. Setelah 3 kali gagal, kombinasi email, role login, dan IP dikunci selama 15 menit.
3. Percobaan gagal, blokir login, dan CSRF login invalid dicatat ke tabel `security_events`.
4. Tabel baru `login_attempts` ditambahkan untuk menyimpan status percobaan login.
5. Header keamanan diperketat melalui `.htaccess` dan `config/security.php`.
6. File `favicon.ico`, `robots.txt`, dan `sitemap.xml` ditambahkan agar server tidak mengembalikan halaman error default Hostinger tanpa header keamanan.

## File yang Diubah

- `auth/process_login.php`
- `auth/admin_login.php`
- `auth/user_login.php`
- `auth/login.php`
- `config/security.php`
- `.htaccess`
- `.user.ini`
- `database/sipintar_ti.sql`
- `healthz.php`

## File Baru

- `helpers/login_rate_limit_helper.php`
- `database/2026_06_09_login_attempts.sql`
- `favicon.ico`
- `robots.txt`
- `sitemap.xml`

## Catatan Database

Pada database baru, tabel `login_attempts` sudah tersedia di `database/sipintar_ti.sql`.

Pada database yang sudah berjalan, sistem akan mencoba membuat tabel otomatis saat proses login berjalan. Jika hosting tidak mengizinkan `CREATE TABLE` dari aplikasi, jalankan manual file berikut melalui phpMyAdmin:

Import file `database/2026_06_09_login_attempts.sql` melalui menu Import atau SQL di phpMyAdmin.

## Catatan ZAP

Temuan yang ditangani:

- CSP wildcard dan unsafe-inline dihilangkan dari konfigurasi baru.
- Directive CSP yang tidak punya fallback ditambahkan secara eksplisit, seperti `base-uri`, `form-action`, dan `frame-ancestors`.
- Header HSTS, X-Content-Type-Options, Referrer-Policy, X-Frame-Options, Permissions-Policy, COOP, dan CORP ditambahkan.
- Header `X-Powered-By` dihapus melalui PHP dan `.htaccess`.
- SRI ditambahkan pada CDN Bootstrap dan Font Awesome.
- Link Google Fonts dihapus agar tidak menambah resource lintas domain tanpa SRI. Font akan memakai fallback sistem.
- File `favicon.ico`, `robots.txt`, dan `sitemap.xml` dibuat agar tidak jatuh ke halaman error default hosting.

Temuan cross-domain untuk CDN dapat hilang total hanya jika Bootstrap, Font Awesome, dan font dipindahkan menjadi asset lokal di domain sendiri.
