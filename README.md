# Sistem Monitoring Bimbingan Konseling SMP Frater Makassar

Aplikasi web berbasis **Laravel + Filament** untuk digitalisasi pencatatan pelanggaran, validasi Guru BK, penanganan kasus, monitoring orang tua, serta klasifikasi perilaku siswa menggunakan **Naive Bayes yang dioptimasi dengan Information Gain**.

Versi project ini telah diselaraskan dengan metodologi skripsi **“Optimasi Algoritma Naïve Bayes Menggunakan Information Gain untuk Klasifikasi Perilaku Siswa dalam Sistem Monitoring Bimbingan Konseling di SMP Frater Makassar.”**

## Metodologi yang Diterapkan

- Data klasifikasi hanya menggunakan **pelanggaran yang sudah disetujui Guru BK**.
- Periode penelitian/demo default: **Tahun Ajaran 2025/2026**.
- Tiga aspek utama: **Kerajinan, Kelakuan, Kerapian**.
- Poin resmi diakumulasikan per aspek, lalu diskalakan menjadi:
    - `Tidak Ada` = 0 poin
    - `Ringan` = 1–4 poin
    - `Sedang` = 5–15 poin
    - `Berat` = 16 poin atau lebih
- Information Gain dihitung pada data training untuk meranking tiga aspek dan memilih fitur yang informatif.
- Kelas target:
    - **Baik**
    - **Butuh Perhatian**
    - **Bermasalah**
- Pembagian data wajib **70% training : 30% testing** dengan stratified split dan random seed `42`.
- Evaluasi menghasilkan **Accuracy, Precision, Recall, F1-Score**, serta **Confusion Matrix 3×3**.
- Sistem menyimpan hasil Naive Bayes murni dan Naive Bayes + Information Gain agar peningkatan performa dapat dibandingkan.
- Model probabilitas hasil training disimpan sebagai **knowledge model** di tabel `naive_bayes_models`; pelanggaran resmi baru memakai model tersimpan untuk prediksi real-time tanpa mengulang evaluasi 70:30.

## Hak Akses Pengguna

| Role                                  | Hak akses utama                                                                                                                                                                                                          |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Guru BK / `super_admin`**           | Akses penuh; kelola kelas, siswa, jenis pelanggaran, akun, label aktual; catat/ubah/hapus pelanggaran; setujui/tolak laporan OSIS; kelola penanganan; jalankan klasifikasi; lihat IG/evaluasi; export laporan PDF/Excel. |
| **OSIS / `admin`**                    | Lihat data siswa; ajukan laporan pelanggaran milik akun sendiri; perbaiki laporan yang ditolak; lihat hasil klasifikasi; lihat dan mencatat penanganan pada pelanggaran resmi.                                           |
| **Kepala Sekolah / `kepala_sekolah`** | Read-only untuk data siswa, pelanggaran resmi, penanganan, klasifikasi, Information Gain, evaluasi model; dapat export laporan PDF/Excel.                                                                                |
| **Wali Murid / `wali_murid`**         | Hanya melihat pelanggaran resmi, hasil klasifikasi, dan riwayat penanganan **anak yang terhubung dengan akun wali**.                                                                                                     |

## Fitur Utama

1. Login multi-role menggunakan Filament.
2. Master kelas, siswa, jenis pelanggaran, dan pengguna.
3. Relasi satu akun wali dengan satu atau beberapa siswa.
4. Pengajuan pelanggaran oleh OSIS dengan status `menunggu`, `disetujui`, atau `ditolak`.
5. Validasi pelanggaran oleh Guru BK.
6. Akumulasi poin hanya dari pelanggaran resmi/disetujui.
7. Label aktual perilaku untuk data supervised learning.
8. Proses Naive Bayes + Information Gain melalui Python.
9. Ranking Information Gain untuk Kerajinan, Kelakuan, dan Kerapian.
10. Perbandingan hasil Naive Bayes murni vs Naive Bayes + Information Gain.
11. Confusion Matrix dan metrik evaluasi model.
12. Refresh klasifikasi otomatis saat pelanggaran resmi dibuat, disetujui, diubah, atau dihapus. Jika knowledge model sudah tersedia, sistem hanya melakukan prediksi real-time; jika belum, sistem membentuk model melalui training 70:30.
13. Penanganan kasus oleh Guru BK dan OSIS.
14. Dashboard berbeda sesuai role, termasuk dashboard terbatas Wali Murid.
15. Export laporan pelanggaran resmi ke **PDF** atau **Excel (.xls)** per periode, kelas, atau siswa.

## Persyaratan Aplikasi

Project saat ini menggunakan dependency aktual berikut:

- PHP **8.3 atau lebih baru**
- Laravel **13.x**
- Filament **5.x**
- MySQL/MariaDB
- Composer
- Python 3
- Node.js + npm

> Catatan: jangan menurunkan PHP project ini ke PHP 8.1 tanpa sekaligus menurunkan Laravel/Filament karena dependency project saat ini membutuhkan PHP yang lebih baru.

## Instalasi Baru

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Atur database MySQL pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_monitoring_bk
DB_USERNAME=root
DB_PASSWORD=

