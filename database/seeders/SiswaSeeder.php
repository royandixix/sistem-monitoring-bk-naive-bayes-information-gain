<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SiswaSeeder extends Seeder
{
    public function run():void
    {
        $tahunAjaran='2025/2026';

        $daftarNama=[
            'Andi Saputra',
            'Aurelia Putri',
            'Muhammad Fajar',
            'Cecilia Natalia',
            'Rizky Pratama',

            'Gabriel Alexander',
            'Nur Aisyah',
            'Jonathan Wijaya',
            'Maria Theresia',
            'Dimas Setiawan',

            'Yoseph Adrian',
            'Siti Rahma',
            'Ferdinand Gunawan',
            'Clara Angelina',
            'Arif Hidayat',

            'Rafael Christian',
            'Nabila Ramadhani',
            'Kevin Santoso',
            'Monica Patricia',
            'Reza Maulana',

            'Stefanus Michael',
            'Putri Maharani',
            'Aldi Kurniawan',
            'Felicia Amanda',
            'Ilham Akbar',

            'Fransiskus Xavier',
            'Dewi Lestari',
            'Bagas Prakoso',
            'Veronica Melinda',
            'Fadli Rahman',

            'Vincentius Daniel',
            'Intan Permata',
            'Agus Salim',
            'Theresia Gracia',
            'Rian Firmansyah',

            'Dominikus Andre',
            'Ayu Wulandari',
            'Bima Aditya',
            'Christina Olivia',
            'Akmal Fauzan',

            'Markus Sebastian',
            'Anisa Safitri',
            'Yoga Pranata',
            'Angela Clarissa',
            'Farhan Nugraha',
        ];

        $namaKelas=[
            'VII A',
            'VII B',
            'VII C',
            'VIII A',
            'VIII B',
            'VIII C',
            'IX A',
            'IX B',
            'IX C',
        ];

        $kelas=Kelas::query()
            ->where('tahun_ajaran',$tahunAjaran)
            ->whereIn('nama_kelas',$namaKelas)
            ->pluck('id','nama_kelas');

        if($kelas->count()<9){
            throw new RuntimeException(
                'Data kelas belum lengkap. Jalankan KelasSeeder terlebih dahulu.'
            );
        }

        DB::transaction(function()use(
            $daftarNama,
            $namaKelas,
            $kelas
        ):void{
            foreach($daftarNama as $index=>$nama){
                $kelasIndex=intdiv($index,5);

                $kelasNama=$namaKelas[$kelasIndex];

                $nis='25'.str_pad(
                    (string)($index+1),
                    4,
                    '0',
                    STR_PAD_LEFT
                );

                $jk=$index%2===0
                    ?'L'
                    :'P';

                Siswa::query()->updateOrCreate(
                    [
                        'nis'=>$nis,
                    ],
                    [
                        'nama'=>$nama,
                        'jk'=>$jk,
                        'kelas_id'=>$kelas[$kelasNama],
                        'tempat_lahir'=>'Makassar',
                        'tanggal_lahir'=>'2012-01-01',
                        'alamat'=>'Makassar',
                        'nama_ayah'=>'Orang Tua '.$nama,
                        'nama_ibu'=>'Orang Tua '.$nama,
                        'no_hp_ortu'=>'081234567890',
                        'status'=>'Aktif',
                    ]
                );
            }
        });

        $jumlah=Siswa::query()
            ->where('status','Aktif')
            ->count();

        $this->command?->info(
            'Seeder siswa selesai.'
        );

        $this->command?->line(
            'Jumlah siswa aktif: '.$jumlah
        );
    }
}