<?php

namespace App\Services;

use App\Models\Pelanggaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class PelanggaranReportService
{
    public function downloadExcel(array $filters = []): Response
    {
        $rows = $this->rows($filters);
        $xml = $this->buildExcelXml($rows, $filters);
        $filename = $this->filename($filters, 'xls');

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }

    public function downloadPdf(array $filters = []): Response
    {
        $rows = $this->rows($filters);
        $pdf = $this->buildSimplePdf($rows, $filters);
        $filename = $this->filename($filters, 'pdf');

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }

    private function rows(array $filters): Collection
    {
        return $this->query($filters)->get();
    }

    private function query(array $filters): Builder
    {
        $query = Pelanggaran::query()
            ->disetujui()
            ->with([
                'siswa.kelas',
                'jenisPelanggaran',
                'diajukanOleh',
                'diprosesOleh',
            ])
            ->orderBy('tanggal')
            ->orderBy('id');

        if ($tahunAjaran = trim((string) ($filters['tahun_ajaran'] ?? ''))) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }

        if ($semester = trim((string) ($filters['semester'] ?? ''))) {
            $query->where('semester', $semester);
        }

        if ($kelasId = (int) ($filters['kelas_id'] ?? 0)) {
            $query->whereHas(
                'siswa',
                fn (Builder $siswaQuery): Builder =>
                    $siswaQuery->where('kelas_id', $kelasId)
            );
        }

        if ($siswaId = (int) ($filters['siswa_id'] ?? 0)) {
            $query->where('siswa_id', $siswaId);
        }

        return $query;
    }

    private function buildExcelXml(Collection $rows, array $filters): string
    {
        $cell = static fn (mixed $value): string =>
            '<Cell><Data ss:Type="String">'.htmlspecialchars(
                (string) $value,
                ENT_XML1 | ENT_QUOTES,
                'UTF-8'
            ).'</Data></Cell>';

        $output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $output .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $output .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        $output .= 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'."\n";
        $output .= '<Worksheet ss:Name="Laporan Pelanggaran"><Table>'."\n";

        $output .= '<Row>'.$cell('LAPORAN DATA PELANGGARAN SISWA').'</Row>'."\n";
        $output .= '<Row>'.$cell('SMP Frater Makassar').'</Row>'."\n";
        $output .= '<Row>'.$cell($this->filterSummary($filters)).'</Row>'."\n";
        $output .= '<Row>'.$cell('Total Data: '.$rows->count()).'</Row>'."\n";
        $output .= '<Row></Row>'."\n";

        $headers = [
            'No',
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Aspek',
            'Tingkat',
            'Jenis Pelanggaran',
            'Poin',
            'Semester',
            'Tahun Ajaran',
            'Keterangan',
        ];

        $output .= '<Row>'.implode('', array_map($cell, $headers)).'</Row>'."\n";

        foreach ($rows as $index => $record) {
            $values = [
                $index + 1,
                $record->tanggal?->format('d-m-Y') ?? '-',
                $record->siswa?->nis ?? '-',
                $record->siswa?->nama ?? '-',
                $record->siswa?->kelas?->nama_kelas ?? '-',
                $record->jenisPelanggaran?->aspek_pelanggaran ?? '-',
                $record->jenisPelanggaran?->tingkat_pelanggaran ?? '-',
                $record->jenisPelanggaran?->nama_jenis ?? '-',
                (int) ($record->jenisPelanggaran?->poin ?? 0),
                $record->semester ?? '-',
                $record->tahun_ajaran ?? '-',
                $record->keterangan ?? '-',
            ];

            $output .= '<Row>'.implode('', array_map($cell, $values)).'</Row>'."\n";
        }

        $output .= '</Table></Worksheet></Workbook>';

        return $output;
    }

    private function buildSimplePdf(Collection $rows, array $filters): string
    {
        $lines = [
            'LAPORAN DATA PELANGGARAN SISWA',
            'SMP FRATER MAKASSAR',
            $this->filterSummary($filters),
            'Total Data: '.$rows->count(),
            str_repeat('-', 110),
            sprintf(
                '%-3s %-10s %-10s %-22s %-8s %-11s %-7s %-25s %4s',
                'No', 'Tanggal', 'NIS', 'Nama', 'Kelas', 'Aspek', 'Tingkat', 'Pelanggaran', 'Poin'
            ),
            str_repeat('-', 110),
        ];

        foreach ($rows as $index => $record) {
            $lines[] = sprintf(
                '%-3s %-10s %-10s %-22s %-8s %-11s %-7s %-25s %4d',
                $index + 1,
                $record->tanggal?->format('d-m-Y') ?? '-',
                $this->clip($record->siswa?->nis ?? '-', 10),
                $this->clip($record->siswa?->nama ?? '-', 22),
                $this->clip($record->siswa?->kelas?->nama_kelas ?? '-', 8),
                $this->clip($record->jenisPelanggaran?->aspek_pelanggaran ?? '-', 11),
                $this->clip($record->jenisPelanggaran?->tingkat_pelanggaran ?? '-', 7),
                $this->clip($record->jenisPelanggaran?->nama_jenis ?? '-', 25),
                (int) ($record->jenisPelanggaran?->poin ?? 0),
            );
        }

        if ($rows->isEmpty()) {
            $lines[] = 'Tidak ada data pelanggaran yang sesuai dengan filter.';
        }

        $lines[] = str_repeat('-', 110);
        $lines[] = 'Dicetak: '.now()->format('d-m-Y H:i:s');

        return $this->pdfFromLines($lines);
    }

    private function pdfFromLines(array $lines): string
    {
        $pages = array_chunk($lines, 48);
        $objects = [];

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';

        $kids = [];
        $nextId = 4;

        foreach ($pages as $pageLines) {
            $pageId = $nextId++;
            $contentId = $nextId++;
            $kids[] = $pageId.' 0 R';

            $stream = "BT\n/F1 7 Tf\n30 560 Td\n";

            foreach ($pageLines as $line) {
                $stream .= '('.$this->pdfEscape($line).") Tj\n0 -11 Td\n";
            }

            $stream .= "ET\n";

            $objects[$contentId] =
                '<< /Length '.strlen($stream)." >>\nstream\n".$stream."endstream";

            $objects[$pageId] =
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] '.
                '/Resources << /Font << /F1 3 0 R >> >> '.
                '/Contents '.$contentId.' 0 R >>';
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$body."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxId + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }

        $pdf .= "trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $value): string
    {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $converted = $converted === false ? $value : $converted;

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $converted
        );
    }

    private function clip(string $value, int $length): string
    {
        return mb_strimwidth($value, 0, $length, '');
    }

    private function filterSummary(array $filters): string
    {
        $parts = [];

        if (! empty($filters['tahun_ajaran'])) {
            $parts[] = 'Tahun Ajaran: '.$filters['tahun_ajaran'];
        }

        if (! empty($filters['semester'])) {
            $parts[] = 'Semester: '.$filters['semester'];
        }

        if (! empty($filters['kelas_id'])) {
            $kelas = \App\Models\Kelas::query()->find((int) $filters['kelas_id']);
            $parts[] = 'Kelas: '.($kelas?->nama_kelas ?? '-');
        }

        if (! empty($filters['siswa_id'])) {
            $siswa = \App\Models\Siswa::query()->find((int) $filters['siswa_id']);
            $parts[] = 'Siswa: '.($siswa?->nama ?? '-');
        }

        return $parts ? implode(' | ', $parts) : 'Semua data pelanggaran resmi';
    }

    private function filename(array $filters, string $extension): string
    {
        $parts = ['laporan-pelanggaran'];

        if (! empty($filters['tahun_ajaran'])) {
            $parts[] = str_replace('/', '-', (string) $filters['tahun_ajaran']);
        }

        if (! empty($filters['semester'])) {
            $parts[] = strtolower((string) $filters['semester']);
        }

        return implode('-', $parts).'.'.$extension;
    }
}
