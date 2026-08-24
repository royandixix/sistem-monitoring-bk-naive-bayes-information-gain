<?php

namespace Database\Seeders;

use App\Models\JenisPelanggaran;
use App\Models\LabelPerilaku;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DataKlasifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaran = '2025/2026';
        $semester = 'Genap';
        $maksimalSiswa = 45;

        $guruBk = User::query()
            ->where('role', 'super_admin')
            ->orderBy('id')
            ->first();

        if (! $guruBk) {
            throw new RuntimeException(
                'Akun Guru BK belum tersedia. Jalankan UserSeeder terlebih dahulu.'
            );
        }

        $siswas = Siswa::query()
            ->where('status', 'Aktif')
            ->orderBy('id')
            ->limit($maksimalSiswa)
            ->get();

        if ($siswas->count() < 6) {
            throw new RuntimeException(
                'Minimal diperlukan 6 siswa aktif agar setiap kelas perilaku mempunyai sedikitnya 2 data.'
            );
        }

        $kodeJenis = [
            'JP001',
            'JP002',
            'JP003',
            'JP004',
            'JP005',
        ];

        $jenisPelanggarans = JenisPelanggaran::query()
            ->whereIn('kode_jenis', $kodeJenis)
            ->get()
            ->keyBy('kode_jenis');

        foreach ($kodeJenis as $kode) {
            if (! $jenisPelanggarans->has($kode)) {
                throw new RuntimeException(
                    "Jenis pelanggaran {$kode} belum tersedia. Jalankan JenisPelanggaranSeeder terlebih dahulu."
                );
            }
        }

        DB::transaction(function () use (
            $tahunAjaran,
            $semester,
            $siswas,
            $guruBk,
            $jenisPelanggarans
        ): void {
            Pelanggaran::query()
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->where(
                    'keterangan',
                    'like',
                    '[SEED-ML]%'
                )
                ->delete();

            foreach ($siswas as $index => $siswa) {
                $labelAktual = match ($index % 3) {
                    0 => 'Baik',
                    1 => 'Butuh Perhatian',
                    default => 'Bermasalah',
                };

                $catatan = match ($labelAktual) {
                    'Baik' =>
                        'Data demo: siswa menunjukkan perilaku baik dengan pelanggaran ringan yang rendah.',

                    'Butuh Perhatian' =>
                        'Data demo: siswa memiliki beberapa pelanggaran tingkat sedang dan membutuhkan perhatian Guru BK.',

                    'Bermasalah' =>
                        'Data demo: siswa memiliki pelanggaran dengan akumulasi poin tinggi dan membutuhkan penanganan lebih lanjut.',
                };

                LabelPerilaku::query()
                    ->updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'tahun_ajaran' => $tahunAjaran,
                            'semester' => $semester,
                        ],
                        [
                            'label_aktual' => $labelAktual,
                            'catatan' => $catatan,
                            'labeled_by' => $guruBk->id,
                        ]
                    );

                $tanggalDasar = Carbon::create(
                    2025,
                    8,
                    1
                )->addDays($index * 2);

                $polaPelanggaran = match ($labelAktual) {
                    'Baik' => [
                        'JP001',
                        'JP002',
                    ],

                    'Butuh Perhatian' => [
                        'JP003',
                        'JP002',
                        'JP002',
                    ],

                    'Bermasalah' => [
                        'JP004',
                        'JP004',
                        'JP005',
                        'JP002',
                        'JP002',
                        'JP002',
                        'JP002',
                        'JP002',
                        'JP002',
                    ],
                };

                foreach (
                    $polaPelanggaran as
                    $urutan => $kodeJenisPelanggaran
                ) {
                    $jenisPelanggaran =
                        $jenisPelanggarans[
                            $kodeJenisPelanggaran
                        ];

                    Pelanggaran::query()
                        ->create([
                            'siswa_id' =>
                                $siswa->id,

                            'jenis_pelanggaran_id' =>
                                $jenisPelanggaran->id,

                            'tanggal' =>
                                $tanggalDasar
                                    ->copy()
                                    ->addDays($urutan)
                                    ->format('Y-m-d'),

                            'semester' =>
                                $semester,

                            'tahun_ajaran' =>
                                $tahunAjaran,

                            'keterangan' =>
                                '[SEED-ML] Data demo klasifikasi '.
                                $labelAktual,

                            'diajukan_oleh' =>
                                $guruBk->id,

                            'status_pengajuan' =>
                                Pelanggaran::STATUS_DISETUJUI,

                            'diproses_oleh' =>
                                $guruBk->id,

                            'diproses_pada' =>
                                now(),

                            'catatan_verifikasi' =>
                                'Data demo dibuat otomatis oleh DataKlasifikasiSeeder.',
                        ]);
                }
            }
        });

        $jumlahBaik = LabelPerilaku::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('label_aktual', 'Baik')
            ->count();

        $jumlahButuhPerhatian =
            LabelPerilaku::query()
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
                    'Butuh Perhatian'
                )
                ->count();

        $jumlahBermasalah =
            LabelPerilaku::query()
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
                    'Bermasalah'
                )
                ->count();

        $totalPelanggaranDemo =
            Pelanggaran::query()
                ->where(
                    'tahun_ajaran',
                    $tahunAjaran
                )
                ->where(
                    'semester',
                    $semester
                )
                ->where(
                    'keterangan',
                    'like',
                    '[SEED-ML]%'
                )
                ->count();

        $this->command?->newLine();

        $this->command?->info(
            'DATA KLASIFIKASI DEMO BERHASIL DIBUAT'
        );

        $this->command?->line(
            'Tahun Ajaran: '.
            $tahunAjaran
        );

        $this->command?->line(
            'Semester: '.
            $semester
        );

        $this->command?->newLine();

        $this->command?->line(
            'Baik: '.
            $jumlahBaik.
            ' siswa'
        );

        $this->command?->line(
            'Butuh Perhatian: '.
            $jumlahButuhPerhatian.
            ' siswa'
        );

        $this->command?->line(
            'Bermasalah: '.
            $jumlahBermasalah.
            ' siswa'
        );

        $this->command?->line(
            'Total data pelanggaran demo: '.
            $totalPelanggaranDemo
        );

        $this->command?->newLine();

        $this->command?->info(
            'Dataset siap diproses dengan rasio 70% training dan 30% testing.'
        );
    }
}