# Sistem Inventaris LAB

Halo, teman-teman! 👋

README ini adalah buku petunjuk kecil untuk mengerjakan proyek **Sistem
Inventaris LAB**. Kita akan membagi pekerjaan supaya semua anggota bisa
ikut coding sedikit demi sedikit tanpa mengganggu pekerjaan teman lain.

> **Ingat:** jangan takut bertanya. Lebih baik bertanya dulu daripada
> mengubah file yang bukan bagian tugasmu.

## 1. Proyek ini tentang apa?

Sistem Inventaris LAB adalah aplikasi untuk membantu mencatat dan
mengelola barang laboratorium.

Contoh hal yang ingin dibantu oleh aplikasi: - Menyimpan data barang. -
Mengelompokkan barang berdasarkan kategori. - Mencatat transaksi
inventaris. - Melihat ringkasan informasi inventaris. - Mencatat
aktivitas tertentu di sistem.

Daftar tersebut adalah gambaran kebutuhan proyek. Tidak berarti semua
fitur sudah selesai dibuat.

## 2. Teknologi yang dipakai

-   **PHP**: bahasa yang dipakai untuk menjalankan logika aplikasi.
-   **MySQL**: tempat menyimpan data.
-   **PDO**: cara PHP berbicara dengan database.
-   **Git**: alat untuk mencatat perubahan kode.
-   **GitHub**: tempat menyimpan repository dan menggabungkan pekerjaan
    tim.

## 3. Gambaran folder

Struktur berikut menggambarkan file utama yang sudah dikerjakan dan
folder tempat tugas baru akan dibuat. Folder tugas yang belum ada boleh
dibuat sesuai instruksi.

``` text
Sistem Inventaris LAB/
├── app/
│   ├── controllers/
│   │   └── DashboardController.php
│   ├── services/             # tugas Ferdi
│   ├── repositories/         # tugas Ferdi
│   ├── validators/           # tugas Ferdi
│   └── views/
│       └── partials/         # komponen tampilan jika diperlukan
├── config/                   # tugas Rizki
├── core/                     # tugas Rizki
├── database/
├── public/
│   └── index.php
├── routes/
│   └── web.php
├── docs/
├── .env                      # rahasia lokal; jangan commit
├── .env.example              # contoh konfigurasi tanpa rahasia
└── README.md
```

**Penting:** sebelum membuat folder atau file, cek isi repository dulu.
Jangan menimpa file yang sudah ada. Nama folder di atas adalah usulan
pembagian tugas; ikuti pola penamaan yang sudah dipakai project jika
berbeda.

## 4. Pembagian tugas

### A. Ferdi --- Validator, Service, dan Repository

Ferdi mengerjakan tiga bagian kecil. Jangan langsung membuat banyak
fitur sekaligus. Mulai dari satu file sederhana, tes, lalu lanjut.

#### Apa itu Validator?

Validator adalah penjaga gerbang data.

Misalnya, pengguna memasukkan nama barang kosong. Validator memeriksa
data itu dan memberi tahu bahwa nama barang harus diisi.

**Tugas Ferdi:** 1. Buat folder `app/validators/` jika belum ada. 2.
Buat file `app/validators/ItemValidator.php`. 3. Buat method untuk
memeriksa data barang sederhana, minimal nama barang tidak boleh kosong
dan jumlah stok tidak boleh negatif. 4. Kembalikan hasil yang mudah
dipakai, misalnya daftar pesan kesalahan. 5. Tes dengan data benar dan
data salah.

**Contoh hasil yang diharapkan:** - Nama `"Mikroskop"` dan stok `3` →
tidak ada kesalahan. - Nama kosong dan stok `-1` → muncul pesan
kesalahan.

**Batasan:** jangan mengubah controller, schema database, atau `.env`.

#### Apa itu Service?

Service adalah tempat menaruh aturan kerja aplikasi.

Contoh: sebelum barang disimpan, service meminta validator memeriksa
datanya. Kalau data salah, proses dihentikan. Kalau data benar, service
meneruskan pekerjaan ke repository.

**Tugas Ferdi:** 1. Setelah validator selesai, buat folder
`app/services/` jika belum ada. 2. Buat `app/services/ItemService.php`.
3. Buat satu method untuk memeriksa data sebelum penyimpanan. 4. Service
menggunakan validator, lalu memberi hasil yang jelas jika data tidak
valid. 5. Jangan menulis query SQL langsung di service.

**Batasan:** untuk tugas pertama, belum perlu menyambungkan service ke
controller atau menambahkan fitur CRUD lengkap.

#### Apa itu Repository?

Repository adalah bagian yang bertugas berbicara dengan database.

