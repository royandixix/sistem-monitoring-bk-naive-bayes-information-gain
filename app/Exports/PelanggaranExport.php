<?php

namespace App\Exports;

use App\Models\Pelanggaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PelanggaranExport implements FromCollection, WithHeadings
{
    protected array $filters;


    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }


    public function collection(): Collection
    {
        $query = Pelanggaran::with([
            'siswa.kelas',
            'jenisPelanggaran'
        ]);


        // Filter Tahun Ajaran
        if (!empty($this->filters['tahun_ajaran'])) {

            $query->where(
                'tahun_ajaran',
                $this->filters['tahun_ajaran']
            );

        }


        // Filter Semester
        if (!empty($this->filters['semester'])) {

            $query->where(
                'semester',
                $this->filters['semester']
            );

        }


        // Filter Kelas
        if (!empty($this->filters['kelas_id'])) {

            $query->whereHas('siswa', function ($q) {

                $q->where(
                    'kelas_id',
                    $this->filters['kelas_id']
                );

            });

        }


        return $query->get()->map(function ($item) {

            return [

                'NIS' =>
                    $item->siswa->nis ?? '-',

                'Nama Siswa' =>
                    $item->siswa->nama ?? '-',

                'Kelas' =>
                    $item->siswa->kelas->nama_kelas ?? '-',

                'Jenis Pelanggaran' =>
                    $item->jenisPelanggaran->nama_jenis ?? '-',

                'Aspek' =>
                    $item->aspek ?? '-',

                'Tingkat' =>
                    $item->tingkat ?? '-',

                'Poin' =>
                    $item->poin ?? 0,

                'Tanggal Kejadian' =>
                    $item->tanggal_kejadian ?? '-',

                'Tahun Ajaran' =>
                    $item->tahun_ajaran ?? '-',

                'Semester' =>
                    $item->semester ?? '-',

            ];

        });
    }


    public function headings(): array
    {
        return [

            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jenis Pelanggaran',
            'Aspek',
            'Tingkat',
            'Poin',
            'Tanggal Kejadian',
            'Tahun Ajaran',
            'Semester',

        ];
    }
}