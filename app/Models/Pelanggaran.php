<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'siswa_id',
        'jenis_pelanggaran_id',
        'tanggal',
        'keterangan',
        'semester',
        'tahun_ajaran',
        'status_pengajuan',
        'diajukan_oleh',
        'diproses_oleh',
        'diproses_pada',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'diproses_pada' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            Siswa::class,
            'siswa_id'
        );
    }

    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(
            JenisPelanggaran::class,
            'jenis_pelanggaran_id'
        );
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diajukan_oleh'
        );
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diproses_oleh'
        );
    }

    public function scopeMenunggu(
        Builder $query
    ): Builder {
        return $query->where(
            'status_pengajuan',
            self::STATUS_MENUNGGU
        );
    }

    public function scopeDisetujui(
        Builder $query
    ): Builder {
        return $query->where(
            'status_pengajuan',
            self::STATUS_DISETUJUI
        );
    }

    public function scopeDitolak(
        Builder $query
    ): Builder {
        return $query->where(
            'status_pengajuan',
            self::STATUS_DITOLAK
        );
    }

    public function isMenunggu(): bool
    {
        return $this->status_pengajuan ===
            self::STATUS_MENUNGGU;
    }

    public function isDisetujui(): bool
    {
        return $this->status_pengajuan ===
            self::STATUS_DISETUJUI;
    }

    public function isDitolak(): bool
    {
        return $this->status_pengajuan ===
            self::STATUS_DITOLAK;
    }
}