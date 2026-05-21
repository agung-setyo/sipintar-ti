# Hardening & Dependency Audit - SIPINTAR-TI

## Security Configuration Checklist

| Area | Status | Catatan |
|---|---|---|
| `.env` tidak dikirim | Done | File dihapus, `.env.example` dibuat. |
| Credential tidak hardcoded | Partial | `config/database.php` membaca env var dengan fallback local. Untuk production, wajib set env var. |
| Debug/error detail ke user | Partial | Error DB dibuat generik pada koneksi dan borrow query; tetap perlu review seluruh aplikasi. |
| Security headers | Done | CSP, XFO, XCTO, Referrer-Policy, Permissions-Policy, HSTS saat HTTPS. |
| Session cookie HttpOnly | Done | `config/session.php`. |
| Session cookie SameSite | Done | Strict. |
| Session cookie Secure | Conditional | Aktif otomatis saat HTTPS. Localhost HTTP tetap false agar aplikasi lokal berjalan. |
| Admin RBAC | Done | Middleware admin di halaman admin utama. |
| CSRF | Done | Aksi utama state-changing sudah POST + CSRF. |
| Audit log | Done | Login/logout/register/CRUD/status borrow/cancel. |
| Security event | Done | CSRF invalid dan login rate limit. |

## Dependency Audit

Proyek ini tidak terlihat memakai Composer/npm sebagai dependency manager utama. Karena itu:

- Jika tidak ada `composer.json` atau `package.json`, dependency audit dapat diberi status **N/A** dengan justifikasi bahwa aplikasi memakai PHP native, MySQLi, Bootstrap CDN, dan library frontend dari CDN.
- Jika nanti ditambahkan Composer/npm, jalankan:

```bash
composer audit
npm audit
```

## CDN Risk Note

Aplikasi memakai Bootstrap/Tabler/Google Fonts dari CDN. Untuk hardening tambahan, pertimbangkan:

1. Mengunduh aset dan menyajikannya dari domain sendiri.
2. Menambahkan Subresource Integrity (SRI) jika tetap memakai CDN.
3. Menyesuaikan CSP agar hanya CDN yang diperlukan yang diizinkan.

## Re-test Checklist

| Test | Expected Result |
|---|---|
| Login salah 5x | Diblokir sementara dan security event tercatat. |
| Delete item via GET | Ditolak dengan 405. |
| Delete item via POST tanpa CSRF | Ditolak dengan 403. |
| Peminjam akses admin | Ditolak 403. |
| User cancel borrow milik user lain | Redirect error/tidak berubah. |
| `.env` dicari dalam ZIP | Tidak ditemukan. |
