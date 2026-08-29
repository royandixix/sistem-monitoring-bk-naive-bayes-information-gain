<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IsiSemuaDataBK extends Command
{
    protected $signature = 'bk:isi-semua-data';

    protected $description = 'Mengisi tabel aplikasi BK berdasarkan data sumber penelitian';

    public function handle(): int
    {
        $this->info('============================================================');
        $this->info('ISI DATA APLIKASI BK');
        $this->info('============================================================');

        if (! Schema::hasTable('siswas')) {
            $this->error('Tabel siswas tidak ditemukan.');
            return self::FAILURE;
        }

        if (! Schema::hasTable('pelanggaran_imports')) {
            $this->error('Tabel pelanggaran_imports tidak ditemukan.');
            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            $guruBkId = DB::table('users')
                ->where('role', 'super_admin')
                ->value('id');

            $jenisRaw = $this->buatJenisDariExcel();

            $jenisCsv = $this->buatJenisReferensiCsv();

            $this->hubungkanSiswaStaging();

            $this->hubungkanJenisStaging();

            $laporan = $this->buatLaporanPelanggaran($guruBkId);

            $label = $this->buatLabelPerilaku();

            $penanganan = $this->buatPenanganan($guruBkId);

            DB::commit();

            $this->newLine();
            $this->info('============================================================');
            $this->info('HASIL');
            $this->info('============================================================');

            $this->table(
                ['Data', 'Jumlah'],
                [
                    ['Kelas', DB::table('kelas')->count()],
                    ['Siswa', DB::table('siswas')->count()],
                    ['Jenis Pelanggaran', DB::table('jenis_pelanggarans')->count()],
                    ['Laporan Pelanggaran', DB::table('pelanggarans')->count()],
                    ['Data Penanganan', DB::table('penanganans')->count()],
                    ['Label Perilaku', DB::table('label_perilakus')->count()],
                    ['Jenis dari Excel', $jenisRaw],
                    ['Jenis referensi CSV', $jenisCsv],
                    ['Laporan dibuat', $laporan],
                    ['Label dibuat', $label],
                    ['Penanganan dibuat', $penanganan],
                ]
            );

            $this->newLine();

            $this->warn(
                'Catatan: data penanganan otomatis adalah SIMULASI UI, bukan catatan penanganan asli Guru BK.'
            );

            $this->warn(
                'Label berasal dari Kategori_Perilaku_CSV dan masih memerlukan validasi Guru BK untuk ground truth final.'
            );

            $this->warn(
                'Poin jenis pelanggaran bersifat bobot sementara/referensi dan tidak mengubah poin_resmi pada tabel mapping.'
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function buatJenisDariExcel(): int
    {
        if (! Schema::hasTable('pelanggaran_mapping_refs')) {
            return 0;
        }

        $refs = DB::table('pelanggaran_mapping_refs')
            ->whereNotIn('status_validasi', [
                'diabaikan',
                'perlu_verifikasi',
            ])
            ->whereNotNull('aspek_validasi')
            ->orderBy('id')
            ->get();

        $count = 0;

        foreach ($refs as $ref) {
            $nama = trim((string) $ref->pelanggaran_normalized);

            if ($nama === '') {
                continue;
            }

            $aspek = ucfirst(
                strtolower(
                    trim((string) $ref->aspek_validasi)
                )
            );

            if (! in_array(
                $aspek,
                ['Kerajinan', 'Kelakuan', 'Kerapian'],
                true
            )) {
                continue;
            }

            $poin = $this->tentukanPoinSementara(
                $nama,
                $aspek
            );

            $tingkat = $this->tingkat($poin);

            $kode = 'EX' . str_pad(
                (string) $ref->id,
                3,
                '0',
                STR_PAD_LEFT
            );

            DB::table('jenis_pelanggarans')->updateOrInsert(
                [
                    'kode_jenis' => $kode,
                ],
                [
                    'nama_jenis' => $nama,
                    'aspek_pelanggaran' => $aspek,
                    'tingkat_pelanggaran' => $tingkat,
                    'poin' => $poin,
                    'keterangan' => 'Sumber Excel penelitian. Bobot sementara untuk operasional aplikasi; bukan poin resmi sekolah.',
                    'kode_pelanggaran' => $kode,
                    'nama_pelanggaran' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $count++;
        }

        return $count;
    }

    private function buatJenisReferensiCsv(): int
    {
        $data = [
            ['CSVK01', 'Alfa 1 hari', 'Kerajinan', 5],
            ['CSVK02', 'Alfa 10 hari berturut-turut', 'Kerajinan', 27],
            ['CSVK03', 'Alfa 3 hari berturut-turut', 'Kerajinan', 13],
            ['CSVK04', 'Keluar kelas tanpa izin', 'Kerajinan', 1],
            ['CSVK05', 'Mengganggu KBM', 'Kerajinan', 3],
            ['CSVK06', 'Meninggalkan jam pelajaran tanpa ket', 'Kerajinan', 8],
            ['CSVK07', 'Tidak ikuti pelajaran wajib', 'Kerajinan', 4],
            ['CSVK08', 'Tidak kerjakan tugas', 'Kerajinan', 2],

            ['CSVL01', 'Bawa kendaraan ke sekolah', 'Kelakuan', 11],
            ['CSVL02', 'Berkelahi bawa orang luar', 'Kelakuan', 27],
            ['CSVL03', 'Berolahraga pakai seragam olahraga', 'Kelakuan', 3],
            ['CSVL04', 'Berpacaran di sekolah', 'Kelakuan', 9],
            ['CSVL05', 'Buang sampah sembarangan', 'Kelakuan', 2],
            ['CSVL06', 'Makan/minum di kelas', 'Kelakuan', 1],
            ['CSVL07', 'Melompat pagar/jendela', 'Kelakuan', 8],
            ['CSVL08', 'Mencoret sarana sekolah', 'Kelakuan', 4],
            ['CSVL09', 'Mencuri di sekolah', 'Kelakuan', 31],
            ['CSVL10', 'Merusak sarana sekolah', 'Kelakuan', 10],
            ['CSVL11', 'Pakai HP saat KBM tanpa izin', 'Kelakuan', 4],
            ['CSVL12', 'Premanisme/pemerasan', 'Kelakuan', 17],

            ['CSVR01', 'Atribut kurang (lambang, topi, dasi, kaos kaki)', 'Kerapian', 1],
            ['CSVR02', 'Putra pakai aksesoris wanita', 'Kerapian', 3],
            ['CSVR03', 'Putri makeup/perhiasan berlebihan', 'Kerapian', 3],
            ['CSVR04', 'Rambut gondrong/disemir', 'Kerapian', 5],
            ['CSVR05', 'Seragam tidak sesuai ketentuan', 'Kerapian', 4],
        ];

        foreach ($data as [$kode, $nama, $aspek, $poin]) {
            DB::table('jenis_pelanggarans')->updateOrInsert(
                [
                    'kode_jenis' => $kode,
                ],
                [
                    'nama_jenis' => $nama,
                    'aspek_pelanggaran' => $aspek,
                    'tingkat_pelanggaran' => $this->tingkat($poin),
                    'poin' => $poin,
                    'keterangan' => 'Referensi dari Dataset_500_Siswa_BK_SMP_Frater_Lengkap.csv. Belum ditetapkan sebagai poin resmi Guru BK.',
                    'kode_pelanggaran' => $kode,
                    'nama_pelanggaran' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return count($data);
    }

    private function tentukanPoinSementara(
        string $nama,
        string $aspek
    ): int {
        $n = strtoupper($nama);

        if ($aspek === 'Kerajinan') {
            if (
                str_contains($n, 'TELAT') ||
                str_contains($n, 'TERLAMBAT')
            ) {
                return 2;
            }

            return 2;
        }

        if ($aspek === 'Kelakuan') {
            return 4;
        }

        if (str_contains($n, 'RAMBUT')) {
            return 5;
        }

        if (
            str_contains($n, 'SERAGAM') ||
            str_contains($n, 'BAJU') ||
            str_contains($n, 'CELANA') ||
            str_contains($n, 'ROK')
        ) {
            return 4;
        }

        if (
            str_contains($n, 'MAKE UP') ||
            str_contains($n, 'LIP BALM') ||
            str_contains($n, 'LIPBALM') ||
            str_contains($n, 'AKSESORIS') ||
            str_contains($n, 'GELANG') ||
            str_contains($n, 'CINCIN') ||
            str_contains($n, 'RING')
        ) {
            return 3;
        }

        return 1;
    }

    private function tingkat(int $poin): string
    {
        if ($poin <= 4) {
            return 'Ringan';
        }

        if ($poin <= 15) {
            return 'Sedang';
        }

        return 'Berat';
    }

    private function hubungkanSiswaStaging(): void
    {
        $siswas = DB::table('siswas')
            ->join(
                'kelas',
                'kelas.id',
                '=',
                'siswas.kelas_id'
            )
            ->select([
                'siswas.id',
                'siswas.nama',
                'kelas.nama_kelas',
                'kelas.kode_kelas',
            ])
            ->get();

        $map = [];

        foreach ($siswas as $siswa) {
            $kelas = $this->normalizeKelas(
                $siswa->kode_kelas
                    ?: $siswa->nama_kelas
            );

            $nama = $this->normalizeNama(
                $siswa->nama
            );

            $map[$kelas . '|' . $nama] = $siswa->id;
        }

        DB::table('pelanggaran_imports')
            ->whereNull('siswa_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($map) {
                foreach ($rows as $row) {
                    $key =
                        $this->normalizeKelas($row->kelas_raw)
                        . '|'
                        . $this->normalizeNama($row->nama_normalized);

                    if (! isset($map[$key])) {
                        continue;
                    }

                    DB::table('pelanggaran_imports')
                        ->where('id', $row->id)
                        ->update([
                            'siswa_id' => $map[$key],
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function hubungkanJenisStaging(): void
    {
        $jenis = DB::table('jenis_pelanggarans')
            ->where('kode_jenis', 'like', 'EX%')
            ->get();

        foreach ($jenis as $row) {
            $nama = strtoupper(
                trim((string) $row->nama_jenis)
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

    private function buatLaporanPelanggaran(
        ?int $guruBkId
    ): int {
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

        $count = 0;

        foreach ($rows as $row) {
            $tanggal = substr(
                (string) $row->tanggal,
                0,
                10
            );

            $keterangan =
                '[IMPORT EXCEL] '
                . $row->source_file
                . ' | Sheet '
                . $row->source_sheet
                . ' | Baris '
                . $row->source_row
                . ' | Item '
                . $row->item_index;

            $exists = DB::table('pelanggarans')
                ->where('siswa_id', $row->siswa_id)
                ->where(
                    'jenis_pelanggaran_id',
                    $row->jenis_pelanggaran_id
                )
                ->where('tanggal', $tanggal)
                ->where('keterangan', $keterangan)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('pelanggarans')->insert([
                'siswa_id' => $row->siswa_id,
                'jenis_pelanggaran_id' => $row->jenis_pelanggaran_id,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'semester' => $row->semester ?: 'Ganjil',
                'tahun_ajaran' => $row->tahun_ajaran ?: '2025/2026',
                'status_pengajuan' => 'disetujui',
                'diajukan_oleh' => $guruBkId,
                'diproses_oleh' => $guruBkId,
                'diproses_pada' => now(),
                'catatan_verifikasi' => 'Sumber transaksi berasal dari file Excel penelitian.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function buatLabelPerilaku(): int
    {
        $path = storage_path(
            'app/import/csv/Audit_500_Siswa_vs_Excel.csv'
        );

        if (! file_exists($path)) {
            return 0;
        }

        $siswas = DB::table('siswas')
            ->join(
                'kelas',
                'kelas.id',
                '=',
                'siswas.kelas_id'
            )
            ->select([
                'siswas.id',
                'siswas.nama',
                'kelas.nama_kelas',
                'kelas.kode_kelas',
            ])
            ->get();

        $map = [];

        foreach ($siswas as $siswa) {
            $kelas = $this->normalizeKelas(
                $siswa->kode_kelas
                    ?: $siswa->nama_kelas
            );

            $nama = $this->normalizeNama(
                $siswa->nama
            );

            $map[$kelas . '|' . $nama] = $siswa->id;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return 0;
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);
            return 0;
        }

        $header = array_map(
            fn ($x) => trim((string) $x),
            $header
        );

        $count = 0;

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($header)) {
                continue;
            }

            $row = array_combine(
                $header,
                $values
            );

            if (! $row) {
                continue;
            }

            $status = trim(
                (string) (
                    $row['Status_Identitas']
                    ?? ''
                )
            );

            if (! in_array(
                $status,
                ['COCOK', 'DAPAT_DIPETAKAN'],
                true
            )) {
                continue;
            }

            $kelas = $this->normalizeKelas(
                $row['Kelas_Excel']
                    ?: $row['Kelas_CSV']
                    ?: ''
            );

            $nama = $this->normalizeNama(
                $row['Nama_Siswa']
                    ?? ''
            );

            $key = $kelas . '|' . $nama;

            if (! isset($map[$key])) {
                continue;
            }

            $label = trim(
                (string) (
                    $row['Kategori_Perilaku_CSV']
                    ?? ''
                )
            );

            if (! in_array(
                $label,
                [
                    'Baik',
                    'Butuh Perhatian',
                    'Bermasalah',
                ],
                true
            )) {
                continue;
            }

            DB::table('label_perilakus')
                ->updateOrInsert(
                    [
                        'siswa_id' => $map[$key],
                        'tahun_ajaran' => '2025/2026',
                        'semester' => 'Ganjil',
                    ],
                    [
                        'label_aktual' => $label,
                        'catatan' => '[SUMBER CSV] Kategori_Perilaku_CSV. Masih perlu validasi Guru BK sebelum digunakan sebagai ground truth final.',
                        'labeled_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            $count++;
        }

        fclose($handle);

        return $count;
    }

    private function buatPenanganan(
        ?int $guruBkId
    ): int {
        $rows = DB::table('pelanggarans')
            ->join(
                'jenis_pelanggarans',
                'jenis_pelanggarans.id',
                '=',
                'pelanggarans.jenis_pelanggaran_id'
            )
            ->where(
                'pelanggarans.status_pengajuan',
                'disetujui'
            )
            ->select([
                'pelanggarans.id',
                'pelanggarans.siswa_id',
                'pelanggarans.tanggal',
                'jenis_pelanggarans.nama_jenis',
                'jenis_pelanggarans.aspek_pelanggaran',
                'jenis_pelanggarans.poin',
            ])
            ->orderBy('pelanggarans.siswa_id')
            ->orderByDesc('pelanggarans.tanggal')
            ->get()
            ->unique('siswa_id');

        $count = 0;

        foreach ($rows as $row) {
            if (
                DB::table('penanganans')
                    ->where(
                        'pelanggaran_id',
                        $row->id
                    )
                    ->exists()
            ) {
                continue;
            }

            $poin = (int) $row->poin;

            $tindakan = match (true) {
                $poin <= 1 => 'Teguran Lisan',
                $poin <= 4 => 'Teguran Tertulis',
                $poin <= 15 => 'Konseling',
                default => 'Pemanggilan Orang Tua',
            };

            DB::table('penanganans')->insert([
                'pelanggaran_id' => $row->id,
                'tindakan' => $tindakan,
                'tanggal_penanganan' => Carbon::parse(
                    $row->tanggal
                )->addDay()->toDateString(),
                'catatan' => '[SIMULASI UI] Penanganan dibuat otomatis berdasarkan tingkat pelanggaran agar modul aplikasi dapat diuji. Bukan catatan penanganan asli sekolah.',
                'user_id' => $guruBkId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function normalizeNama(
        ?string $nama
    ): string {
        $nama = strtoupper(
            trim((string) $nama)
        );

        $nama = preg_replace(
            '/^\d+\.\s*/',
            '',
            $nama
        );

        $nama = preg_replace(
            '/\s+/',
            ' ',
            $nama
        );

        return trim($nama);
    }

    private function normalizeKelas(
        ?string $kelas
    ): string {
        return strtoupper(
            preg_replace(
                '/[^0-9A-Z]/',
                '',
                (string) $kelas
            )
        );
    }
}