Contoh: mencari daftar barang atau mengambil satu barang dari tabel
`items`.

**Tugas Ferdi:** 1. Buat folder `app/repositories/` jika belum ada. 2.
Buat `app/repositories/ItemRepository.php`. 3. Gunakan PDO yang sudah
disediakan aplikasi; jangan membuat koneksi database baru sendiri. 4.
Buat satu method sederhana untuk mengambil daftar barang dari tabel
`items`. 5. Gunakan prepared statement jika query membutuhkan masukan
pengguna.

**Batasan:** jangan mengubah struktur tabel atau membuat tabel baru.
Jangan menghubungkan repository ke aplikasi utama sebelum Project Lead
melakukan review.

#### Urutan kerja Ferdi

1.  Validator dahulu.
2.  Periksa syntax PHP.
3.  Service menggunakan validator.
4.  Periksa syntax PHP.
5.  Repository menggunakan PDO yang sesuai pola project.
6.  Periksa syntax PHP dan minta review.

Jangan membuat ketiga file sekaligus tanpa memeriksa cara kerja tiap
bagian.

### B. Rizki --- Config dan Core

Rizki mengerjakan bagian dasar aplikasi. Bagian ini penting, jadi
**jangan mengganti atau menulis ulang file yang sudah ada**. Baca
dahulu, lalu lakukan perubahan kecil hanya bila Project Lead
menyetujuinya.

#### Apa itu Config?

Config adalah tempat menyimpan pengaturan aplikasi, seperti nama
aplikasi, mode pengembangan, dan pengaturan database.

**Tugas Rizki:** 1. Periksa file konfigurasi yang sudah ada di
`config/`. 2. Baca `config/bootstrap.php` dan pahami urutan file yang
dipanggil. 3. Buat `config/README.md` yang menjelaskan fungsi file
konfigurasi yang benar-benar ada. 4. Periksa bahwa konfigurasi sensitif
diambil dari `.env`, bukan ditulis sebagai password langsung di source
code. 5. Laporkan masalah yang ditemukan; jangan mengubah `.env` atau
nilai rahasia.

**Batasan:** untuk tugas awal, dokumentasi dan pemeriksaan saja. Jangan
membuat sistem konfigurasi baru.

#### Apa itu Core?

Core adalah perkakas dasar yang dipakai bagian lain dari aplikasi.
Contohnya router, pembaca environment, dan penghubung database.

**Tugas Rizki:** 1. Baca `core/Env.php`, `core/Database.php`, dan
`core/App.php` jika file-file itu ada. 2. Buat `core/README.md`. 3.
Jelaskan dengan bahasa sederhana fungsi masing-masing file yang
benar-benar ada. 4. Catat bagaimana route didaftarkan dan bagaimana
koneksi database diambil, berdasarkan kode aktual. 5. Jalankan
pemeriksaan syntax PHP pada file core yang ada dan catat hasilnya.

**Batasan:** jangan mengganti `App.php`, `Database.php`, atau `Env.php`;
jangan mengubah sistem routing dan koneksi database. Kalau menemukan
bug, tulis nama file, masalah, dan langkah untuk melihatnya, lalu minta
persetujuan Project Lead sebelum memperbaiki.

#### Urutan kerja Rizki

1.  Baca file yang sudah ada.
2.  Buat `config/README.md`.
3.  Buat `core/README.md`.
4.  Jalankan `php -l` untuk memeriksa syntax.
5.  Laporkan hasil dan buka Pull Request.

Rizki tetap ikut coding: dia boleh menambah contoh perintah PHP atau
snippet kecil pada dokumentasi, tetapi jangan mengubah perilaku core
tanpa review.

## 5. Cara menjalankan aplikasi

Pastikan PHP dan MySQL tersedia. Dari root proyek, periksa PHP:

``` powershell
php -v
```

Konfigurasi database disimpan di `.env` lokal. Sesuaikan nama database
dan akun dengan komputer masing-masing. Jangan membagikan password.

Untuk menjalankan server pengembangan:

``` powershell
php -S 127.0.0.1:8081 -t public
```

Buka `http://127.0.0.1:8081` di browser. Hentikan server dengan
`Ctrl+C`.

## 6. Git itu apa?

Bayangkan Git seperti buku catatan perubahan kode. Branch adalah jalur
kerja sendiri, sehingga kita bisa mencoba perubahan tanpa langsung
mengubah pekerjaan utama.

-   **`main`**: jalur utama yang dijaga tetap stabil.
-   **Branch**: jalur pribadi untuk mengerjakan satu tugas.
-   **Commit**: satu catatan perubahan.
-   **Push**: mengirim branch ke GitHub.
-   **Pull Request (PR)**: meminta teman memeriksa dan menggabungkan
    perubahan.

