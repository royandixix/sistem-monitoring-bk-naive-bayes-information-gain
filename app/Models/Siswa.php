<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nama',
        'jk',
        'kelas_id',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'no_hp_ortu',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * Kelas tempat siswa berada.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Daftar pelanggaran siswa.
     */
    public function pelanggarans(): HasMany
    {
        return $this->hasMany(
            Pelanggaran::class,
            'siswa_id'
        );
    }

    /**
     * Seluruh riwayat hasil klasifikasi siswa.
     */
    public function klasifikasis(): HasMany
    {
        return $this->hasMany(
            Klasifikasi::class,
            'siswa_id'
        );
    }

    /**
     * Hasil klasifikasi siswa yang terbaru.
     */
    public function klasifikasi(): HasOne
    {
        return $this->hasOne(
            Klasifikasi::class,
            'siswa_id'
        )->latestOfMany();
    }

    /**
     * Seluruh label aktual perilaku siswa.
     */
    public function labelPerilakus(): HasMany
    {
        return $this->hasMany(
            LabelPerilaku::class,
            'siswa_id'
        );
    }

    /**
     * Akun orang tua atau wali yang terhubung dengan siswa.
     *
     * Satu siswa dapat memiliki beberapa wali,
     * misalnya akun ayah, ibu, atau wali lainnya.
     */
    public function waliMurids(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'wali_murid_siswa',
            'siswa_id',
            'user_id'
        )
            ->withPivot([
                'hubungan',
                'is_primary',
            ])
            ->withTimestamps();
    }
}