<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ImportPelanggaranExcel extends Command
{
    protected $signature = 'bk:import-excel
        {file}
        {semester}
        {--tahun=2025/2026}';

    protected $description = 'Import Excel pelanggaran asli ke pelanggaran_imports';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        $semester = ucfirst(strtolower((string) $this->argument('semester')));
        $tahunAjaran = (string) $this->option('tahun');

        if (! in_array($semester, ['Ganjil', 'Genap'], true)) {
            $this->error('Semester harus Ganjil atau Genap.');

            return self::FAILURE;
        }

        if (! str_starts_with($file, '/')) {
            $file = base_path($file);
        }

        if (! is_file($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        $sourceFile = basename($file);
        $spreadsheet = IOFactory::load($file);

        $total = 0;
        $jumlahSheet = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $sheetName = strtoupper(trim($sheet->getTitle()));

            if (! preg_match('/^[789][A-E]$/', $sheetName)) {
                continue;
            }

            $headerRow = $this->findHeaderRow($sheet);

            if ($headerRow === null) {
                $this->warn("Header tidak ditemukan: {$sheetName}");
                continue;
            }

            $jumlahSheet++;
            $sheetTotal = 0;

            for (
                $row = $headerRow + 1;
                $row <= $sheet->getHighestDataRow();
                $row++
            ) {
                $tanggalRaw = $sheet->getCell("A{$row}")->getValue();
                $namaRaw = trim((string) $sheet->getCell("B{$row}")->getValue());
                $kelasRaw = trim((string) $sheet->getCell("C{$row}")->getValue());
                $jenisRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
                $lainnyaRaw = trim((string) $sheet->getCell("E{$row}")->getValue());

                if ($namaRaw === '') {
                    continue;
                }

                if ($kelasRaw === '') {
                    $kelasRaw = $sheetName;
                }

                $items = array_merge(
                    $this->splitPelanggaran($jenisRaw),
                    $this->splitPelanggaran($lainnyaRaw)
                );

                if ($items === []) {
                    continue;
                }

                $tanggal = $this->parseTanggal($tanggalRaw);
                $itemIndex = 0;

                foreach ($items as $pelanggaranRaw) {
                    $itemIndex++;

                    DB::table('pelanggaran_imports')->updateOrInsert(
                        [
                            'source_file' => $sourceFile,
                            'source_sheet' => $sheetName,
                            'source_row' => $row,
                            'item_index' => $itemIndex,
                        ],
                        [
                            'tanggal' => $tanggal,
                            'nama_raw' => $namaRaw,
                            'nama_normalized' => $this->normalize($namaRaw),
                            'kelas_raw' => strtoupper($kelasRaw),
                            'pelanggaran_raw' => $pelanggaranRaw,
                            'pelanggaran_normalized' => $this->normalize($pelanggaranRaw),
                            'semester' => $semester,
                            'tahun_ajaran' => $tahunAjaran,
                            'siswa_id' => null,
                            'jenis_pelanggaran_id' => null,
                            'status_mapping' => 'belum_dipetakan',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    $sheetTotal++;
                    $total++;
                }
            }

            $this->line("{$sheetName}: {$sheetTotal} item");
        }

        $this->newLine();
        $this->info('IMPORT SELESAI');
        $this->line("File         : {$sourceFile}");
        $this->line("Semester     : {$semester}");
        $this->line("Tahun ajaran : {$tahunAjaran}");
        $this->line("Sheet        : {$jumlahSheet}");
        $this->line("Total item   : {$total}");

        return self::SUCCESS;
    }

    private function findHeaderRow($sheet): ?int
    {
        for ($row = 1; $row <= 10; $row++) {
            $value = strtoupper(
                trim((string) $sheet->getCell("A{$row}")->getValue())
            );

            if ($value === 'TIMESTAMP') {
                return $row;
            }
        }

        return null;
    }

    private function splitPelanggaran(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $items = preg_split('/[,;\n]+/u', $value) ?: [];

        return array_values(array_filter(array_map(
            fn ($item) => trim($item),
            $items
        )));
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? $value;

        return mb_strtoupper($value);
    }

    private function parseTanggal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject(
                    (float) $value
                )->format('Y-m-d H:i:s');
            }

            return Carbon::parse((string) $value)
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
}