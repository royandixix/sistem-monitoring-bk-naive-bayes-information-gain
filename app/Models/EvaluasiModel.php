<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiModel extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_models';

    protected $fillable = [
        'metode',
        'tahun_ajaran',
        'semester',
        'jumlah_data_training',
        'jumlah_data_testing',
        'training_ratio',
        'random_seed',
        'akurasi',
        'precision',
        'recall',
        'f1_score',
        'confusion_matrix',
        'selected_features',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_data_training' => 'integer',
            'jumlah_data_testing' => 'integer',
            'training_ratio' => 'float',
            'random_seed' => 'integer',
            'akurasi' => 'float',
            'precision' => 'float',
            'recall' => 'float',
            'f1_score' => 'float',
            'confusion_matrix' => 'array',
            'selected_features' => 'array',
        ];
    }
}
