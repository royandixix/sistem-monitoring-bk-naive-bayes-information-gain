<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisPelanggaranSeeder extends Seeder
{
    public function run(): void
    {
        $columns = Schema::getColumnListing(
            'jenis_pelanggarans'
        );

        $hasColumn = static function (
            string $column
        ) use ($columns): bool {
            return in_array(
                $column,
                $columns,
                true
            );
        };

        $jenisPelanggaran = [
            [
                'kode' => 'JP001',
                'nama' => 'Terlambat masuk sekolah',
                'aspek' => 'Kerajinan',
                'tingkat' => 'Ringan',
                'poin' => 2,
                'keterangan' =>
                    'Terlambat masuk sekolah kurang dari 15 menit.',
            ],
            [
                'kode' => 'JP002',
                'nama' => 'Tidak memakai atribut lengkap',
                'aspek' => 'Kerapian',
                'tingkat' => 'Ringan',
                'poin' => 3,
                'keterangan' =>
                    'Tidak menggunakan atribut seragam sekolah secara lengkap.',
            ],
            [
                'kode' => 'JP003',
                'nama' => 'Tidak mengerjakan tugas',
                'aspek' => 'Kerajinan',
                'tingkat' => 'Sedang',
                'poin' => 5,
                'keterangan' =>
                    'Tidak mengerjakan atau mengumpulkan tugas yang diberikan.',
            ],
            [
                'kode' => 'JP004',
                'nama' => 'Membolos',
                'aspek' => 'Kerajinan',
                'tingkat' => 'Sedang',
                'poin' => 10,
                'keterangan' =>
                    'Tidak mengikuti kegiatan pembelajaran tanpa izin.',
            ],
            [
                'kode' => 'JP005',
                'nama' => 'Berkelahi',
                'aspek' => 'Kelakuan',
                'tingkat' => 'Berat',
                'poin' => 20,
                'keterangan' =>
                    'Terlibat perkelahian di lingkungan sekolah.',
            ],
            [
                'kode' => 'JP006',
                'nama' => 'Merokok di lingkungan sekolah',
                'aspek' => 'Kelakuan',
                'tingkat' => 'Berat',
                'poin' => 25,
                'keterangan' =>
                    'Merokok atau membawa rokok di lingkungan sekolah.',
            ],
            [
                'kode' => 'JP007',
                'nama' => 'Merusak fasilitas sekolah',
                'aspek' => 'Kelakuan',
                'tingkat' => 'Berat',
                'poin' => 30,
                'keterangan' =>
                    'Merusak fasilitas atau sarana sekolah secara sengaja.',
            ],
            [
                'kode' => 'JP008',
                'nama' => 'Tidak mengikuti upacara',
                'aspek' => 'Kerajinan',
                'tingkat' => 'Ringan',
                'poin' => 4,
                'keterangan' =>
                    'Tidak mengikuti upacara sekolah tanpa alasan yang sah.',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Gunakan kolom kode lama jika masih tersedia
        |--------------------------------------------------------------------------
        */
        $keyColumn = $hasColumn('kode_jenis')
            ? 'kode_jenis'
            : 'kode_pelanggaran';

        foreach ($jenisPelanggaran as $item) {
            $data = [];

            /*
            |--------------------------------------------------------------------------
            | Kompatibilitas nama kolom kode
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('kode_jenis')) {
                $data['kode_jenis'] =
                    $item['kode'];
            }

            if ($hasColumn('kode_pelanggaran')) {
                $data['kode_pelanggaran'] =
                    $item['kode'];
            }

            /*
            |--------------------------------------------------------------------------
            | Kompatibilitas nama kolom nama
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('nama_jenis')) {
                $data['nama_jenis'] =
                    $item['nama'];
            }

            if ($hasColumn('nama_pelanggaran')) {
                $data['nama_pelanggaran'] =
                    $item['nama'];
            }

            /*
            |--------------------------------------------------------------------------
            | Aspek pelanggaran
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('aspek_pelanggaran')) {
                $data['aspek_pelanggaran'] =
                    $item['aspek'];
            }

            if ($hasColumn('aspek')) {
                $data['aspek'] =
                    $item['aspek'];
            }

            if ($hasColumn('kategori')) {
                $data['kategori'] =
                    $item['aspek'];
            }

            /*
            |--------------------------------------------------------------------------
            | Tingkat pelanggaran
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('tingkat_pelanggaran')) {
                $data['tingkat_pelanggaran'] =
                    $item['tingkat'];
            }

            if ($hasColumn('tingkat')) {
                $data['tingkat'] =
                    $item['tingkat'];
            }

            if ($hasColumn('level')) {
                $data['level'] =
                    $item['tingkat'];
            }

            /*
            |--------------------------------------------------------------------------
            | Poin
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('poin')) {
                $data['poin'] =
                    $item['poin'];
            }

            if ($hasColumn('bobot')) {
                $data['bobot'] =
                    $item['poin'];
            }

            /*
            |--------------------------------------------------------------------------
            | Keterangan
            |--------------------------------------------------------------------------
            */
            if ($hasColumn('keterangan')) {
                $data['keterangan'] =
                    $item['keterangan'];
            }

            if ($hasColumn('deskripsi')) {
                $data['deskripsi'] =
                    $item['keterangan'];
            }

            if ($hasColumn('updated_at')) {
                $data['updated_at'] = now();
            }

            $existing = DB::table(
                'jenis_pelanggarans'
            )
                ->where(
                    $keyColumn,
                    $item['kode']
                )
                ->exists();

            if ($existing) {
                DB::table('jenis_pelanggarans')
                    ->where(
                        $keyColumn,
                        $item['kode']
                    )
                    ->update($data);
            } else {
                if ($hasColumn('created_at')) {
                    $data['created_at'] = now();
                }

                DB::table(
                    'jenis_pelanggarans'
                )->insert($data);
            }
        }

        $this->command?->info(
            count($jenisPelanggaran) .
            ' data jenis pelanggaran berhasil ditambahkan.'
        );
    }
}