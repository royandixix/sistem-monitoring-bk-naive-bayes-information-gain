# Catatan Perbaikan Full

Versi ini menyelaraskan source code aplikasi dengan metodologi dan hak akses pada skripsi.

## Perbaikan utama

- Label klasifikasi diseragamkan menjadi `Baik`, `Butuh Perhatian`, `Bermasalah`.
- Rasio eksperimen dikunci ke 70:30.
- Split 70:30 dibuat stratified dan reproducible (`random_seed = 42`).
- Fitur metode dibatasi menjadi Kerajinan, Kelakuan, dan Kerapian.
- Nilai setiap aspek dibentuk dari akumulasi poin pelanggaran resmi lalu diskalakan 0 / Ringan / Sedang / Berat.
- Hanya pelanggaran `disetujui` yang masuk ke akumulasi dan klasifikasi.
- Information Gain Resource yang sebelumnya kosong sudah dibuat lengkap.
- Hak akses OSIS, Kepala Sekolah, dan Wali Murid diperbaiki.
- Wali Murid hanya dapat melihat data anak yang terhubung dengan akunnya.
- OSIS dapat mencatat penanganan kasus resmi.
- Export laporan PDF/Excel ditambahkan untuk Guru BK dan Kepala Sekolah.
- Knowledge model Naive Bayes disimpan ke tabel `naive_bayes_models`.
- Refresh hasil klasifikasi otomatis ditambahkan setelah data pelanggaran resmi berubah; sistem menggunakan model tersimpan untuk prediksi real-time dan hanya training jika model belum tersedia.
- Model `JenisPelanggaran` dan `EvaluasiModel` diperbaiki agar kolom penting dapat tersimpan.
- Seeder role/user diperbaiki dan password demo diseragamkan menjadi `password1234`.
- Seeder periode demo diselaraskan ke Tahun Ajaran 2025/2026.
- Migration kompatibilitas ditambahkan untuk mengubah database lama tanpa menghapus data master/operasional. Data historis yang masih berstatus `menunggu` dengan `diajukan_oleh` kosong dinormalisasi sebagai data resmi.

## Perintah wajib setelah mengganti project lama

Backup database dahulu, kemudian:

```bash
composer install
php artisan migrate
php artisan optimize:clear
npm install
npm run build
```

Pastikan `.env` memiliki:

```env
PYTHON_BIN=python3
```

Untuk mereset keempat akun demo ke password `password1234`:

```bash
php artisan db:seed --class=UserSeeder
```

Setelah migration, jalankan kembali proses Naive Bayes + Information Gain dari menu **Klasifikasi**, karena hasil ML lama sengaja dibersihkan agar tidak tercampur dengan metodologi lama.

## Akun demo

- `gurubk@gmail.com` / `password1234`
- `osis@gmail.com` / `password1234`
- `kepalasekolah@gmail.com` / `password1234`
- `walimurid@gmail.com` / `password1234`

## Penting

Project aktual memakai Laravel 13 + Filament 5 dan membutuhkan PHP 8.3+. Jika naskah skripsi masih menuliskan PHP 8.1.10, bagian spesifikasi software perlu disesuaikan dengan implementasi aktual; source code ini tidak dapat sekadar dipaksa turun ke PHP 8.1 tanpa downgrade framework/dependency.

## Catatan konsistensi naskah

Ada beberapa bagian naskah yang masih saling berbeda dan tidak dapat diselesaikan hanya dari source code:

1. Bab III/batasan penelitian menetapkan tiga aspek `Kerajinan`, `Kelakuan`, `Kerapian`, sedangkan bagian landasan teori masih menyebut kategori `kehadiran`, `atribut`, `keterlambatan`, dan `karakter`. Project ini mengikuti Bab III/batasan penelitian.
2. Tabel perangkat lunak naskah menyebut PHP 8.1.10, sedangkan project aktual Laravel 13/Filament 5 membutuhkan PHP 8.3+. Project dipertahankan pada dependency aktual agar tetap dapat berjalan.
3. Naskah menyebut 500 data pelanggaran sebagai sumber dataset. Implementasi membentuk satu sampel klasifikasi per siswa-periode dari akumulasi pelanggaran resmi, karena label aktual tersedia pada level siswa-periode. Jika dosen menghendaki 500 baris pelanggaran langsung sebagai sampel model, definisi unit analisis pada naskah dan struktur label perlu ditegaskan kembali.
