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
        Schema::create('evaluasi_models', function (Blueprint $table) {
            $table->id();

            $table->string('metode'); // Naive Bayes / Naive Bayes + Information Gain

            $table->integer('jumlah_data_training');
            $table->integer('jumlah_data_testing');

            $table->decimal('akurasi', 5, 2)->default(0);
            $table->decimal('precision', 5, 2)->default(0);
            $table->decimal('recall', 5, 2)->default(0);
            $table->decimal('f1_score', 5, 2)->default(0);

            $table->text('confusion_matrix')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_models');
    }
};