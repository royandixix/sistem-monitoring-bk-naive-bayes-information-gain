<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('information_gain_results')) {
            Schema::create('information_gain_results', function (Blueprint $table): void {
                $table->id();
                $table->string('fitur');
                $table->decimal('gain', 14, 10)->default(0);
                $table->decimal('entropy_before', 14, 10)->default(0);
                $table->decimal('entropy_after', 14, 10)->default(0);
                $table->boolean('selected')->default(false);
                $table->string('metode')->default('Information Gain');
                $table->integer('jumlah_data')->default(0);
                $table->integer('ranking')->default(0);
                $table->json('detail')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('information_gain_results');
    }
};
