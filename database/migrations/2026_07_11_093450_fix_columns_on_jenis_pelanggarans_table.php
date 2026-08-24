<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_pelanggarans')) {
            return;
        }

        Schema::table('jenis_pelanggarans', function (Blueprint $table) {
            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'kode_pelanggaran'
            )) {
                $table
                    ->string('kode_pelanggaran', 20)
                    ->nullable()
                    ->unique();
            }

            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'nama_pelanggaran'
            )) {
                $table
                    ->string('nama_pelanggaran', 150)
                    ->nullable();
            }

            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'aspek_pelanggaran'
            )) {
                $table
                    ->string('aspek_pelanggaran', 100)
                    ->nullable();
            }

            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'tingkat_pelanggaran'
            )) {
                $table
                    ->string('tingkat_pelanggaran', 50)
                    ->nullable();
            }

            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'poin'
            )) {
                $table
                    ->unsignedInteger('poin')
                    ->default(0);
            }

            if (! Schema::hasColumn(
                'jenis_pelanggarans',
                'keterangan'
            )) {
                $table
                    ->text('keterangan')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        /*
         * Dibiarkan kosong agar rollback tidak menghapus
         * kolom lama yang mungkin sudah digunakan.
         */
    }
};