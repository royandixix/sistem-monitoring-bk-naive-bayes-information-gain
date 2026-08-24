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
        Schema::create('klasifikasis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('jumlah_pelanggaran')->default(0);
            $table->integer('total_poin')->default(0);

            $table->enum('hasil_klasifikasi', [
                'Baik',
                'Butuh Perhatian',
                'Bermasalah'
            ]);

            $table->double('probabilitas', 10, 6)->nullable();

            $table->string('metode')->default('Naive Bayes + Information Gain');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasis');
    }
};