<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_imports', function (Blueprint $table) {
            $table->id();

            $table->string('source_file');
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->unsignedInteger('item_index')->default(1);

            $table->dateTime('tanggal')->nullable();

            $table->string('nama_raw');
            $table->string('nama_normalized');
            $table->string('kelas_raw');

            $table->string('pelanggaran_raw');
            $table->string('pelanggaran_normalized');

            $table->enum('semester', [
                'Ganjil',
                'Genap',
            ]);

            $table->string('tahun_ajaran')
                ->default('2025/2026');

            $table->foreignId('siswa_id')
                ->nullable()
                ->constrained('siswas')
                ->nullOnDelete();

            $table->foreignId('jenis_pelanggaran_id')
                ->nullable()
                ->constrained('jenis_pelanggarans')
                ->nullOnDelete();

            $table->string('status_mapping')
                ->default('belum_dipetakan');

            $table->timestamps();

            $table->unique([
                'source_file',
                'source_sheet',
                'source_row',
                'item_index',
            ], 'pelanggaran_import_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_imports');
    }
};