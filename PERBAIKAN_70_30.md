# Penerapan Pembagian Data 70:30

Versi aplikasi ini dikunci menggunakan **70% data training dan 30% data testing**.

Implementasi utama:

- `ListKlasifikasis.php` mengirim `trainingRatio: 0.7`.
- `PythonNaiveBayesInformationGainService.php` menolak rasio selain `0.7`.
- `python/naive_bayes_ig.py` memvalidasi rasio `0.7` dan melakukan stratified split dengan random seed.
- Information Gain dihitung hanya dari data training.
- Naive Bayes dilatih hanya dari data training.
- Data testing dipakai untuk Confusion Matrix, Accuracy, Precision, Recall, dan F1 Score.
- Tabel evaluasi menampilkan rasio sebagai `70:30`.

Untuk database lama, jalankan:

```bash
php artisan migrate
php artisan optimize:clear
```

Lalu proses ulang model dari menu **Klasifikasi Perilaku Siswa → Proses Naive Bayes + Information Gain** agar hasil evaluasi menggunakan 70:30.
