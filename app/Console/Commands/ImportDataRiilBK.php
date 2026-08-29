<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

#[Signature('bk:import-data-riil')]
#[Description('Import data riil penelitian BK ke database utama')]
class ImportDataRiilBK extends Command
{
    private string $tahunAjaran = '2025/2026';

    public function handle(): int
    {
        $auditPath = storage_path('app/import/csv/Audit_500_Siswa_vs_Excel.csv');

        if (! file_exists($auditPath)) {
            $this->error("File tidak ditemukan: {$auditPath}");

            return self::FAILURE;
        }

        if (! Schema::hasTable('pelanggaran_imports')) {
            $this->error('Tabel pelanggaran_imports tidak ditemukan.');

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            $this->newLine();
            $this->info('IMPORT DATA RIIL BK SMP FRATER');
            $this->line(str_repeat('=', 60));

            $kelas = $this->importKelas();
            $siswa = $this->importSiswa($auditPath, $kelas);

            $this->hubungkanImportKeSiswa();

            $jenis = $this->importJenisPelanggaranTervalidasi();

            if ($jenis > 0) {
                $this->hubungkanImportKeJenis();
                $pelanggaran = $this->importPelanggaranRiil();
            } else {
                $pelanggaran = 0;
            }

            DB::commit();

            $this->newLine();
            $this->line(str_repeat('=', 60));
            $this->info('HASIL IMPORT');
            $this->line(str_repeat('=', 60));

            $this->table(
                ['Data', 'Jumlah'],
                [
                    ['Kelas', DB::table('kelas')->count()],
                    ['Siswa', DB::table('siswas')->count()],
                    ['Jenis Pelanggaran', DB::table('jenis_pelanggarans')->count()],
                    ['Pelanggaran', DB::table('pelanggarans')->count()],
                    ['Label Aktual', Schema::hasTable('label_perilakus') ? DB::table('label_perilakus')->count() : 0],
                    ['Siswa dibuat pada proses ini', $siswa],
                    ['Jenis valid dibuat', $jenis],
                    ['Pelanggaran valid dibuat', $pelanggaran],
                ]
            );

            $this->newLine();

            $belumSiswa = DB::table('pelanggaran_imports')
                ->whereNull('siswa_id')
                ->count();

            $belumJenis = DB::table('pelanggaran_imports')
                ->whereNull('jenis_pelanggaran_id')
                ->count();

            $this->line("Import staging belum terhubung siswa : {$belumSiswa}");
            $this->line("Import staging belum terhubung jenis : {$belumJenis}");

            $this->newLine();

            if ($jenis === 0) {
                $this->warn(
                    'Jenis pelanggaran belum dimasukkan karena poin_resmi belum tersedia. '
                    .'Importer sengaja tidak membuat poin palsu.'
                );
            }

            if (Schema::hasTable('label_perilakus')
                && DB::table('label_perilakus')->count() === 0) {
                $this->warn(
                    'Label aktual belum diisi. Label harus berasal dari validasi Guru BK, '
                    .'bukan dibuat otomatis dari total poin.'
                );
            }

            $kelakuan = DB::table('pelanggaran_mapping_refs')
                ->whereRaw('LOWER(aspek_validasi) = ?', ['kelakuan'])
                ->count();

            if ($kelakuan === 0) {
                $this->warn(
                    'Belum ada mapping pelanggaran aspek Kelakuan pada data sumber yang tervalidasi.'
                );
            }

            $this->newLine();
            $this->info('Import selesai tanpa membuat data random/demo.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::rollBack();

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function importKelas(): array
    {
        $namaKelas = [
            '7A', '7B', '7C', '7D', '7E',
            '8A', '8B', '8C', '8D', '8E',
            '9A', '9B', '9C', '9D', '9E',
        ];

        $hasil = [];

        foreach ($namaKelas as $nama) {
            $row = [
                'nama_kelas' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('kelas', 'kode_kelas')) {
                $row['kode_kelas'] = $nama;
            }

            if (Schema::hasColumn('kelas', 'tahun_ajaran')) {
                $row['tahun_ajaran'] = $this->tahunAjaran;
            }

            if (Schema::hasColumn('kelas', 'kode_kelas')) {
                DB::table('kelas')->updateOrInsert(
                    ['kode_kelas' => $nama],
                    $row
                );

                $id = DB::table('kelas')
                    ->where('kode_kelas', $nama)
                    ->value('id');
            } else {
                DB::table('kelas')->updateOrInsert(
                    ['nama_kelas' => $nama],
                    $row
                );

                $id = DB::table('kelas')
                    ->where('nama_kelas', $nama)
                    ->value('id');
            }

            $hasil[$nama] = $id;
        }

        return $hasil;
    }

    private function importSiswa(string $auditPath, array $kelas): int
    {
        $handle = fopen($auditPath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Audit CSV tidak dapat dibuka.');
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            throw new \RuntimeException('Header Audit CSV tidak ditemukan.');
        }

        $header = array_map(
            fn ($value) => trim((string) $value),
            $header
        );

        $inserted = 0;
        $processed = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($header)) {
                continue;
            }

            $row = array_combine($header, $data);

            if (! $row) {
                continue;
            }

            $status = trim((string) ($row['Status_Identitas'] ?? ''));

            if (! in_array($status, ['COCOK', 'DAPAT_DIPETAKAN'], true)) {
                continue;
            }

            $nama = $this->bersihkanNama(
                (string) ($row['Nama_Siswa'] ?? '')
            );

            if ($nama === '') {
                continue;
            }

            $kelasNama = trim(
                (string) (
                    $row['Kelas_Excel']
                    ?: $row['Kelas_CSV']
                    ?: ''
                )
            );

            $kelasNama = strtoupper(
                preg_replace('/[^0-9A-Z]/', '', $kelasNama)
            );

            if (! isset($kelas[$kelasNama])) {
                continue;
            }

            $key = $this->normalisasiNama($nama).'|'.$kelasNama;

            if (isset($processed[$key])) {
                continue;
            }

            $processed[$key] = true;

            $existing = DB::table('siswas')
                ->where('nama', $nama)
                ->where('kelas_id', $kelas[$kelasNama])
                ->first();

            if ($existing) {
                continue;
            }

            DB::table('siswas')->insert([
                'nis' => null,
                'nama' => $nama,
                'jk' => null,
                'kelas_id' => $kelas[$kelasNama],
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        fclose($handle);

        return $inserted;
    }

    private function hubungkanImportKeSiswa(): void
    {
        $siswas = DB::table('siswas')
            ->join('kelas', 'kelas.id', '=', 'siswas.kelas_id')
            ->select(
                'siswas.id',
                'siswas.nama',
                'kelas.nama_kelas',
                'kelas.kode_kelas'
            )
            ->get();

        foreach ($siswas as $siswa) {
            $nama = $this->normalisasiNama($siswa->nama);

            $kelas = strtoupper(
                preg_replace(
                    '/[^0-9A-Z]/',
                    '',
                    (string) ($siswa->kode_kelas ?: $siswa->nama_kelas)
                )
            );

            DB::table('pelanggaran_imports')
                ->whereRaw('UPPER(TRIM(nama_normalized)) = ?', [$nama])
                ->whereRaw(
                    "UPPER(REPLACE(REPLACE(kelas_raw, '-', ''), ' ', '')) = ?",
                    [$kelas]
                )
                ->update([
                    'siswa_id' => $siswa->id,
                    'updated_at' => now(),
                ]);
        }
    }

    private function importJenisPelanggaranTervalidasi(): int
    {
        if (! Schema::hasTable('pelanggaran_mapping_refs')) {
            return 0;
        }

        $refs = DB::table('pelanggaran_mapping_refs')
            ->whereNotNull('aspek_validasi')
            ->whereNotNull('poin_resmi')
            ->where('poin_resmi', '>', 0)
            ->whereNotIn(
                'status_validasi',
                ['diabaikan', 'perlu_verifikasi']
            )
            ->orderBy('id')
            ->get();

        $inserted = 0;

        foreach ($refs as $ref) {
            $aspek = ucfirst(
                strtolower(trim((string) $ref->aspek_validasi))
            );

            if (! in_array(
                $aspek,
                ['Kerajinan', 'Kelakuan', 'Kerapian'],
                true
            )) {
                continue;
            }

            $poin = (int) $ref->poin_resmi;

            $tingkat = match (true) {
                $poin <= 4 => 'Ringan',
                $poin <= 15 => 'Sedang',
                default => 'Berat',
            };

            $kode = 'IMP'.str_pad(
                (string) $ref->id,
                3,
                '0',
                STR_PAD_LEFT
            );

            DB::table('jenis_pelanggarans')->updateOrInsert(
                ['kode_jenis' => $kode],
                [
                    'nama_jenis' => $ref->pelanggaran_normalized,
                    'aspek_pelanggaran' => $aspek,
                    'tingkat_pelanggaran' => $tingkat,
                    'poin' => $poin,
                    'keterangan' => 'Diimpor dari hasil validasi data penelitian',
                    'kode_pelanggaran' => $kode,
                    'nama_pelanggaran' => $ref->pelanggaran_normalized,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $inserted++;
        }

        return $inserted;
    }

    private function hubungkanImportKeJenis(): void
    {
        $jenis = DB::table('jenis_pelanggarans')
            ->select(
                'id',
                'nama_jenis',
                'nama_pelanggaran'
            )
            ->get();

        foreach ($jenis as $row) {
            $nama = strtoupper(
                trim(
                    (string) (
                        $row->nama_pelanggaran
                        ?: $row->nama_jenis
                    )
                )
            );

            DB::table('pelanggaran_imports')
                ->whereRaw(
                    'UPPER(TRIM(pelanggaran_normalized)) = ?',
                    [$nama]
                )
                ->update([
                    'jenis_pelanggaran_id' => $row->id,
                    'updated_at' => now(),
                ]);
        }
    }

    private function importPelanggaranRiil(): int
    {
        $rows = DB::table('pelanggaran_imports')
            ->whereNotNull('siswa_id')
            ->whereNotNull('jenis_pelanggaran_id')
            ->whereNotNull('tanggal')
            ->whereNotIn(
                'status_mapping',
                ['noise', 'review']
            )
            ->orderBy('id')
            ->get();

        $inserted = 0;

        $guruBkId = DB::table('users')
            ->where('role', 'super_admin')
            ->value('id');

        foreach ($rows as $row) {
            $exists = DB::table('pelanggarans')
                ->where('siswa_id', $row->siswa_id)
                ->where(
                    'jenis_pelanggaran_id',
                    $row->jenis_pelanggaran_id
                )
                ->where('tanggal', $row->tanggal)
                ->where(
                    'keterangan',
                    'like',
                    '%Import sumber Excel%'
                )
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('pelanggarans')->insert([
                'siswa_id' => $row->siswa_id,
                'jenis_pelanggaran_id' => $row->jenis_pelanggaran_id,
                'tanggal' => $row->tanggal,
                'keterangan' => 'Import sumber Excel: '
                    .$row->source_file
                    .' / '
                    .$row->source_sheet
                    .' / baris '
                    .$row->source_row,
                'semester' => $row->semester,
                'tahun_ajaran' => $row->tahun_ajaran,
                'status_pengajuan' => 'Disetujui',
                'diajukan_oleh' => $guruBkId,
                'diproses_oleh' => $guruBkId,
                'diproses_pada' => now(),
                'catatan_verifikasi' => 'Data berasal dari sumber Excel penelitian dan hanya diimpor setelah mapping siswa dan jenis pelanggaran tersedia.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        return $inserted;
    }

    private function bersihkanNama(string $nama): string
    {
        $nama = preg_replace('/^\d+\.\s*/', '', trim($nama));
        $nama = preg_replace('/\s+/', ' ', $nama);

        return Str::title(strtolower($nama));
    }

    private function normalisasiNama(string $nama): string
    {
        $nama = preg_replace('/^\d+\.\s*/', '', trim($nama));
        $nama = preg_replace('/\s+/', ' ', $nama);

        return strtoupper($nama);
    }
}
