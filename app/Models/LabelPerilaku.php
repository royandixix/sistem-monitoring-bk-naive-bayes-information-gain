<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelPerilaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran',
        'semester',
        'label_aktual',
        'catatan',
        'labeled_by',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function labeledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'labeled_by');
    }
}