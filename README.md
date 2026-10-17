# K3 Inventaris Toko/Lab

Sistem inventaris untuk pengelolaan barang toko atau laboratorium.
Proyek kelompok 3 dengan fokus CRUD, transaksi stok, dan security by design.

## 1. Tujuan

- Mengelola data kategori dan barang.
- Mencatat barang masuk dan barang keluar.
- Menjaga konsistensi stok melalui transaksi database.
- Mengamankan autentikasi, sesi, input, dan akses pengguna.
- Menyediakan audit log untuk aktivitas penting.
- Menyiapkan pengujian fungsional dan keamanan.

## 2. Teknologi

- PHP 8.2 atau lebih baru.
- MySQL 8.x.
- PDO dengan driver pdo_mysql.
- HTML5, CSS3, dan JavaScript.
- Composer dan PHPUnit.
- Git dan GitHub.

Arsitektur awal menggunakan PHP MVC native, bukan framework.
Versi PHP dan dependensi harus diverifikasi pada lingkungan pengembangan.

## 3. Fitur

### Inventaris

- CRUD kategori.
- CRUD barang.
- Kode barang unik.
- Harga, satuan, stok, dan stok minimum.
- Pencarian dan pagination.
- Peringatan stok minimum.

### Transaksi stok

- Barang masuk.
- Barang keluar.
- Riwayat transaksi.
- Validasi stok tidak boleh negatif.
- Pencatatan pengguna, waktu, tipe, jumlah, dan catatan.
- Transaksi database atomik untuk perubahan stok.

### Pengguna dan akses

- Login dan logout.
- Password hashing dan rehash.
- Role-based access control (RBAC).
- Perlindungan sesi.
- CSRF protection.
- Audit log aktivitas penting.

Fitur tersebut adalah target implementasi. Pembuatan struktur folder saja
belum berarti seluruh fitur sudah berfungsi atau aman.

## 4. Arsitektur

Alur request:

HTTP Request
  -> Public Front Controller
  -> Security Headers
  -> Rate Limiting
  -> Session dan Authentication
  -> Authorization / RBAC
  -> CSRF dan validasi request
  -> Router
  -> Controller
  -> Service
  -> Repository / Model
  -> PDO
  -> MySQL

Controller bertanggung jawab menangani request dan response.
Service menangani aturan bisnis.
Repository atau Model menangani akses database.
Middleware menangani kontrol keamanan lintas request.
Validator menangani validasi input server-side.
Policy menentukan apakah pengguna berhak melakukan suatu tindakan.

## 5. Struktur direktori

```text
.
├── app/
│   ├── controllers/
│   ├── middleware/
│   ├── models/
│   ├── policies/
│   ├── repositories/
│   ├── services/
│   ├── validators/
│   └── views/
│       ├── auth/
│       ├── categories/
│       ├── dashboard/
│       ├── items/
│       ├── layouts/
│       └── transactions/
├── config/
├── core/
├── database/
│   ├── migrations/
│   └── seeds/
├── docs/
├── public/
│   └── assets/
│       ├── css/
│       ├── images/
│       └── js/
├── routes/
├── storage/
│   ├── cache/
│   └── logs/
├── tests/
│   ├── Integration/
│   ├── Security/
│   └── Unit/
├── .env.example
├── .gitignore
├── composer.json
├── phpunit.xml
└── README.md
```

## 6. Instalasi lokal

### Prasyarat

Pastikan PHP, Composer, dan MySQL tersedia.

```powershell
php -v
composer --version
php -m
```

Pastikan ekstensi `PDO` dan `pdo_mysql` tersedia.

### Instal dependensi

```powershell
composer install
```

### Siapkan konfigurasi

Salin `.env.example` menjadi `.env`, lalu isi konfigurasi database lokal.
Jangan pernah commit `.env` atau password database ke repository.

Buat database kosong melalui MySQL:

