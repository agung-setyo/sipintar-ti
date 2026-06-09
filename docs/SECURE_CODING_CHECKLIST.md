# Secure Coding Checklist - SIPINTAR-TI

| No | Checklist | Status | Bukti/Catatan |
|---|---|---|---|
| 1 | Password memakai bcrypt/Argon2 | Done | `process_register.php` memakai `password_hash(..., PASSWORD_BCRYPT)`. |
| 2 | Login memakai prepared statement | Done | `auth/process_login.php`. |
| 3 | Error login generik | Done | Email/password salah tidak dibedakan. |
| 4 | CSRF pada login | Done | `process_login.php`. |
| 5 | CSRF pada create/update/delete kategori | Done | `admin/categories/*.php`. |
| 6 | CSRF pada create/update/delete item | Done | `admin/items/*.php`. |
| 7 | CSRF pada approve/reject/return borrow | Done | `admin/borrow/*.php`. |
| 8 | CSRF pada cancel borrow | Done | `peminjam/cancel.php`. |
| 9 | Admin middleware pada halaman admin | Done | kategori, item, borrow, logs. |
| 10 | Peminjam middleware pada halaman peminjam | Done | borrow/history/items/cancel. |
| 11 | Session ID regenerated after login | Done | `session_regenerate_id(true)`. |
| 12 | Cookie HttpOnly | Done | `config/session.php`. |
| 13 | Cookie SameSite Strict | Done | `config/session.php`. |
| 14 | Rate limiting login | Done | 5 gagal/5 menit. |
| 15 | Prepared statement untuk CRUD utama | Done | login, kategori, item, borrow status, cancel, history/items search. |
| 16 | Secret tidak dikirim | Done | `.env` dihapus, `.env.example` dibuat. |
| 17 | Audit log aktivitas penting | Done | login/logout/register/CRUD/status borrow/cancel. |
| 18 | Security event untuk kejadian mencurigakan | Done | CSRF invalid, login failed limit. |
| 19 | Security headers | Done | `config/security.php`. |
| 20 | Output escaping | Partial | Banyak tampilan memakai `htmlspecialchars`; tetap perlu review semua template. |
| 21 | Dependency audit | Pending | Jalankan `composer audit` jika ada Composer atau dokumentasikan N/A. |
| 22 | DAST/ZAP | Pending | Perlu aplikasi berjalan di localhost/staging. |
| 23 | SAST | Pending | Jalankan Semgrep/SonarQube dan simpan laporan. |
| 24 | Production debug off | Partial | Error DB dibuat generik, tetapi perlu cek konfigurasi server. |
