<?php

namespace App\Filament\Widgets;

use App\Models\KMeansRun;
use Filament\Widgets\ChartWidget;

class ClusterDistributionChart extends ChartWidget
{
    protected ?string $heading =
        'Distribusi Hasil Clustering';

    protected function getData(): array
    {
        $run = KMeansRun::query()
            ->latest('id')
            ->first();

        if (! $run) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Titik',
                        'data' => [0, 0, 0],
                    ],
                ],
                'labels' => [
                    'C1 - Rendah',
                    'C2 - Sedang',
                    'C3 - Tinggi',
                ],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Titik',
                    'data' => [
                        $run->results()
                            ->where('cluster', 1)
                            ->count(),

                        $run->results()
                            ->where('cluster', 2)
                            ->count(),

                        $run->results()
                            ->where('cluster', 3)
                            ->count(),
                    ],
                ],
            ],
            'labels' => [
                'C1 - Rendah',
                'C2 - Sedang',
                'C3 - Tinggi',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