```sql
CREATE DATABASE inventaris_k3
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Buat migration dan implementasikan runner migration sebelum menjalankan
schema aplikasi. Jangan menganggap database siap hanya karena sudah dibuat.

### Jalankan aplikasi

Setelah `public/index.php`, router, konfigurasi, dan halaman awal selesai
diimplementasikan:

```powershell
php -S 127.0.0.1:8081 -t public
```

Jangan mengekspos server bawaan PHP ke jaringan publik.

## 7. Security by design

### Validasi dan output encoding

- Validasi semua input di sisi server.
- Validasi ID sebagai integer positif.
- Validasi jumlah stok sebagai integer positif.
- Validasi panjang input berdasarkan batas kolom database.
- Gunakan allowlist untuk nilai enum, tipe transaksi, dan role.
- Gunakan `htmlspecialchars()` untuk output HTML.
- Jangan menganggap sanitasi input sebagai pengganti output encoding.
- Jangan membangun SQL dengan menggabungkan input pengguna.

### SQL injection

Gunakan PDO prepared statements untuk seluruh query dengan input dinamis.
Nama kolom dan arah pengurutan yang tidak bisa di-bind harus dipilih dari
allowlist yang ditentukan aplikasi.

### Password hashing dan rehash

- Gunakan `password_hash($password, PASSWORD_DEFAULT)`.
- Verifikasi dengan `password_verify()`.
- Sesudah login berhasil, cek `password_needs_rehash()`.
- Jika diperlukan, hitung hash baru dan simpan dengan aman.
- Gunakan kolom database yang cukup panjang, misalnya `VARCHAR(255)`.
- Jangan simpan password asli atau hash buatan sendiri menggunakan SHA-256.
- Terapkan pesan kegagalan login yang tidak membocorkan keberadaan akun.

### Rate limiting

- Batasi login berdasarkan akun dan sumber request.
- Batasi endpoint sensitif sesuai risiko.
- Gunakan penyimpanan penghitung yang persisten dan operasi atomik.
- Tetapkan jendela waktu dan kebijakan pembersihan data limiter.
- Kembalikan HTTP 429 jika batas terlampaui.
- Jangan bergantung pada variabel PHP biasa yang hilang setelah request.
- Pertimbangkan risiko pengguna berbagi IP dan serangan terdistribusi.

### Session dan autentikasi

- Aktifkan strict session mode.
- Regenerasi session ID setelah autentikasi.
- Gunakan cookie HttpOnly dan SameSite.
- Aktifkan Secure pada lingkungan HTTPS.
- Terapkan timeout sesi sesuai kebutuhan.
- Jangan menerima session ID dari URL.
- Invalidasi sesi pada logout.

### CSRF dan authorization

- Gunakan token CSRF untuk request yang mengubah state.
- Jangan melakukan mutasi melalui GET.
- Periksa izin pada setiap request, bukan hanya dengan menyembunyikan tombol.
- Terapkan prinsip least privilege.
- Validasi kepemilikan sumber daya jika data dibatasi per pengguna.

### Transaksi stok

- Gunakan transaksi database.
- Kunci baris stok dengan mekanisme yang sesuai.
- Periksa stok tersedia sebelum barang keluar.
- Update stok dan insert riwayat transaksi dalam satu transaksi atomik.
- Rollback jika salah satu operasi gagal.
- Uji request bersamaan untuk mencegah overselling.

### Audit log

Catat aktivitas sensitif seperti login, perubahan stok, dan perubahan data
penting. Hindari mencatat password, session ID, token, atau rahasia lainnya.
Lindungi audit log dari perubahan oleh pengguna yang tidak berwenang.

### Error handling

- Jangan tampilkan stack trace kepada pengguna pada production.
- Simpan detail error di log yang aksesnya dibatasi.
- Jangan masukkan rahasia atau data sensitif ke log.
- Matikan debug pada production.

## 8. Database yang direncanakan

Tabel inti:

- `users`: identitas akun, password hash, status, dan role terkait.
- `roles`: definisi role.
- `permissions`: izin granular.
- `role_permissions`: hubungan role dan permission.
- `categories`: kategori barang.
- `items`: kode, nama, kategori, harga, satuan, stok, dan stok minimum.
- `transactions`: barang, pengguna, tipe, jumlah, waktu, dan catatan.
- `audit_logs`: aktivitas penting.

Gunakan foreign key, indeks, constraint, dan tipe data yang sesuai.
Rancangan akhir perlu disesuaikan dengan kebutuhan kelompok dan migration
yang benar-benar diimplementasikan.

## 9. Pengujian

Target pengujian:

- Login berhasil dan gagal.
- Password rehash setelah kebijakan berubah.
- Rate limit dan respons HTTP 429.
- Input tidak valid dan SQL injection.
- XSS melalui output yang di-escape.
- CSRF token tidak valid atau hilang.
- Pengguna tanpa izin menerima penolakan akses.
- Stok keluar melebihi stok tersedia ditolak.
- Perubahan stok rollback saat insert transaksi gagal.
- Transaksi bersamaan tidak menyebabkan stok negatif.
- Session ID berubah setelah login.
- Rahasia tidak muncul dalam log atau response error.

Jalankan pengujian setelah test suite tersedia:

```powershell
composer test
composer test-security
```

Pengujian keamanan harus dilakukan pada lingkungan lokal atau staging
yang diizinkan, bukan pada sistem pihak lain.

## 10. Konfigurasi dan rahasia

- `.env` hanya untuk mesin lokal atau secret manager.
- `.env.example` hanya berisi contoh tanpa rahasia.
- Gunakan akun database dengan hak minimum.
- Hindari akun root untuk deployment.
- Gunakan HTTPS pada deployment.
- Atur backup dan uji proses pemulihan.
- Batasi akses tulis ke direktori storage.
- Pastikan direktori konfigurasi dan storage tidak bisa diakses langsung
  dari web.

## 11. Workflow Git

```powershell
git status
git add .
git commit -m "chore: initialize secure inventory project"
git push
```

Sebelum commit, periksa bahwa `.env`, dump database, log, dan kredensial
tidak ikut masuk ke repository.

## 12. Roadmap

1. Bootstrap aplikasi, konfigurasi, dan router.
2. Implementasi database connection dan migration runner.
3. Middleware security headers, error handler, dan rate limiter.
4. Autentikasi, session, password hashing dan rehash.
5. RBAC dan CSRF protection.
6. CRUD kategori dan barang.
7. Transaksi stok atomik.
8. Audit log.
9. Unit, integration, dan security testing.
10. Dokumentasi deployment dan demonstrasi.

## 13. Definition of Done

Sebuah fitur belum dianggap selesai hanya karena halaman dapat dibuka.

Setiap fitur harus memiliki:
- Validasi server-side.
- Pemeriksaan otorisasi.
- Penanganan error yang aman.
- Query database yang aman.
- Pengujian untuk jalur sukses dan gagal.
- Dokumentasi penggunaan.
- Pemeriksaan regresi terhadap fitur yang sudah ada.

---

**Status:** Bootstrap struktur proyek dan dokumentasi awal.
Fitur aplikasi, migration, middleware, dan test suite harus diimplementasikan
serta diuji secara terpisah sebelum dinyatakan siap digunakan.