# Dokumentasi: Dual Role Login (Admin & Peminjam Bersamaan)

## Ringkasan Perubahan
Sistem telah diperbarui untuk memungkinkan admin dan peminjam login bersamaan dalam sesi yang sama tanpa saling menggantikan sesi.

## File-File yang Dimodifikasi

### 1. **auth/process_login.php**
- Mengubah logika session untuk menyimpan multiple logins
- Struktur session baru:
  ```php
  $_SESSION['logins'] = [
      'admin' => ['user_id', 'name', 'identity_type'],
      'peminjam' => ['user_id', 'name', 'identity_type']
  ];
  $_SESSION['current_role'] = 'admin'; // Role yang sedang aktif
  ```
- Session lama tetap tersimpan untuk backward compatibility

### 2. **middleware/admin.php**
- Diperbarui untuk mengecek keberadaan role di `$_SESSION['logins']['admin']`
- Jika admin role tersedia, otomatis switch ke admin context
- Hanya logout jika TIDAK ada admin login sama sekali

### 3. **middleware/peminjam.php**
- Logika yang sama dengan admin middleware
- Mengecek keberadaan role di `$_SESSION['logins']['peminjam']`
- Otomatis switch ke peminjam context saat mengakses area peminjam

### 4. **auth/login.php**
- Menampilkan status kedua login dengan badges
- Alert yang informatif tentang siapa yang sudah login

### 5. **auth/admin_login.php** & **auth/user_login.php**
- Alert messages yang lebih informatif
- Menunjukkan kemungkinan login kedua role

### 6. **auth/logout.php** (DIPERBARUI PENTING)
- Bukan lagi destroy seluruh session
- Hanya logout role yang sedang aktif
- Jika masih ada role lain, tetap stay logged in
- Jika semua role logout, baru destroy session

### 7. **includes/navbar.php** (BARU)
- Tambahan "Beralih Role" dropdown saat kedua role login
- Memudahkan switching antar role tanpa logout
- Tombol "Keluar Semua" untuk logout semua role

### 8. **includes/switch-role.php** (FILE BARU)
- Handle switching antar role
- Update `current_role` dan context session variables
- Redirect ke dashboard yang sesuai

## Cara Menggunakan

### Scenario 1: Admin dan Peminjam Login Bersamaan
1. Admin login melalui halaman Admin Login
2. Dari halaman login, klik "Login Peminjam"
3. Login sebagai peminjam
4. Sekarang keduanya tersimpan dalam sesi yang sama

### Scenario 2: Switching Antar Role
1. Saat sudah login kedua role, navbar akan menampilkan tombol "Beralih Role"
2. Klik untuk melihat dropdown dengan opsi admin/peminjam
3. Pilih role yang ingin diaktifkan
4. Akan redirect ke dashboard role yang dipilih

### Scenario 3: Logout Satu Role
1. Saat di dashboard admin atau peminjam, klik "Keluar"
2. Hanya logout role aktif saat ini
3. Jika masih ada role lain, kembali ke halaman login dengan role lain tetap aktif
4. Bisa login ulang sebagai role yang baru saja logout

### Scenario 4: Logout Semua
1. Klik "Beralih Role" di navbar
2. Pilih "Keluar Semua"
3. Semua login dihapus dan session destroy

## Testing Checklist

- [ ] Admin login berhasil
- [ ] Login peminjam berhasil dengan admin tetap active
- [ ] Navbar menampilkan "Beralih Role" saat kedua role login
- [ ] Bisa switch ke admin role dari peminjam
- [ ] Bisa switch ke peminjam role dari admin
- [ ] Logout satu role tidak mempengaruhi role lain
- [ ] Akses admin area tetap terlindungi middleware
- [ ] Akses peminjam area tetap terlindungi middleware
- [ ] "Keluar Semua" menghapus semua login
- [ ] Login page menampilkan badge status login

## Session Structure Lama vs Baru

### Lama (Single Role):
```php
$_SESSION = [
    'user_id' => 1,
    'name' => 'Admin Name',
    'role' => 'admin',
    'identity_type' => 'NIP'
];
```

### Baru (Multi Role):
```php
$_SESSION = [
    // Legacy fields (untuk backward compatibility)
    'user_id' => 1,        // User ID role aktif saat ini
    'name' => 'Admin Name', // Name role aktif saat ini
    'role' => 'admin',      // Role aktif saat ini
    'identity_type' => 'NIP', // Identity type role aktif
    'last_activity' => time(),
    
    // New multi-role fields
    'current_role' => 'admin',
    'logins' => [
        'admin' => [
            'user_id' => 1,
            'name' => 'Admin Name',
            'identity_type' => 'NIP'
        ],
        'peminjam' => [
            'user_id' => 5,
            'name' => 'Peminjam Name',
            'identity_type' => 'NIM'
        ]
    ]
];
```

## Backward Compatibility

Fitur ini tetap backward compatible karena:
1. Field lama tetap tersimpan dan digunakan
2. Middleware middleware masih bisa mengecek `$_SESSION['role']`
3. Code lama yang mengecek `$_SESSION['user_id']` tetap berfungsi
4. Hanya menambah field baru tanpa menghilangkan yang lama

## Catatan Penting

1. **Activity Logging**: Saat switch role, `last_activity` tidak di-update (hanya saat login pertama)
2. **Multiple Browser**: Tiap browser/tab masih punya session terpisah
3. **Security**: Session timeout tetap berlaku untuk semua role
4. **CSRF Protection**: Tetap terlindungi dengan token CSRF

---
**Last Updated**: May 22, 2026
