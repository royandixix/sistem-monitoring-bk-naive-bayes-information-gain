<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformationGainResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'fitur',
        'gain',
        'entropy_before',
        'entropy_after',
        'selected',
        'metode',
        'jumlah_data',
        'ranking',
        'random_seed',
        'detail',
    ];

    protected function casts(): array
    {
        return [
            'gain' => 'float',
            'entropy_before' => 'float',
            'entropy_after' => 'float',
            'selected' => 'boolean',
            'jumlah_data' => 'integer',
            'ranking' => 'integer',
            'random_seed' => 'integer',
            'detail' => 'array',
        ];
    }
}