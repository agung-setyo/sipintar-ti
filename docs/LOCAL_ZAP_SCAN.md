# Panduan Scan ZAP Lokal

Untuk scan di `localhost:3000`, jangan jalankan PHP built-in server tanpa router. Server bawaan PHP tidak membaca `.htaccess`, sehingga header keamanan pada file statis dan folder internal tidak ikut terkirim.

Jalankan salah satu perintah berikut dari folder project:

```bash
composer serve
```

atau:

```bash
php -S localhost:3000 router.php
```

Router ini melakukan tiga hal:

1. Memblokir akses URL langsung ke folder internal seperti `/config/`, `/database/`, `/helpers/`, `/middleware/`, `/scripts/`, `/includes/`, dan `/docs/`.
2. Mengirim security header untuk file statis seperti CSS, JS, `robots.txt`, dan `sitemap.xml`.
3. Menghapus header `X-Powered-By` pada response PHP.

Di hosting Apache/Hostinger, pengamanan tetap dilakukan melalui `.htaccess` dan `.user.ini`.
