<?php

namespace App\Services;

use App\Models\Pelanggaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class PelanggaranReportService
{
    public function downloadPdf(array $filters = []): Response
    {
        $rows = $this->rows($filters);

        $pdf = Pdf::loadHtml(
            $this->buildPdfHtml($rows, $filters)
        )
        ->setPaper('a4', 'landscape');

        return $pdf->download(
            $this->filename($filters, 'pdf')
        );
    }


    public function downloadExcel(array $filters = []): Response
    {
        $rows = $this->rows($filters);

        $xml = $this->buildExcelXml($rows, $filters);

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' .
                $this->filename($filters, 'xls') . '"',
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


        if (!empty($filters['tahun_ajaran'])) {
            $query->where(
                'tahun_ajaran',
                $filters['tahun_ajaran']
            );
        }


        if (!empty($filters['semester'])) {
            $query->where(
                'semester',
                $filters['semester']
            );
        }


        if (!empty($filters['kelas_id'])) {
            $query->whereHas(
                'siswa',
                fn($q) =>
                    $q->where(
                        'kelas_id',
                        $filters['kelas_id']
                    )
            );
        }


        if (!empty($filters['siswa_id'])) {
            $query->where(
                'siswa_id',
                $filters['siswa_id']
            );
        }


        return $query;
    }


    private function buildPdfHtml(
        Collection $rows,
        array $filters
    ): string {

        $html = '
        <html>
        <head>

        <style>
            body {
                font-family: Arial;
                font-size: 11px;
            }

            h3 {
                text-align:center;
            }

            table {
                width:100%;
                border-collapse: collapse;
            }

            table, th, td {
                border:1px solid black;
            }

            th, td {
                padding:5px;
            }

            th {
                background:#eeeeee;
            }
        </style>

        </head>

        <body>

        <h3>
        LAPORAN DATA PELANGGARAN SISWA<br>
        SMP FRATER MAKASSAR
        </h3>


        <p>
        '.$this->filterSummary($filters).'
        </p>


        <table>

        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Aspek</th>
            <th>Jenis Pelanggaran</th>
            <th>Poin</th>
        </tr>
        ';


        foreach ($rows as $i => $row) {

            $html .= '

            <tr>

            <td>'.($i+1).'</td>

            <td>'.
            ($row->tanggal?->format('d-m-Y') ?? '-')
            .'</td>


            <td>'.
            ($row->siswa?->nis ?? '-')
            .'</td>


            <td>'.
            ($row->siswa?->nama ?? '-')
            .'</td>


            <td>'.
            ($row->siswa?->kelas?->nama_kelas ?? '-')
            .'</td>


            <td>'.
            ($row->jenisPelanggaran?->aspek_pelanggaran ?? '-')
            .'</td>


            <td>'.
            ($row->jenisPelanggaran?->nama_jenis ?? '-')
            .'</td>


            <td>'.
            ($row->jenisPelanggaran?->poin ?? 0)
            .'</td>


            </tr>';

        }


        $html .= '

        </table>

        </body>
        </html>
        ';


        return $html;
    }


    private function filterSummary(array $filters): string
    {
        $parts = [];


        if (!empty($filters['tahun_ajaran'])) {
            $parts[] =
                'Tahun Ajaran: ' .
                $filters['tahun_ajaran'];
        }


        if (!empty($filters['semester'])) {
            $parts[] =
                'Semester: ' .
                $filters['semester'];
        }


        return $parts
            ? implode(' | ', $parts)
            : 'Semua Data';
    }


    private function filename(
        array $filters,
        string $extension
    ): string {

        return 'laporan-pelanggaran.'
            .$extension;
    }


    private function buildExcelXml(
        Collection $rows,
        array $filters
    ): string {

        return '';
    }
}