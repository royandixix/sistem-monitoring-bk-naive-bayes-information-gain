<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->string('status_pengajuan', 20)
                ->default('menunggu')
                ->after('tahun_ajaran');

            $table->foreignId('diajukan_oleh')
                ->nullable()
                ->after('status_pengajuan')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('diproses_oleh')
                ->nullable()
                ->after('diajukan_oleh')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('diproses_pada')
                ->nullable()
                ->after('diproses_oleh');

            $table->text('catatan_verifikasi')
                ->nullable()
                ->after('diproses_pada');

            $table->index(
                [
                    'status_pengajuan',
                    'tahun_ajaran',
                    'semester',
                ],
                'pelanggarans_status_periode_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pelanggarans', function (Blueprint $table) {
            $table->dropIndex(
                'pelanggarans_status_periode_index'
            );

            $table->dropConstrainedForeignId(
                'diproses_oleh'
            );

            $table->dropConstrainedForeignId(
                'diajukan_oleh'
            );

            $table->dropColumn([
                'status_pengajuan',
                'diproses_pada',
                'catatan_verifikasi',
            ]);
        });
    }
};