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
        Schema::create('penanganans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggaran_id')
                ->constrained('pelanggarans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('tindakan', [
                'Teguran Lisan',
                'Teguran Tertulis',
                'Pemanggilan Orang Tua',
                'Konseling',
                'Skorsing',
                'Lainnya'
            ]);

            $table->date('tanggal_penanganan');

            $table->text('catatan')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanganans');
    }
};