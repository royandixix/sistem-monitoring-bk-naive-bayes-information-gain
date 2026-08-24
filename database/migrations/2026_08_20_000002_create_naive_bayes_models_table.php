<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naive_bayes_models', function (Blueprint $table): void {
            $table->id();
            $table->string('tahun_ajaran', 20);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('metode', 50);
            $table->json('classes');
            $table->json('features');
            $table->json('model');
            $table->unsignedInteger('jumlah_data_training')->default(0);
            $table->decimal('training_ratio', 5, 4)->default(0.7000);
            $table->unsignedBigInteger('random_seed')->default(42);
            $table->timestamps();

            $table->unique(
                ['tahun_ajaran', 'semester', 'metode'],
                'nb_models_periode_metode_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naive_bayes_models');
    }
};
