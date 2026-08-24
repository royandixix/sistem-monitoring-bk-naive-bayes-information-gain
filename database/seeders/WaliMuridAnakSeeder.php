<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class WaliMuridAnakSeeder extends Seeder
{
    public function run(): void
    {
        $waliMurid = User::query()
            ->where('email', 'walimurid@gmail.com')
            ->where('role', 'wali_murid')
            ->first();

        if (! $waliMurid) {
            throw new RuntimeException(
                'Akun walimurid@gmail.com belum tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Anak milik akun Wali Murid
        |--------------------------------------------------------------------------
        | Dua anak berada di kelas VII A.
        | Satu anak berada di kelas VII B.
        | Satu anak berada di kelas VIII A.
        */
        $daftarAnak = [
            '2025001' => [
                'hubungan' => 'Orang Tua/Wali',
                'is_primary' => true,
            ],
            '2025002' => [
                'hubungan' => 'Orang Tua/Wali',
                'is_primary' => true,
            ],
            '2025006' => [
                'hubungan' => 'Orang Tua/Wali',
                'is_primary' => true,
            ],
            '2024001' => [
                'hubungan' => 'Orang Tua/Wali',
                'is_primary' => true,
            ],
        ];

        $siswas = Siswa::query()
            ->whereIn(
                'nis',
                array_keys($daftarAnak)
            )
            ->get()
            ->keyBy('nis');

        $nisTidakDitemukan = collect(
            array_keys($daftarAnak)
        )
            ->reject(
                fn (string $nis): bool =>
                    $siswas->has($nis)
            )
            ->values();

        if ($nisTidakDitemukan->isNotEmpty()) {
            throw new RuntimeException(
                'Siswa dengan NIS berikut belum ditemukan: ' .
                $nisTidakDitemukan->implode(', ')
            );
        }

        $dataPivot = [];

        foreach ($daftarAnak as $nis => $pivot) {
            $dataPivot[
                $siswas->get($nis)->id
            ] = $pivot;
        }

        /*
        |--------------------------------------------------------------------------
        | Hubungkan akun dengan seluruh anak
        |--------------------------------------------------------------------------
        */
        $waliMurid->anak()->sync($dataPivot);

        $this->command?->info(
            $waliMurid->name .
            ' berhasil dihubungkan dengan ' .
            count($dataPivot) .
            ' anak.'
        );
    }
}