## 7. Aturan sebelum mulai

1.  Selalu kerjakan tugas di branch sendiri.
2.  Jangan mengerjakan langsung di `main`.
3.  Jangan mengedit file di luar tugas tanpa izin.
4.  Jangan commit `.env`, password, token, atau data pribadi.
5.  Jangan menggunakan `git reset --hard`, `git clean -fd`, atau force
    push kalau belum paham dampaknya.
6.  Kalau muncul error yang tidak dipahami, simpan pesan error lalu
    minta bantuan. Jangan asal menghapus file.
7.  Satu branch untuk satu tugas. Selesaikan PR sebelum memulai tugas
    baru jika memungkinkan.

## 8. Langkah Git untuk Ferdi

Jalankan dari folder repository. Kalau repository belum ada di komputer,
clone dahulu menggunakan URL GitHub kelompok yang benar.

``` powershell
git status
git switch main
git pull origin main
git switch -c feat/ferdi-item-layers
```

Ferdi mengerjakan hanya file-file berikut, sesuai tahap dan kesepakatan:

``` text
app/validators/ItemValidator.php
app/services/ItemService.php
app/repositories/ItemRepository.php
```

Periksa syntax file yang sudah dibuat:

``` powershell
php -l app/validators/ItemValidator.php
php -l app/services/ItemService.php
php -l app/repositories/ItemRepository.php
```

Catatan: perintah `php -l` hanya memeriksa syntax, bukan memastikan kode
sudah benar saat dijalankan.

Periksa perubahan sebelum commit:

``` powershell
git status
git diff
```

Tambahkan hanya file tugas:

``` powershell
git add app/validators/ItemValidator.php app/services/ItemService.php app/repositories/ItemRepository.php
git diff --cached
git commit -m "feat: add item validation and data layers"
git push -u origin feat/ferdi-item-layers
```

Lalu buka GitHub dan buat PR dari `feat/ferdi-item-layers` ke `main`.
Minta Project Lead memeriksa perubahan sebelum merge.

## 9. Langkah Git untuk Rizki

``` powershell
git status
git switch main
git pull origin main
git switch -c docs/rizki-config-core
```

Rizki membuat dokumentasi berikut:

``` text
config/README.md
core/README.md
```

Periksa status:

``` powershell
git status
git diff
```

Tambahkan hanya file tugas:

``` powershell
git add config/README.md core/README.md
git diff --cached
git commit -m "docs: explain config and core modules"
git push -u origin docs/rizki-config-core
```

Lalu buka GitHub dan buat PR dari `docs/rizki-config-core` ke `main`.
Minta Project Lead memeriksa sebelum merge.

## 10. Setelah PR digabungkan

Setelah PR milik anggota selesai di-merge, perbarui `main` lokal:

``` powershell
git switch main
git pull origin main
```

Jika ingin mengerjakan tugas baru, buat branch baru dari `main` terbaru.

**Peringatan:** bila ada perubahan yang belum di-commit, jangan
berpindah branch secara sembarangan. Jalankan `git status` dan
selesaikan atau amankan perubahan terlebih dahulu.

## 11. Checklist sebelum Pull Request

-   [ ] Saya bekerja di branch sendiri.
-   [ ] Saya hanya mengubah file sesuai tugas.
-   [ ] Tidak ada `.env` atau rahasia yang ikut di-commit.
-   [ ] Syntax PHP lolos untuk file PHP yang diubah.
-   [ ] Saya membaca kembali perubahan dengan `git diff`.
-   [ ] Saya menulis deskripsi PR yang menjelaskan apa yang diubah.
-   [ ] Saya tidak menyatakan fitur berhasil jika belum diuji.
-   [ ] Saya meminta review sebelum merge.

## 12. Ringkasan pembagian

  -----------------------------------------------------------------------
  Anggota                 Tugas                   File utama
  ----------------------- ----------------------- -----------------------
  Project Lead            Integrasi, review,      Core aplikasi dan
                          backend utama           integrasi

  Ferdi                   Validator, Service,     `app/validators/`,
                          Repository              `app/services/`,
                                                  `app/repositories/`

  Rizki                   Dokumentasi dan         `config/README.md`,
                          pemeriksaan Config/Core `core/README.md`
  -----------------------------------------------------------------------

**Pesan terakhir:** kita tidak harus langsung membuat fitur besar. Buat
satu file kecil, pahami fungsinya, periksa hasilnya, lalu minta review.
Dengan begitu semua anggota belajar coding dan Git dengan aman.
