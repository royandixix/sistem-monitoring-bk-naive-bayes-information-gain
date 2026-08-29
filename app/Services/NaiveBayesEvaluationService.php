<?php

namespace App\Services;

use App\Models\EvaluasiModel;

class NaiveBayesEvaluationService
{
    private array $labels = [
        'Baik',
        'Butuh Perhatian',
        'Bermasalah',
    ];

    public function evaluate(): array
    {
        $records = EvaluasiModel::query()
            ->latest('id')
            ->get();

        $baseline = $records->first(function ($record) {
            $metode = strtolower((string) $record->metode);

            return ! str_contains($metode, 'information')
                && ! str_contains($metode, 'gain')
                && ! str_contains($metode, 'ig');
        });

        $informationGain = $records->first(function ($record) {
            $metode = strtolower((string) $record->metode);

            return str_contains($metode, 'information')
                || str_contains($metode, 'gain')
                || str_contains($metode, 'ig');
        });

        return [
            'labels' => $this->labels,
            'baseline' => $this->formatEvaluation($baseline),
            'information_gain' => $this->formatEvaluation($informationGain),
        ];
    }

    private function formatEvaluation($record): array
    {
        if (! $record) {
            return [
                'available' => false,
                'metode' => '-',
                'training' => 0,
                'testing' => 0,
                'accuracy' => null,
                'precision' => null,
                'recall' => null,
                'f1_score' => null,
                'matrix' => $this->emptyMatrix(),
            ];
        }

        return [
            'available' => true,
            'metode' => $record->metode ?? '-',
            'training' => $record->jumlah_data_training ?? 0,
            'testing' => $record->jumlah_data_testing ?? 0,
            'accuracy' => $record->akurasi ?? $record->accuracy,
            'precision' => $record->precision,
            'recall' => $record->recall,
            'f1_score' => $record->f1_score,
            'matrix' => $this->normalizeMatrix(
                $record->confusion_matrix
            ),
        ];
    }

    private function normalizeMatrix($matrix): array
    {
        if (is_string($matrix)) {
            $decoded = json_decode($matrix, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $matrix = $decoded;
            }
        }

        if (! is_array($matrix)) {
            return $this->emptyMatrix();
        }

        $normalized = $this->emptyMatrix();

        if (
            isset($matrix[0])
            && is_array($matrix[0])
        ) {
            foreach ($this->labels as $i => $actual) {
                foreach ($this->labels as $j => $predicted) {
                    $normalized[$actual][$predicted] =
                        (int) ($matrix[$i][$j] ?? 0);
                }
            }

            return $normalized;
        }

        foreach ($this->labels as $actual) {
            foreach ($this->labels as $predicted) {
                $normalized[$actual][$predicted] =
                    (int) ($matrix[$actual][$predicted] ?? 0);
            }
        }

        return $normalized;
    }

    private function emptyMatrix(): array
    {
        $matrix = [];

        foreach ($this->labels as $actual) {
            foreach ($this->labels as $predicted) {
                $matrix[$actual][$predicted] = 0;
            }
        }

        return $matrix;
    }
}
