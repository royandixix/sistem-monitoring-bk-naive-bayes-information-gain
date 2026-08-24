<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wali_murid_siswa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->string('hubungan', 30)
                ->default('Orang Tua/Wali');

            $table->boolean('is_primary')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'user_id',
                'siswa_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_murid_siswa');
    }
};