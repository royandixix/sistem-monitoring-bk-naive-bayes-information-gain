<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LabelPerilakuSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaran = '2025/2026';

        $semesters = [
            'Ganjil',
            'Genap',
        ];

        if (!Schema::hasTable('label_perilakus')) {
            throw new RuntimeException(
                'Tabel label_perilakus belum tersedia. Jalankan php artisan migrate terlebih dahulu.'
            );
        }

        $guruBkId = User::query()
            ->where('role', 'super_admin')
            ->orderBy('id')
            ->value('id');

        if (!$guruBkId) {
            throw new RuntimeException(
                'Akun Guru BK dengan role super_admin belum tersedia.'
            );
        }

        $siswaTable = (new Siswa())->getTable();

        $kelasTable = (new Kelas())->getTable();

        $siswas = DB::table($siswaTable)
            ->join(
                $kelasTable,
                "{$kelasTable}.id",
                '=',
                "{$siswaTable}.kelas_id"
            )
            ->where(
                "{$kelasTable}.tahun_ajaran",
                $tahunAjaran
            )
            ->where(
                "{$siswaTable}.status",
                'Aktif'
            )
            ->orderBy(
                "{$siswaTable}.id"
            )
            ->select([
                "{$siswaTable}.id as siswa_id",
                "{$siswaTable}.nis",
                "{$siswaTable}.nama",
            ])
            ->get();

        if ($siswas->count() < 6) {
            throw new RuntimeException(
                'Minimal diperlukan 6 siswa aktif.'
            );
        }

        $catatan = [
            'Baik' =>
                'Siswa menunjukkan perilaku baik dan tidak memiliki catatan pelanggaran yang signifikan.',

            'Butuh Perhatian' =>
                'Siswa memiliki catatan perilaku yang memerlukan perhatian dan pembinaan dari Guru BK.',

            'Bermasalah' =>
                'Siswa memiliki catatan perilaku yang memerlukan penanganan dan pembinaan lebih lanjut.',
        ];

        DB::transaction(function () use (
            $siswas,
            $tahunAjaran,
            $semesters,
            $guruBkId,
            $catatan
        ): void {
            foreach ($semesters as $semester) {
                foreach ($siswas as $index => $siswa) {
                    $labelAktual = match ($index % 3) {
                        0 => 'Baik',
                        1 => 'Butuh Perhatian',
                        default => 'Bermasalah',
                    };

                    DB::table('label_perilakus')
                        ->updateOrInsert(
                            [
                                'siswa_id' =>
                                    $siswa->siswa_id,

                                'tahun_ajaran' =>
                                    $tahunAjaran,

                                'semester' =>
                                    $semester,
                            ],
                            [
                                'label_aktual' =>
                                    $labelAktual,

                                'catatan' =>
                                    $catatan[$labelAktual],

                                'labeled_by' =>
                                    $guruBkId,

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),
                            ]
                        );
                }
            }
        });

        $this->command?->newLine();

        $this->command?->info(
            'LABEL PERILAKU BERHASIL DIBUAT'
        );

        foreach ($semesters as $semester) {
            $this->command?->newLine();

            $this->command?->line(
                "Periode {$tahunAjaran} - {$semester}"
            );

            foreach ([
                'Baik',
                'Butuh Perhatian',
                'Bermasalah',
            ] as $label) {
                $jumlah = DB::table(
                    'label_perilakus'
                )
                    ->where(
                        'tahun_ajaran',
                        $tahunAjaran
                    )
                    ->where(
                        'semester',
                        $semester
                    )
                    ->where(
                        'label_aktual',
                        $label
                    )
                    ->count();

                $this->command?->line(
                    "{$label}: {$jumlah}"
                );
            }
        }

        $this->command?->newLine();

        $this->command?->info(
            'Data siap digunakan untuk klasifikasi Ganjil maupun Genap.'
        );
    }
}