<?php

namespace App\Services;

use App\Models\Pelanggaran;
use App\Exports\PelanggaranExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class PelanggaranReportService
{

    /**
     * Export PDF
     */
    public function downloadPdf(array $filters = [])
    {
        $rows = $this->query($filters)->get();

        return Pdf::loadHtml(
            $this->buildPdf($rows, $filters)
        )
        ->setPaper('a4', 'landscape')
        ->download('laporan-pelanggaran.pdf');
    }



    /**
     * Export Excel
     */
    public function downloadExcel(array $filters = [])
    {
        return Excel::download(
            new PelanggaranExport($filters),
            'laporan-pelanggaran.xlsx'
        );
    }



    /**
     * Query laporan
     */
    private function query(array $filters): Builder
    {

        $query = Pelanggaran::query()
            ->where(
                'status_pengajuan',
                'disetujui'
            )
            ->with([
                'siswa.kelas',
                'jenisPelanggaran'
            ])
            ->orderBy(
                'tanggal',
                'asc'
            );



        // Filter Tahun Ajaran
        if (!empty($filters['tahun_ajaran'])) {

            $query->where(
                'tahun_ajaran',
                trim($filters['tahun_ajaran'])
            );

        }



        // Filter Semester
        if (!empty($filters['semester'])) {

            $query->where(
                'semester',
                trim($filters['semester'])
            );

        }



        // Filter Kelas
        if (!empty($filters['kelas_id'])) {

            $query->whereHas(
                'siswa',
                function ($q) use ($filters) {

                    $q->where(
                        'kelas_id',
                        $filters['kelas_id']
                    );

                }
            );

        }



        // Filter Siswa
        if (!empty($filters['siswa_id'])) {

            $query->where(
                'siswa_id',
                $filters['siswa_id']
            );

        }



        return $query;

    }




    /**
     * Template PDF
     */
    private function buildPdf(
        Collection $rows,
        array $filters
    ): string
    {

        $html = '
<html>

<head>

<style>

body{
    font-family: Arial;
    font-size:10px;
}

h2{
    text-align:center;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid black;
    padding:5px;
}

th{
    background:#ddd;
}

</style>

</head>


<body>


<h2>
LAPORAN DATA PELANGGARAN SISWA
<br>
SMP FRATER MAKASSAR
</h2>



<p>

Tahun Ajaran :
'.($filters['tahun_ajaran'] ?? 'Semua').'

<br>

Semester :
'.($filters['semester'] ?? 'Semua').'

</p>



<table>


<tr>

<th>No</th>
<th>Tanggal</th>
<th>NIS</th>
<th>Nama Siswa</th>
<th>Kelas</th>
<th>Jenis Pelanggaran</th>
<th>Poin</th>

</tr>

';



        if ($rows->count() == 0) {

            $html .= '

<tr>

<td colspan="7">

Tidak ada data pelanggaran

</td>

</tr>';

        }



        foreach ($rows as $i => $row) {

            $html .= '

<tr>


<td>
'.($i + 1).'
</td>


<td>
'.($row->tanggal?->format('d-m-Y') ?? '-').'
</td>


<td>
'.($row->siswa?->nis ?? '-').'
</td>


<td>
'.($row->siswa?->nama ?? '-').'
</td>


<td>
'.($row->siswa?->kelas?->nama_kelas ?? '-').'
</td>


<td>
'.($row->jenisPelanggaran?->nama_jenis ?? '-').'
</td>


<td>
'.($row->jenisPelanggaran?->poin ?? 0).'
</td>


</tr>';

        }



        $html .= '

</table>


</body>

</html>';



        return $html;

    }

}