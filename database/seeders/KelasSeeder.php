<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run():void
    {
        $tahunAjaran='2025/2026';

        $kelas=[
            [
                'kode_kelas'=>'VII-A',
                'nama_kelas'=>'VII A',
            ],
            [
                'kode_kelas'=>'VII-B',
                'nama_kelas'=>'VII B',
            ],
            [
                'kode_kelas'=>'VII-C',
                'nama_kelas'=>'VII C',
            ],
            [
                'kode_kelas'=>'VIII-A',
                'nama_kelas'=>'VIII A',
            ],
            [
                'kode_kelas'=>'VIII-B',
                'nama_kelas'=>'VIII B',
            ],
            [
                'kode_kelas'=>'VIII-C',
                'nama_kelas'=>'VIII C',
            ],
            [
                'kode_kelas'=>'IX-A',
                'nama_kelas'=>'IX A',
            ],
            [
                'kode_kelas'=>'IX-B',
                'nama_kelas'=>'IX B',
            ],
            [
                'kode_kelas'=>'IX-C',
                'nama_kelas'=>'IX C',
            ],
        ];

        foreach($kelas as $item){
            Kelas::query()->updateOrCreate(
                [
                    'kode_kelas'=>$item['kode_kelas'],
                ],
                [
                    'nama_kelas'=>$item['nama_kelas'],
                    'tahun_ajaran'=>$tahunAjaran,
                ]
            );
        }

        $this->command?->info(
            count($kelas).' data kelas berhasil dibuat.'
        );
    }
}