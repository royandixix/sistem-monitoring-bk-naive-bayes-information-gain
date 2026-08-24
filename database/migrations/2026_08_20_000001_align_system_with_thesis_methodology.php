<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_LABEL = 'Perlu Pembinaan';
    private const NEW_LABEL = 'Butuh Perhatian';

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $this->expandLabelEnumsForMigration();
        }

        $this->migrateLabels();
        $this->migrateAspects();
        $this->migrateLegacyViolations();
        $this->syncLegacyViolationTypeColumns();

        if ($driver === 'mysql') {
            $this->finalizeLabelEnums();
            $this->finalizeAspectEnum();
        }

        /*
         * Hasil ML lama tidak lagi valid setelah perubahan label, fitur,
         * dan rasio 70:30. Data master/label aktual tidak dihapus.
         * Jalankan proses klasifikasi kembali dari menu Klasifikasi.
         */
        if (Schema::hasTable('klasifikasis')) {
            DB::table('klasifikasis')->delete();
        }

        if (Schema::hasTable('evaluasi_models')) {
            DB::table('evaluasi_models')->delete();
        }

        if (Schema::hasTable('information_gain_results')) {
            DB::table('information_gain_results')->delete();
        }
    }

    public function down(): void
    {
        // Perubahan metodologi penelitian sengaja tidak dibalik otomatis.
    }

    private function migrateLabels(): void
    {
        if (Schema::hasTable('label_perilakus') && Schema::hasColumn('label_perilakus', 'label_aktual')) {
            DB::table('label_perilakus')
                ->where('label_aktual', self::OLD_LABEL)
                ->update(['label_aktual' => self::NEW_LABEL]);
        }

        if (! Schema::hasTable('klasifikasis')) {
            return;
        }

        foreach ([
            'hasil_klasifikasi',
            'label_aktual',
            'hasil_naive_bayes',
            'hasil_ig_naive_bayes',
        ] as $column) {
            if (! Schema::hasColumn('klasifikasis', $column)) {
                continue;
            }

            DB::table('klasifikasis')
                ->where($column, self::OLD_LABEL)
                ->update([$column => self::NEW_LABEL]);
        }
    }

    private function migrateAspects(): void
    {
        if (! Schema::hasTable('jenis_pelanggarans') || ! Schema::hasColumn('jenis_pelanggarans', 'aspek_pelanggaran')) {
            return;
        }

        // Pada naskah skripsi, pelanggaran kehadiran masuk Aspek Kerajinan.
        DB::table('jenis_pelanggarans')
            ->where('aspek_pelanggaran', 'Kehadiran')
            ->update(['aspek_pelanggaran' => 'Kerajinan']);

        // Data lama "Lainnya" dinormalisasi ke aspek perilaku/kelakuan.
        DB::table('jenis_pelanggarans')
            ->where('aspek_pelanggaran', 'Lainnya')
            ->update(['aspek_pelanggaran' => 'Kelakuan']);
    }


    /**
     * Data pelanggaran yang sudah ada sebelum workflow approval ditambahkan
     * memperoleh status default "menunggu" dan diajukan_oleh NULL. Data seperti
     * ini adalah data historis resmi, bukan pengajuan OSIS baru, sehingga perlu
     * dinormalisasi menjadi disetujui agar tetap ikut akumulasi poin.
     */
    private function migrateLegacyViolations(): void
    {
        if (
            ! Schema::hasTable('pelanggarans')
            || ! Schema::hasColumn('pelanggarans', 'status_pengajuan')
            || ! Schema::hasColumn('pelanggarans', 'diajukan_oleh')
        ) {
            return;
        }

        $guruBkId = Schema::hasTable('users')
            ? DB::table('users')
                ->where('role', 'super_admin')
                ->orderBy('id')
                ->value('id')
            : null;

        $updates = [
            'status_pengajuan' => 'disetujui',
        ];

        if (Schema::hasColumn('pelanggarans', 'diproses_oleh')) {
            $updates['diproses_oleh'] = $guruBkId;
        }

        if (Schema::hasColumn('pelanggarans', 'diproses_pada')) {
            $updates['diproses_pada'] = DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)');
        }

        if (Schema::hasColumn('pelanggarans', 'catatan_verifikasi')) {
            $updates['catatan_verifikasi'] =
                'Data historis dinormalisasi sebagai data resmi sebelum workflow persetujuan.';
        }

        if ($guruBkId && Schema::hasColumn('pelanggarans', 'diajukan_oleh')) {
            $updates['diajukan_oleh'] = $guruBkId;
        }

        DB::table('pelanggarans')
            ->where('status_pengajuan', 'menunggu')
            ->whereNull('diajukan_oleh')
            ->update($updates);
    }

    private function syncLegacyViolationTypeColumns(): void
    {
        if (! Schema::hasTable('jenis_pelanggarans')) {
            return;
        }

        if (
            Schema::hasColumn('jenis_pelanggarans', 'kode_jenis') &&
            Schema::hasColumn('jenis_pelanggarans', 'kode_pelanggaran')
        ) {
            DB::table('jenis_pelanggarans')
                ->whereNull('kode_pelanggaran')
                ->update(['kode_pelanggaran' => DB::raw('kode_jenis')]);
        }

        if (
            Schema::hasColumn('jenis_pelanggarans', 'nama_jenis') &&
            Schema::hasColumn('jenis_pelanggarans', 'nama_pelanggaran')
        ) {
            DB::table('jenis_pelanggarans')
                ->whereNull('nama_pelanggaran')
                ->update(['nama_pelanggaran' => DB::raw('nama_jenis')]);
        }
    }

    private function expandLabelEnumsForMigration(): void
    {
        if (Schema::hasTable('label_perilakus') && Schema::hasColumn('label_perilakus', 'label_aktual')) {
            DB::statement(
                "ALTER TABLE `label_perilakus` MODIFY `label_aktual` ENUM('Baik','Perlu Pembinaan','Butuh Perhatian','Bermasalah') NOT NULL"
            );
        }

        if (! Schema::hasTable('klasifikasis')) {
            return;
        }

        if (Schema::hasColumn('klasifikasis', 'hasil_klasifikasi')) {
            DB::statement(
                "ALTER TABLE `klasifikasis` MODIFY `hasil_klasifikasi` ENUM('Baik','Perlu Pembinaan','Butuh Perhatian','Bermasalah') NOT NULL"
            );
        }

        foreach (['label_aktual', 'hasil_naive_bayes', 'hasil_ig_naive_bayes'] as $column) {
            if (Schema::hasColumn('klasifikasis', $column)) {
                DB::statement(
                    "ALTER TABLE `klasifikasis` MODIFY `{$column}` ENUM('Baik','Perlu Pembinaan','Butuh Perhatian','Bermasalah') NULL"
                );
            }
        }

        if (Schema::hasTable('jenis_pelanggarans') && Schema::hasColumn('jenis_pelanggarans', 'aspek_pelanggaran')) {
            DB::statement(
                "ALTER TABLE `jenis_pelanggarans` MODIFY `aspek_pelanggaran` ENUM('Kerajinan','Kelakuan','Kerapian','Kehadiran','Lainnya') NOT NULL DEFAULT 'Kelakuan'"
            );
        }
    }

    private function finalizeLabelEnums(): void
    {
        if (Schema::hasTable('label_perilakus') && Schema::hasColumn('label_perilakus', 'label_aktual')) {
            DB::statement(
                "ALTER TABLE `label_perilakus` MODIFY `label_aktual` ENUM('Baik','Butuh Perhatian','Bermasalah') NOT NULL"
            );
        }

        if (! Schema::hasTable('klasifikasis')) {
            return;
        }

        if (Schema::hasColumn('klasifikasis', 'hasil_klasifikasi')) {
            DB::statement(
                "ALTER TABLE `klasifikasis` MODIFY `hasil_klasifikasi` ENUM('Baik','Butuh Perhatian','Bermasalah') NOT NULL"
            );
        }

        foreach (['label_aktual', 'hasil_naive_bayes', 'hasil_ig_naive_bayes'] as $column) {
            if (Schema::hasColumn('klasifikasis', $column)) {
                DB::statement(
                    "ALTER TABLE `klasifikasis` MODIFY `{$column}` ENUM('Baik','Butuh Perhatian','Bermasalah') NULL"
                );
            }
        }
    }

    private function finalizeAspectEnum(): void
    {
        if (! Schema::hasTable('jenis_pelanggarans') || ! Schema::hasColumn('jenis_pelanggarans', 'aspek_pelanggaran')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `jenis_pelanggarans` MODIFY `aspek_pelanggaran` ENUM('Kerajinan','Kelakuan','Kerapian') NOT NULL DEFAULT 'Kelakuan'"
        );
    }
};
