<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jenis_pelanggarans', 'aspek_pelanggaran')) {
            Schema::table('jenis_pelanggarans', function (Blueprint $table): void {
                $table->enum('aspek_pelanggaran', ['Kerajinan', 'Kelakuan', 'Kerapian'])->default('Kelakuan')->after('nama_jenis');
            });
        }

        if (! Schema::hasColumn('jenis_pelanggarans', 'tingkat_pelanggaran')) {
            Schema::table('jenis_pelanggarans', function (Blueprint $table): void {
                $table->enum('tingkat_pelanggaran', ['Ringan', 'Sedang', 'Berat'])->default('Ringan')->after('aspek_pelanggaran');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'label_aktual')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->enum('label_aktual', ['Baik', 'Butuh Perhatian', 'Bermasalah'])->nullable()->after('hasil_klasifikasi');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'hasil_naive_bayes')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->enum('hasil_naive_bayes', ['Baik', 'Butuh Perhatian', 'Bermasalah'])->nullable()->after('label_aktual');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'probabilitas_naive_bayes')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->double('probabilitas_naive_bayes', 10, 6)->nullable()->after('hasil_naive_bayes');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'hasil_ig_naive_bayes')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->enum('hasil_ig_naive_bayes', ['Baik', 'Butuh Perhatian', 'Bermasalah'])->nullable()->after('probabilitas_naive_bayes');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'probabilitas_ig_naive_bayes')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->double('probabilitas_ig_naive_bayes', 10, 6)->nullable()->after('hasil_ig_naive_bayes');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'probabilitas_detail')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->json('probabilitas_detail')->nullable()->after('probabilitas');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'fitur_klasifikasi')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->json('fitur_klasifikasi')->nullable()->after('probabilitas_detail');
            });
        }

        if (! Schema::hasColumn('klasifikasis', 'information_gain_detail')) {
            Schema::table('klasifikasis', function (Blueprint $table): void {
                $table->json('information_gain_detail')->nullable()->after('fitur_klasifikasi');
            });
        }
    }

    public function down(): void
    {
        foreach (['information_gain_detail', 'fitur_klasifikasi', 'probabilitas_detail', 'probabilitas_ig_naive_bayes', 'hasil_ig_naive_bayes', 'probabilitas_naive_bayes', 'hasil_naive_bayes', 'label_aktual'] as $column) {
            if (Schema::hasColumn('klasifikasis', $column)) {
                Schema::table('klasifikasis', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }

        foreach (['tingkat_pelanggaran', 'aspek_pelanggaran'] as $column) {
            if (Schema::hasColumn('jenis_pelanggarans', $column)) {
                Schema::table('jenis_pelanggarans', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
