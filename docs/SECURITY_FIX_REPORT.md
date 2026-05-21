# Security Fix Report - SIPINTAR-TI

Dokumen ini merangkum revisi keamanan yang dilakukan agar proyek lebih sesuai dengan arahan Secure Software Engineering Minggu 1-4.

## Ringkasan Perbaikan Kode

| ID | Area | Masalah Sebelum Revisi | Perbaikan |
|---|---|---|---|
| FIX-001 | Login | Query login menggunakan raw SQL berbasis input email. | Diubah menjadi prepared statement dan error login dibuat generik. |
| FIX-002 | Login | Belum ada validasi CSRF pada `process_login.php`. | Menambahkan validasi token CSRF pada proses login. |
| FIX-003 | Login | Belum ada session regeneration setelah login. | Menambahkan `session_regenerate_id(true)` setelah autentikasi berhasil. |
| FIX-004 | Login | Belum ada pembatasan percobaan login. | Menambahkan rate limiting sederhana: 5 percobaan gagal dalam 5 menit diblokir sementara. |
| FIX-005 | Admin | Beberapa halaman admin belum memanggil middleware auth/admin. | Menambahkan `middleware/auth.php` dan `middleware/admin.php` pada halaman admin kategori, item, borrow, dan logs. |
| FIX-006 | CRUD Admin | Create/edit/delete kategori dan item masih raw SQL. | Diubah menjadi prepared statement. |
| FIX-007 | CSRF | Delete/approve/reject/return/cancel menggunakan GET. | Diubah menjadi POST + token CSRF. |
| FIX-008 | Database | Kode memakai kolom `condition`, sedangkan schema memakai `item_condition`. | Diseragamkan ke `item_condition`. |
| FIX-009 | Secrets | File `.env` ikut dalam ZIP. | `.env` dihapus dari deliverable, dibuat `.env.example`. |
| FIX-010 | Security Headers | Header keamanan belum lengkap dan CSP terlalu sempit untuk aset CDN yang digunakan. | Menambahkan CSP yang sesuai, Permissions-Policy, Referrer-Policy, dan HSTS saat HTTPS. |
| FIX-011 | Audit & Security Event | Logging belum konsisten untuk event sensitif. | Menambahkan helper `save_security_event()` dan log untuk login gagal, CSRF invalid, aksi admin, dan cancel borrow. |
| FIX-012 | Peminjam | Halaman history dan daftar item masih membangun query dari input GET. | Diubah menjadi prepared statement dengan whitelist sort/status. |

## File Penting yang Diubah

- `auth/process_login.php`
- `config/database.php`
- `config/session.php`
- `config/security.php`
- `helpers/csrf_helper.php`
- `helpers/log_helper.php`
- `admin/categories/*.php`
- `admin/items/*.php`
- `admin/borrow/*.php`
- `admin/logs/index.php`
- `peminjam/cancel.php`
- `peminjam/history.php`
- `peminjam/items.php`
- `.gitignore`
- `.env.example`

## Catatan Pengujian Cepat

Validasi sintaks dilakukan dengan:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

Hasil: semua file PHP yang tersisa tidak memiliki syntax error.

## Rekomendasi Lanjutan

1. Jalankan aplikasi di XAMPP/local server dan uji semua flow: register, login, tambah kategori, tambah item, peminjaman, approval, return, dan cancel.
2. Jalankan SAST dengan Semgrep atau SonarQube.
3. Jalankan DAST dengan OWASP ZAP pada `http://localhost/sipintar-ti`.
4. Ambil screenshot sebagai bukti untuk laporan Minggu 2-4.
5. Buat commit terpisah untuk setiap perbaikan jika proyek akan dikirim lewat Git.
