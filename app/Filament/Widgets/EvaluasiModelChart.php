<?php

namespace App\Filament\Widgets;

use App\Models\EvaluasiModel;
use Filament\Widgets\ChartWidget;

class EvaluasiModelChart extends ChartWidget
{
    protected ?string $heading =
        'Evaluasi Performa Model Klasifikasi';

    protected ?string $pollingInterval =
        '10s';

    protected int|string|array $columnSpan =
        1;

    public static function canView(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'kepala_sekolah',
            ]) ?? false;
    }

    protected function getData(): array
    {
        $optimized = EvaluasiModel::query()
            ->where(
                'metode',
                'Naive Bayes + Information Gain'
            )
            ->latest()
            ->first();

        $naiveBayesQuery =
            EvaluasiModel::query()
                ->where(
                    'metode',
                    'Naive Bayes'
                );

        if ($optimized?->tahun_ajaran) {
            $naiveBayesQuery
                ->where(
                    'tahun_ajaran',
                    $optimized->tahun_ajaran
                )
                ->where(
                    'semester',
                    $optimized->semester
                );
        }

        $naiveBayes =
            $naiveBayesQuery
                ->latest()
                ->first();

        return [
            'datasets' => [
                [
                    'label' =>
                        'Naive Bayes',

                    'data' => [
                        (float) (
                            $naiveBayes
                                ?->akurasi ?? 0
                        ),

                        (float) (
                            $naiveBayes
                                ?->precision ?? 0
                        ),

                        (float) (
                            $naiveBayes
                                ?->recall ?? 0
                        ),

                        (float) (
                            $naiveBayes
                                ?->f1_score ?? 0
                        ),
                    ],

                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],

                [
                    'label' =>
                        'Naive Bayes + Information Gain',

                    'data' => [
                        (float) (
                            $optimized
                                ?->akurasi ?? 0
                        ),

                        (float) (
                            $optimized
                                ?->precision ?? 0
                        ),

                        (float) (
                            $optimized
                                ?->recall ?? 0
                        ),

                        (float) (
                            $optimized
                                ?->f1_score ?? 0
                        ),
                    ],

                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],

            'labels' => [
                'Akurasi',
                'Precision',
                'Recall',
                'F1 Score',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            'maintainAspectRatio' =>
                false,

            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],

                'tooltip' => [
                    'enabled' => true,
                ],
            ],

            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}