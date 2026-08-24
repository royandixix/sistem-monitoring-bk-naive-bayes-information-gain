<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penanganan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggaran_id',
        'tindakan',
        'tanggal_penanganan',
        'catatan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penanganan' => 'date',
        ];
    }

    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
