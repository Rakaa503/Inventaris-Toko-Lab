# Panduan Git Workflow untuk Pemula

Halo, tim! 👋

README ini menjelaskan **cara kerja bareng menggunakan Git dan GitHub** dengan langkah yang sederhana. Ikuti urutannya pelan-pelan. Tujuannya supaya pekerjaan setiap anggota rapi dan kode teman lain tidak tertimpa.

> **Aturan utama:** sebelum mulai bekerja, ambil perubahan terbaru. Kerjakan tugas di branch sendiri. Simpan perubahan dengan commit, lalu kirim ke GitHub untuk diperiksa sebelum digabungkan ke `main`.

---

## 1. Kenalan dengan istilah Git

- **Repository (repo):** folder proyek yang riwayat perubahannya dicatat oleh Git.
- **GitHub:** tempat menyimpan repo secara online agar tim bisa bekerja bersama.
- **`main`:** branch utama. Anggap seperti versi utama proyek yang harus tetap aman.
- **Branch:** jalur kerja terpisah untuk mengerjakan satu tugas tanpa langsung mengubah `main`.
- **Commit:** catatan/snapshot perubahan yang sudah kita simpan di Git.
- **Push:** mengirim commit dari komputer kita ke GitHub.
- **Pull:** mengambil perubahan terbaru dari GitHub dan menggabungkannya ke branch yang sedang kita pakai.
- **Pull Request (PR):** permintaan agar perubahan di branch kita diperiksa lalu digabungkan ke `main`.
- **Merge:** menggabungkan perubahan dari satu branch ke branch lain.
- **Conflict:** keadaan ketika Git tidak bisa menentukan perubahan mana yang harus dipakai secara otomatis. Jangan panik; selesaikan bersama sebelum melanjutkan.

---

## 2. Siapkan proyek pertama kali

Langkah ini cukup dilakukan **sekali di setiap komputer**.

### A. Pastikan Git terpasang

Buka terminal di VS Code, PowerShell, atau CMD, lalu jalankan:

```bash
git --version
```

Kalau muncul versi Git, lanjutkan. Kalau perintah tidak dikenali, pasang Git terlebih dahulu.

### B. Ambil repo dari GitHub

Buka halaman repository proyek di GitHub. Klik **Code**, salin URL HTTPS repo, lalu jalankan:

```bash
git clone URL_REPOSITORY
```

Ganti `URL_REPOSITORY` dengan URL yang benar-benar disalin dari GitHub. Contoh bentuk URL: `https://github.com/USERNAME/NAMA-REPO.git`.

Masuk ke folder repo yang baru dibuat:

```bash
cd NAMA-REPO
```

Pastikan terminal berada di folder proyek sebelum menjalankan perintah Git berikutnya.

---

## 3. Setiap kali mulai mengerjakan tugas

### Langkah 1 — Periksa posisi dan kondisi repo

```bash
git status
```

Perintah ini memberi tahu branch aktif dan apakah ada file yang berubah.

### Langkah 2 — Pindah ke branch utama

```bash
git switch main
```

### Langkah 3 — Ambil versi terbaru dari GitHub

```bash
git pull origin main
```

Ini membantu agar pekerjaan dimulai dari kode terbaru yang sudah ada di `main`.

### Langkah 4 — Buat branch khusus untuk tugasmu

Pakai nama branch yang menjelaskan pekerjaan. Contoh:

```bash
git switch -c feature/item-validator
```

Contoh nama branch lain:

- `feature/item-service` — membuat service item
- `docs/config-guide` — menulis dokumentasi konfigurasi
- `fix/login-validation` — memperbaiki validasi login

**Jangan mengerjakan tugas langsung di branch `main`.** Buat branch baru untuk setiap tugas yang terpisah.

---

## 4. Setelah mengubah atau membuat file

### Langkah 1 — Lihat file yang berubah

```bash
git status
```

Baca daftar file dengan teliti. Pastikan hanya perubahan yang memang ingin kamu kirim.

### Langkah 2 — Masukkan perubahan ke staging

Kalau semua perubahan yang tampil memang ingin disimpan dalam satu commit:

```bash
git add .
```

Kalau hanya ingin memasukkan satu file, gunakan nama file tersebut, misalnya:

```bash
git add app/validators/ItemValidator.php
```

**Penting:** periksa dulu agar file rahasia seperti `.env`, password, token, atau konfigurasi lokal tidak ikut ditambahkan. Jangan pernah mengunggah rahasia ke GitHub.

### Langkah 3 — Simpan sebagai commit

```bash
git commit -m "Add item validator"
```

Tulis pesan commit yang singkat tetapi jelas. Contoh:

- `Add item validator`
- `Create item service`
- `Document database configuration`
- `Fix item input validation`

Commit adalah catatan perubahan di komputer lokal. Commit belum otomatis terkirim ke GitHub.

### Langkah 4 — Kirim branch ke GitHub

```bash
git push -u origin feature/item-validator
```

Ganti `feature/item-validator` sesuai nama branch milikmu. Untuk push berikutnya pada branch yang sama, biasanya cukup:

```bash
git push
```

---

## 5. Buat Pull Request (PR)

Setelah berhasil push:

