<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaiveBayesModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'metode',
        'classes',
        'features',
        'model',
        'jumlah_data_training',
        'training_ratio',
        'random_seed',
    ];

    protected function casts(): array
    {
        return [
            'classes' => 'array',
            'features' => 'array',
            'model' => 'array',
            'jumlah_data_training' => 'integer',
            'training_ratio' => 'float',
            'random_seed' => 'integer',
        ];
    }
}