PYTHON_BIN=python3
```

Kemudian:

```bash
php artisan migrate
php artisan db:seed
npm run build
composer run dev
```

Buka:

```text
http://127.0.0.1:8000/admin
```

## Akun Demo

Semua akun demo menggunakan password yang sama:

```text
password1234
```

| Pengguna       | Email                     | Role             |
| -------------- | ------------------------- | ---------------- |
| Guru BK        | `gurubk@gmail.com`        | `super_admin`    |
| Pengurus OSIS  | `osis@gmail.com`          | `admin`          |
| Kepala Sekolah | `kepalasekolah@gmail.com` | `kepala_sekolah` |
| Wali Murid     | `walimurid@gmail.com`     | `wali_murid`     |

Untuk penggunaan nyata, ubah password setiap akun setelah demo.

## Cara Memperbarui Project/Database Lama

Sebelum migrasi, **backup database MySQL terlebih dahulu**.

Setelah mengganti source code dengan versi ini, jalankan:

```bash
composer install
php artisan migrate
php artisan optimize:clear
npm install
npm run build
```

Migration penyelarasan metodologi akan:

- mengubah label lama `Perlu Pembinaan` menjadi `Butuh Perhatian`;
- menormalkan aspek lama menjadi tiga aspek penelitian;
- menyinkronkan kolom kompatibilitas jenis pelanggaran;
- menormalkan data pelanggaran historis yang dibuat sebelum workflow approval sebagai data resmi ketika `diajukan_oleh` masih kosong;
- menghapus **hasil ML lama** (`klasifikasis`, `evaluasi_models`, `information_gain_results`) karena hasil tersebut tidak lagi valid setelah metode, label, dan rasio diperbaiki.

Migration **tidak menghapus data siswa, kelas, jenis pelanggaran, pelanggaran, penanganan, akun, atau label aktual**.

Setelah migrasi, buka menu **Klasifikasi** dan jalankan kembali proses untuk periode penelitian.

Jika ingin memastikan keempat akun demo memakai password `password1234`, jalankan hanya seeder akun:

```bash
php artisan db:seed --class=UserSeeder
```

Jangan menjalankan full `php artisan db:seed` pada database penelitian yang sudah berisi data asli kecuali memang ingin menambahkan data demo.

## Menjalankan Engine Python

Cek Python:

```bash
python3 --version
```

Mac/Linux:

```env
PYTHON_BIN=python3
```

Windows, bila perintah Python adalah `python`:

```env
PYTHON_BIN=python
```

Engine berada di:

```text
python/naive_bayes_ig.py
```

Laravel mengirim JSON melalui stdin dan menerima hasil JSON melalui stdout. Tidak ada package machine-learning Python eksternal yang wajib dipasang karena implementasi Entropy, Information Gain, Naive Bayes, split 70:30, dan Confusion Matrix menggunakan Python standard library.

## Urutan Penggunaan Penelitian

1. Login sebagai Guru BK.
2. Pastikan data kelas, siswa, dan jenis pelanggaran sudah benar.
3. Hubungkan akun Wali Murid dengan siswa yang sesuai.
4. Isi/import data pelanggaran.
5. Untuk laporan OSIS, Guru BK melakukan **Setujui** atau **Tolak**.
6. Isi **Label Perilaku** periode yang akan diuji. Masing-masing kelas target minimal memiliki data yang cukup untuk training/testing.
7. Buka menu **Klasifikasi**.
8. Pilih Tahun Ajaran dan Semester, kemudian jalankan **Proses Naive Bayes + Information Gain**.
9. Periksa:
    - hasil klasifikasi;
    - ranking Information Gain;
    - perbandingan baseline vs optimized;
    - Accuracy, Precision, Recall, F1-Score;
    - Confusion Matrix.
10. Gunakan menu **Laporan Pelanggaran → Export Laporan** untuk PDF/Excel.

## Validasi Setelah Update

```bash
php artisan migrate:status
php artisan optimize:clear
python3 -m py_compile python/naive_bayes_ig.py
```

Kemudian login dengan keempat role dan pastikan:

- Guru BK melihat seluruh fungsi administrasi dan algoritma.
- OSIS hanya mengelola pengajuan pelanggarannya sendiri, tetapi dapat melihat siswa, klasifikasi, dan penanganan resmi.
- Kepala Sekolah bersifat read-only dan memiliki export laporan.
- Wali Murid tidak dapat melihat data siswa lain.

## Struktur Penting

```text
app/
├── Filament/
│   ├── Resources/
│   └── Widgets/
├── Models/
│   └── NaiveBayesModel.php
└── Services/
    ├── PythonNaiveBayesInformationGainService.php
    ├── KlasifikasiAutoRefreshService.php
    └── PelanggaranReportService.php

database/
├── migrations/
└── seeders/

python/
└── naive_bayes_ig.py
```

## Catatan Data Penelitian

Seeder hanya disediakan untuk **demo aplikasi**. Dataset penelitian 500 rekam pelanggaran harus berasal dari data resmi SMP Frater Makassar dan tidak dibuat/fabrikasi oleh aplikasi.
