<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPelanggaran extends Model
{
    protected $fillable = [
        // Kolom utama yang dipakai aplikasi.
        'kode_jenis',
        'nama_jenis',
        'aspek_pelanggaran',
        'tingkat_pelanggaran',
        'poin',
        'keterangan',

        // Kolom kompatibilitas dari migration lama.
        'kode_pelanggaran',
        'nama_pelanggaran',
    ];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
        ];
    }

    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