1. Buka repository proyek di GitHub.
2. Biasanya GitHub menampilkan tombol **Compare & pull request**. Klik tombol itu. Jika tidak muncul, buka tab **Pull requests**, lalu pilih **New pull request**.
3. Pastikan tujuan penggabungan adalah branch `main` dan sumbernya adalah branch tugasmu.
4. Isi judul yang jelas, misalnya `Add item validator`.
5. Jelaskan secara singkat apa yang dibuat atau diubah dan bagaimana cara memeriksanya.
6. Buat PR, lalu minta anggota tim yang bertanggung jawab untuk melakukan review.
7. Jika ada masukan, perbaiki file di branch yang sama, lalu jalankan `git add`, `git commit`, dan `git push` lagi. Perubahan akan masuk ke PR yang sama.
8. Gabungkan PR hanya setelah diperiksa dan disetujui sesuai kesepakatan tim.

**Jangan langsung menggabungkan PR sendiri jika tim sudah menetapkan bahwa perubahan harus diperiksa oleh ketua atau reviewer.**

---

## 6. Setelah PR digabungkan

Kalau tugasmu sudah selesai dan PR sudah masuk ke `main`, perbarui branch utama lokal:

```bash
git switch main
git pull origin main
```

Untuk tugas berikutnya, buat branch baru dari `main` yang sudah diperbarui. Jangan memakai satu branch yang sama untuk banyak tugas berbeda jika bisa dipisahkan.

---

## 7. Contoh alur lengkap

Misalnya kamu mendapat tugas membuat `ItemValidator.php`.

```bash
# 1. Perbarui branch utama
git switch main
git pull origin main

# 2. Buat branch tugas
git switch -c feature/item-validator

# 3. Kerjakan dan simpan file, lalu periksa perubahan
git status

# 4. Masukkan file yang dikerjakan
git add app/validators/ItemValidator.php

# 5. Buat commit
git commit -m "Add item validator"

# 6. Kirim branch ke GitHub
git push -u origin feature/item-validator
```

Setelah itu, buka GitHub dan buat Pull Request ke `main`. Tunggu review dan ikuti arahan tim.

> Contoh di atas mengasumsikan file `app/validators/ItemValidator.php` memang ada atau memang menjadi file yang kamu buat. Kalau struktur folder proyek berbeda, gunakan path file yang benar di proyekmu.

---

## 8. Kalau ada perubahan dari teman saat kamu sedang bekerja

Jangan langsung panik atau menjalankan perintah yang belum dipahami. Simpan pekerjaanmu terlebih dahulu sebagai commit jika sudah siap, lalu tanyakan kepada ketua/reviewer cara memperbarui branch tugasmu dari `main`.

Hindari menjalankan perintah seperti `git reset --hard`, `git push --force`, atau menghapus branch jika belum benar-benar paham. Perintah tersebut bisa menghilangkan pekerjaan atau menimpa riwayat perubahan.

---

## 9. Kalau muncul merge conflict

Conflict berarti ada bagian file yang diubah dengan cara berbeda sehingga Git membutuhkan keputusan manusia.

1. Baca pesan Git untuk mengetahui file yang bermasalah.
2. Buka file tersebut di VS Code.
3. Cari penanda seperti `<<<<<<<`, `=======`, dan `>>>>>>>`.
4. Diskusikan dengan teman pemilik perubahan jika kamu tidak yakin bagian mana yang benar.
5. Rapikan isi file dan hapus penanda conflict setelah menentukan isi final.
6. Jalankan `git status`, lalu tes perubahan.
7. Jika hasilnya sudah benar, tambahkan file dan buat commit sesuai arahan Git.

Jangan menghapus salah satu bagian secara asal hanya supaya conflict hilang.

---

## 10. Aturan kerja tim yang wajib diingat

1. **Satu tugas, satu branch.** Nama branch harus mudah dimengerti.
2. **Jangan bekerja langsung di `main`.**
3. **Selalu jalankan `git status`** sebelum `add`, `commit`, atau `push`.
4. **Commit secara bertahap** ketika satu bagian pekerjaan sudah jelas dan masuk akal.
5. **Jangan mengirim `.env`, password, token, atau data rahasia.** Pastikan file rahasia diabaikan oleh `.gitignore`.
6. **Jangan mengedit file milik teman tanpa koordinasi**, terutama jika file itu sedang dikerjakan orang lain.
7. **Buat Pull Request dan minta review** sebelum perubahan masuk ke `main`, sesuai kesepakatan tim.
8. **Jangan menggunakan `git push --force` atau `git reset --hard`** kecuali sudah memahami risikonya dan mendapat persetujuan jika diperlukan.
9. **Kalau tidak yakin, berhenti dulu dan tanya tim.** Lebih baik bertanya daripada kehilangan pekerjaan teman.

---

## Ringkasan paling singkat

Urutan kerja sehari-hari:

```text
Perbarui main
    ↓
Buat branch tugas
    ↓
Kerjakan kode/dokumentasi
    ↓
Periksa dengan git status
    ↓
git add
    ↓
git commit
    ↓
git push
    ↓
Buat Pull Request di GitHub
    ↓
Review → perbaiki jika perlu → merge
```

**Intinya:** `pull` untuk mengambil perubahan terbaru, `branch` untuk memisahkan pekerjaan, `commit` untuk menyimpan catatan perubahan, `push` untuk mengirimnya ke GitHub, dan **Pull Request** untuk meminta pemeriksaan sebelum perubahan digabungkan.
