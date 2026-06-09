# SIPINTAR-TI

Sistem Informasi Peminjaman Inventaris dan Alat Perkuliahan Jurusan Teknik Informatika.

## Fitur

- Login
- Register
- Logout
- Role Admin
- Role Peminjam
- CRUD Categories
- CRUD Items
- Borrow System
- History Borrow
- Cancel Borrow
- Audit Log
- Security Headers
- CSRF Protection
- Session Security

---

## Teknologi

- PHP Native
- MySQL
- Bootstrap 5

---

## Cara Menjalankan

1. Copy folder ke htdocs

2. Import database:

```sql
database/sipintar_ti.sql

3. Install dependencies (developer tools, optional):

```bash
composer install
```

4. Jalankan unit tests (opsional):

```bash
vendor/bin/phpunit --configuration phpunit.xml
```

5. Backup & deploy: lihat `docs/BACKUP.md` dan `docs/CI_CD.md` untuk panduan.

6. Linting (cek sintaks PHP) - lokal:

PowerShell (Windows):
```powershell
.
\scripts\lint.ps1
```

Shell (Linux/macOS):
```bash
./scripts/lint.sh
```

## Versi Fixed Original UI

Versi ini mempertahankan tampilan original SIPINTAR-TI, tetapi sudah diperbaiki untuk Hostinger:

- Login admin tidak lagi diarahkan ke path yang salah.
- Struktur tetap satu folder `sipintar-ti` tanpa folder `public` tambahan.
- Directory listing dimatikan.
- File/folder internal diblokir dari URL.
- Database SQL sudah bersih dan aman untuk import Hostinger tanpa `CREATE DATABASE`.
- Admin dan peminjam dapat mengubah password sendiri melalui menu **Ubah Password**.

### Akun Default Database Bersih

Admin:
- `admin@sipintar.com`
- `Admin123!`

Peminjam:
- `peminjam@sipintar.com`
- `User123!`


## Perbaikan final

- Login admin diperbaiki dengan session yang lebih sederhana dan stabil.
- Redirect admin diarahkan langsung ke `admin/dashboard.php`.
- Database bersih tersedia di `database/sipintar_ti.sql`.
- Akun default:
  - Admin: `admin@sipintar.com` / `Admin123!`
  - Peminjam: `peminjam@sipintar.com` / `User123!`
- Admin dan peminjam dapat mengubah password sendiri melalui menu `Ubah Password`.


## Scan ZAP di Localhost

Jika melakukan scan pada `http://localhost:3000`, jalankan server lokal dengan router keamanan:

```bash
composer serve
```

atau:

```bash
php -S localhost:3000 router.php
```

Jangan menjalankan server PHP bawaan tanpa `router.php`, karena `.htaccess` tidak dibaca oleh PHP built-in server. Tanpa router, ZAP bisa tetap menandai CSP, `X-Content-Type-Options`, dan akses folder internal walaupun konfigurasi untuk hosting Apache/Hostinger sudah benar.
