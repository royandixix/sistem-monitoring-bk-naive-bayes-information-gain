PATCH CONFUSION MATRIX - SISTEM MONITORING BK

Yang ditambahkan:
1. Tombol "Confusion Matrix" pada setiap baris menu Evaluasi Model.
2. Modal matriks 3x3 dengan baris = kelas aktual dan kolom = kelas prediksi.
3. Ringkasan Accuracy, Precision, Recall, F1-Score, jumlah benar dan jumlah salah.
4. Widget Confusion Matrix di Dashboard untuk membandingkan Naive Bayes baseline dan Naive Bayes + Information Gain.
5. Penyimpanan confusion_matrix dibaca otomatis sebagai array JSON oleh Laravel.

Cara pasang:
1. Salin semua folder/file patch ini ke root project sistem-monitoring-bk dan izinkan replace file yang sama.
2. Jalankan:
   php artisan optimize:clear
3. Jalankan aplikasi kembali.
4. Buka menu Klasifikasi lalu proses algoritma jika belum ada data evaluasi.
5. Buka Evaluasi Model dan klik tombol "Confusion Matrix".

Tidak perlu migration baru karena kolom confusion_matrix sudah ada di tabel evaluasi_models pada project ini.
