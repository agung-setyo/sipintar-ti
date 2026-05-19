## SIPINTAR-TI 
Sistem Informasi Peminjaman Inventaris dan Alat Perkuliahan Jurusan Teknik Informatika.
Dibangun sebagai Final Project mata kuliah Secure Software Engineering.

## Tech Stack 
**Backend**: Laravel (PHP)
**Database**: MySQL / MariaDB
**Password Hashing**: bcrypt / Argon2
**Security Testing**: Semgrep (SAST), OWASP ZAP (DAST)

## Instalasi
git clone https://github.com/<username>/sipintar-ti.git
cd sipintar-ti

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

## Konfigurasi DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env

php artisan migrate --seed
php artisan serve

## Role
|   Role   |      Pengguna     |                Akses             |
|----------|-------------------|----------------------------------|
|   Admin  | Admin jurusan,    | jurusan, petugas                 |
|          | petugas inventaris| inventarisKelola alat,           |
|          |                   | approval, audit log              |
|   User   | Dosen, mahasiswa  | mahasiswaAjukan                  |
|          |                   | peminjaman, lihat riwayat sendiri|

## Dokumentasi
| Dokumen | Lokasi |
|------------------------|--------------------------------|
| Security Requirements (SRS-Sec) | docs/SRS-Sec.md |
| Threat Model & STRIDE | docs/Threat-Model.md |
| Vulnerability Report | docs/Vulnerability-Report.md |
| Hardening Checklist | docs/Hardening-Checklist.md |
| Security Testing Report | docs/Security-Testing-Report.md |

