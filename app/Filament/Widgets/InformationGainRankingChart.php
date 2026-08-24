<?php

namespace App\Filament\Widgets;

use App\Models\InformationGainResult;
use Filament\Widgets\ChartWidget;

class InformationGainRankingChart extends ChartWidget
{
    protected ?string $heading =
        'Ranking Fitur Berdasarkan Information Gain';

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
        $latest =
            InformationGainResult::query()
                ->latest('updated_at')
                ->first();

        $query =
            InformationGainResult::query();

        if ($latest?->tahun_ajaran) {
            $query
                ->where(
                    'tahun_ajaran',
                    $latest->tahun_ajaran
                )
                ->where(
                    'semester',
                    $latest->semester
                );
        }

        $results = $query
            ->orderBy('ranking')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' =>
                        'Nilai Gain',

                    'data' =>
                        $results
                            ->pluck('gain')
                            ->map(
                                fn ($value): float =>
                                    (float) $value
                            )
                            ->toArray(),

                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],

            'labels' =>
                $results
                    ->pluck('fitur')
                    ->map(
                        fn (?string $feature): string => match ($feature) {
                            'kerajinan_kategori' => 'Kerajinan',
                            'kelakuan_kategori' => 'Kelakuan',
                            'kerapian_kategori' => 'Kerapian',
                            default => (string) $feature,
                        }
                    )
                    ->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',

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
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}