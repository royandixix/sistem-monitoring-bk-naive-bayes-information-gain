<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('jenis_pelanggaran_id')
                ->constrained('jenis_pelanggarans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->text('keterangan')->nullable();

            $table->enum('semester', [
                'Ganjil',
                'Genap'
            ]);

            $table->string('tahun_ajaran');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggarans');
    }
};