<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Klasifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran',
        'semester',
        'jumlah_pelanggaran',
        'total_poin',
        'hasil_klasifikasi',
        'label_aktual',
        'hasil_naive_bayes',
        'probabilitas_naive_bayes',
        'hasil_ig_naive_bayes',
        'probabilitas_ig_naive_bayes',
        'probabilitas',
        'probabilitas_detail',
        'fitur_klasifikasi',
        'information_gain_detail',
        'metode',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pelanggaran' => 'integer',
            'total_poin' => 'integer',
            'probabilitas' => 'float',
            'probabilitas_naive_bayes' => 'float',
            'probabilitas_ig_naive_bayes' => 'float',
            'probabilitas_detail' => 'array',
            'fitur_klasifikasi' => 'array',
            'information_gain_detail' => 'array',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}