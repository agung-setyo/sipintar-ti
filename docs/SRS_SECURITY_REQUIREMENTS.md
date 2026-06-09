# SRS-Sec SIPINTAR-TI

## 1. Deskripsi Sistem

SIPINTAR-TI adalah sistem informasi peminjaman inventaris untuk lingkungan Teknik Informatika. Sistem digunakan oleh peminjam untuk melihat inventaris, mengajukan peminjaman, melihat riwayat, dan membatalkan permintaan yang masih pending. Admin mengelola kategori, item, permintaan peminjaman, audit log, dan security event.

## 2. Stakeholder

| Stakeholder | Kepentingan |
|---|---|
| Admin/Petugas | Mengelola item, kategori, persetujuan peminjaman, audit, dan monitoring keamanan. |
| Peminjam | Mengajukan peminjaman dan melihat status permintaan. |
| Dosen/Asisten | Memastikan inventaris tersedia dan proses peminjaman tercatat. |
| Tim Pengembang | Menjaga fungsionalitas dan keamanan aplikasi. |

## 3. Aset Kritis

| Aset | Risiko Utama | Perlindungan |
|---|---|---|
| Data user | Kebocoran identitas, email, NIM/NIP | RBAC, prepared statement, audit log |
| Password hash | Brute force/offline cracking | bcrypt, tidak menyimpan plaintext |
| Session cookie | Session hijacking/fixation | HttpOnly, SameSite, session regenerate |
| Data inventaris | Manipulasi stok/status | Admin-only middleware, CSRF, audit log |
| Data peminjaman | IDOR/manipulasi status | Ownership check, RBAC, prepared statement |
| Konfigurasi database | Credential leak | `.env.example`, `.env` tidak dikirim |

## 4. Trust Boundary

1. Browser pengguna ke aplikasi web: input tidak dipercaya dan harus divalidasi server-side.
2. Aplikasi web ke database: akses hanya melalui query yang sudah diparameterisasi.
3. Area publik ke area peminjam: wajib login.
4. Area peminjam ke area admin: wajib role admin.
5. Aplikasi ke file konfigurasi: secret tidak boleh tersimpan di repository.

## 5. Security Requirements

| ID | Requirement |
|---|---|
| SR-01 | Sistem HARUS menyimpan password menggunakan bcrypt. |
| SR-02 | Sistem HARUS menggunakan prepared statement untuk semua query yang memakai input user. |
| SR-03 | Sistem HARUS melakukan validasi CSRF pada seluruh request state-changing seperti login, create, update, delete, approve, reject, return, dan cancel. |
| SR-04 | Sistem HARUS melakukan regenerasi session ID setelah login berhasil. |
| SR-05 | Cookie session HARUS menggunakan HttpOnly dan SameSite Strict. |
| SR-06 | Sistem HARUS membatasi percobaan login gagal untuk mencegah brute force. |
| SR-07 | Sistem HARUS menerapkan RBAC sehingga hanya admin yang dapat mengakses fitur manajemen kategori, item, borrow approval, audit log, dan security event. |
| SR-08 | Sistem TIDAK BOLEH menampilkan detail error database kepada user. |
| SR-09 | Sistem TIDAK BOLEH menyertakan `.env`, password database, secret key, atau token di repository/deliverable. |
| SR-10 | Sistem HARUS mencatat audit log untuk login, logout, register, CRUD admin, dan perubahan status peminjaman. |
| SR-11 | Sistem HARUS mencatat security event untuk CSRF invalid, percobaan login gagal berulang, dan rate limit login. |
| SR-12 | Sistem HARUS memastikan peminjam hanya dapat membatalkan borrow request miliknya sendiri yang masih pending. |
| SR-13 | Sistem HARUS mengirim header keamanan dasar seperti CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, dan Permissions-Policy. |
| SR-14 | Sistem SEBAIKNYA menjalankan audit dependency sebelum final release. |
| SR-15 | Sistem SEBAIKNYA diuji dengan SAST dan DAST sebelum dikumpulkan. |

## 6. STRIDE Threat List

| ID | STRIDE | Ancaman | Komponen | Risk | Mitigasi |
|---|---|---|---|---|---|
| T-01 | Spoofing | Penyerang mencoba login sebagai admin. | Login | High | bcrypt, generic error, rate limiting, audit log. |
| T-02 | Spoofing | Session fixation setelah login. | Session | High | `session_regenerate_id(true)`. |
| T-03 | Tampering | Manipulasi status borrow melalui URL approve/reject. | Admin Borrow | High | POST + CSRF + admin middleware. |
| T-04 | Tampering | Mengubah data item melalui raw SQL injection. | Admin Items | Critical | Prepared statement. |
| T-05 | Tampering | Menghapus kategori via CSRF GET. | Categories | High | Delete hanya POST + CSRF. |
| T-06 | Repudiation | Admin menyangkal melakukan approve/delete. | Admin Actions | Medium | Audit log dengan user ID, IP, timestamp. |
| T-07 | Repudiation | User menyangkal membatalkan borrow. | Cancel Borrow | Medium | Audit log cancel. |
| T-08 | Information Disclosure | Error database tampil ke user. | Error Handling | Medium | Generic error, detail ke server log. |
| T-09 | Information Disclosure | `.env` terbawa dalam ZIP. | Repository | Critical | Hapus `.env`, gunakan `.env.example`. |
| T-10 | Information Disclosure | Session cookie dibaca JavaScript. | Session | High | HttpOnly cookie. |
| T-11 | Denial of Service | Brute force login berulang. | Login | Medium | Rate limiting 5 gagal/5 menit. |
| T-12 | Denial of Service | Query pencarian dengan input bebas. | Items/History | Medium | Prepared statement, whitelist sort/status. |
| T-13 | Elevation of Privilege | Peminjam membuka halaman admin. | Admin Pages | Critical | Auth + admin middleware pada semua halaman admin. |
| T-14 | Elevation of Privilege | User membatalkan borrow milik user lain. | Cancel Borrow | High | Check `id` + `user_id` + status pending. |
| T-15 | Information Disclosure | XSS melalui data nama/deskripsi. | UI Output | Medium | `htmlspecialchars()` saat render. |

## 7. Attack Tree Ringkas

### A. Tujuan: Akses Fitur Admin Tanpa Hak

- Bypass login
  - SQL Injection pada login
  - Credential stuffing
- Bypass role
  - Akses URL admin langsung
  - File admin tidak memanggil middleware
- Eksploitasi session
  - Session fixation
  - Cookie dicuri

Mitigasi: prepared statement login, bcrypt, rate limiting, session regeneration, admin middleware, HttpOnly/SameSite cookie.

### B. Tujuan: Mengubah Status Peminjaman Secara Ilegal

- CSRF ke endpoint approve/reject/return
- Manipulasi parameter `id`
- Akses endpoint admin tanpa role admin
- Replay request lama

Mitigasi: POST only, CSRF token, admin middleware, prepared statement, audit log.
