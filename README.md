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