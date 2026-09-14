<?php

namespace App\Filament\Widgets;

use App\Models\Klasifikasi;
use Filament\Widgets\ChartWidget;

class KlasifikasiHasilChart extends ChartWidget
{
    protected ?string $heading =
        'Distribusi Label Aktual dan Hasil Klasifikasi';

    protected ?string $pollingInterval =
        '10s';

    protected int|string|array $columnSpan =
        1;

    public ?string $filter = 'aktual';

    public static function canView(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'kepala_sekolah',
            ]) ?? false;
    }

    protected function getFilters(): ?array
    {
        return [
            'aktual' =>
                'Label Aktual Dataset',

            'ig' =>
                'Naive Bayes + Information Gain',

            'nb' =>
                'Naive Bayes',

            'akhir' =>
                'Hasil Klasifikasi Akhir',
        ];
    }

    protected function getData(): array
    {
        $column = match ($this->filter) {
            'aktual' =>
                'label_aktual',

            'nb' =>
                'hasil_naive_bayes',

            'akhir' =>
                'hasil_klasifikasi',

            default =>
                'hasil_ig_naive_bayes',
        };

        $labels = [
            'Baik',
            'Butuh Perhatian',
            'Bermasalah',
        ];

        $latest =
            Klasifikasi::query()
                ->latest('updated_at')
                ->first();

        $query =
            Klasifikasi::query()
                ;

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

        $data =
            collect($labels)
                ->map(
                    fn (
                        string $label
                    ): int =>
                        (clone $query)
                            ->where(
                                $column,
                                $label
                            )
                            ->count('id')
                )
                ->toArray();

        return [
            'datasets' => [
                [
                    'label' =>
                        'Jumlah Siswa',

                    'data' =>
                        $data,

                    'borderWidth' => 2,
                    'hoverOffset' => 8,
                ],
            ],

            'labels' =>
                $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            'maintainAspectRatio' =>
                false,

            'cutout' => '62%',

            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],

                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}