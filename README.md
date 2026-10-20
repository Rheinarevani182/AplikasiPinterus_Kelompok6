# Pinterus

Pinterus adalah aplikasi berbagi inspirasi visual yang terinspirasi dari Pinterest. Pengguna dapat menemukan, menyimpan, dan membagikan pin, serta terhubung dengan kreator lain.

## Identitas Aplikasi

- **Aplikasi asli:** Pinterest
- **Nama plesetan:** Pinterus

## Anggota Kelompok

| No. | Nama | NIM |
| --- | --- | --- |
| 1 | MUHAMMAD DIFAA IHLASULAMAL MUNAF | 24051214178 |
| 2 | RHEINA REVANI | 24051214182 |
| 3 | ISNAINI SAFITRI NURKARTIKA PUTRI | 24051214194 |
| 4 | NIZHAR HARRY MUKTI | 24051214210 |

## Teknologi

- **Backend:** Laravel 13, PHP 8.3, Laravel Sanctum
- **Database:** SQLite (konfigurasi bawaan)
- **Aplikasi mobile:** Flutter

## Fitur Utama

- Registrasi, login, dan logout.
- Menjelajahi pin serta mencari berdasarkan kata kunci dan kategori.
- Membuat pin dengan gambar, judul, deskripsi, kategori, dan tautan opsional.
- Menyimpan pin untuk dilihat kembali.
- Membuka tautan pin, membagikan, melaporkan, atau menghapus pin milik sendiri.
- Melihat dan mengubah profil, mengikuti pengguna lain, serta mengatur akun privat.

## Alur Penggunaan

1. Buka aplikasi. Jika belum memiliki sesi, daftar atau masuk menggunakan akun Pinterus.
2. Setelah berhasil masuk, aplikasi menampilkan beranda berisi pin yang dapat dilihat oleh pengguna. Sesi login disimpan agar dapat dipulihkan saat aplikasi dibuka kembali.
3. Cari inspirasi menggunakan kolom pencarian atau pilih kategori, lalu buka pin untuk melihat detailnya.
4. Simpan pin yang disukai untuk menemukannya kembali di halaman pin tersimpan. Dari detail pin, pengguna juga dapat membuka tautan, membagikan, atau melaporkannya.
5. Untuk menerbitkan pin, pilih aksi buat pin, ambil gambar dari perangkat, isi judul dan informasi lainnya, lalu terbitkan.
6. Buka profil untuk melihat pin dan informasi pengguna. Pengguna dapat mengikuti akun lain, mengubah profil, dan mengatur privasi akunnya.
7. Keluar melalui pengaturan akun. Sesi pada perangkat akan dihapus.

## Menjalankan Proyek

### 1. Siapkan backend Laravel

Pastikan PHP 8.3, Composer, dan Flutter SDK tersedia. Dari folder utama proyek, jalankan:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

Perintah `Copy-Item` diperlukan saat menyiapkan salinan proyek baru yang belum memiliki file `.env`. Migrasi menggunakan SQLite sesuai konfigurasi bawaan.

### 2. Jalankan aplikasi Flutter

Buka terminal kedua, lalu masuk ke folder aplikasi:

```powershell
cd pinterus_mobile
flutter pub get
flutter run -d chrome
```

Untuk Android Emulator, gunakan:

```powershell
flutter run -d emulator-5554
```

Aplikasi menggunakan `http://10.0.2.2:8000/api` secara bawaan pada Android Emulator dan `http://127.0.0.1:8000/api` pada web. Untuk perangkat Android fisik, arahkan aplikasi ke alamat IP komputer yang menjalankan backend:

```powershell
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api
```

Ganti `192.168.1.10` dengan alamat IP komputer. Pastikan komputer dan perangkat berada di jaringan yang sama, dan backend dijalankan dengan `--host=0.0.0.0`.
