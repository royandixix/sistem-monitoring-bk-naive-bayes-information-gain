<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_mapping_refs', function (Blueprint $table) {
            $table->id();
            $table->string('pelanggaran_normalized')->unique();
            $table->unsignedInteger('jumlah')->default(0);
            $table->string('aspek_awal')->nullable();
            $table->string('aspek_validasi')->nullable();
            $table->unsignedInteger('poin_resmi')->nullable();
            $table->string('status_validasi')->default('belum_divalidasi');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_mapping_refs');
    }
};
