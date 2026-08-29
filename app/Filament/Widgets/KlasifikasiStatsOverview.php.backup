<?php

namespace App\Filament\Widgets;

use App\Models\EvaluasiModel;
use App\Models\InformationGainResult;
use App\Models\Klasifikasi;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KlasifikasiStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'kepala_sekolah',
        ]) ?? false;
    }

    protected function getStats(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil periode klasifikasi terbaru
        |--------------------------------------------------------------------------
        */
        $latestKlasifikasi = Klasifikasi::query()
            ->latest('updated_at')
            ->first();

        $tahunAjaran = $latestKlasifikasi?->tahun_ajaran;
        $semester = $latestKlasifikasi?->semester;

        /*
        |--------------------------------------------------------------------------
        | Query hasil klasifikasi pada periode terbaru
        |--------------------------------------------------------------------------
        */
        $klasifikasiQuery = Klasifikasi::query();

        if ($tahunAjaran !== null && $semester !== null) {
            $klasifikasiQuery
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester);
        }

        $total = (clone $klasifikasiQuery)->count();

        $baik = (clone $klasifikasiQuery)
            ->where('hasil_ig_naive_bayes', 'Baik')
            ->count();

        $pembinaan = (clone $klasifikasiQuery)
            ->where('hasil_ig_naive_bayes', 'Butuh Perhatian')
            ->count();

        $bermasalah = (clone $klasifikasiQuery)
            ->where('hasil_ig_naive_bayes', 'Bermasalah')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Ambil evaluasi model terbaru
        |--------------------------------------------------------------------------
        */
        $evaluasiQuery = EvaluasiModel::query()
            ->where('metode', 'Naive Bayes + Information Gain');

        if ($tahunAjaran !== null && $semester !== null) {
            $evaluasiQuery
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester);
        }

        $evaluasi = $evaluasiQuery
            ->latest('created_at')
            ->first();

        $akurasi = (float) ($evaluasi?->akurasi ?? 0);
        $precision = (float) ($evaluasi?->precision ?? 0);
        $recall = (float) ($evaluasi?->recall ?? 0);
        $f1Score = (float) ($evaluasi?->f1_score ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Hitung fitur Information Gain yang terpilih
        |--------------------------------------------------------------------------
        */
        $informationGainQuery = InformationGainResult::query()
            ->where('selected', true);

        if ($tahunAjaran !== null && $semester !== null) {
            $informationGainQuery
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester);
        }

        $fiturTerpilih = $informationGainQuery->count();

        /*
        |--------------------------------------------------------------------------
        | Nama periode
        |--------------------------------------------------------------------------
        */
        $periode = ($tahunAjaran !== null && $semester !== null)
            ? "{$tahunAjaran} - {$semester}"
            : 'Belum ada periode';

        return [
            Stat::make(
                'Total Data Klasifikasi',
                number_format($total)
            )
                ->description("Periode: {$periode}")
                ->descriptionIcon('heroicon-o-calendar-days')
                ->icon('heroicon-o-user-group')
                ->chart([
                    0,
                    2,
                    4,
                    6,
                    8,
                    10,
                    $total,
                ])
                ->color('primary'),

            Stat::make(
                'Kategori Baik',
                number_format($baik)
            )
                ->description('Siswa dengan kategori perilaku baik')
                ->descriptionIcon('heroicon-o-check-circle')
                ->icon('heroicon-o-check-badge')
                ->chart([
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    $baik,
                ])
                ->color('success'),

            Stat::make(
                'Butuh Perhatian',
                number_format($pembinaan)
            )
                ->description('Siswa yang membutuhkan pembinaan')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->icon('heroicon-o-exclamation-triangle')
                ->chart([
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    $pembinaan,
                ])
                ->color('warning'),

            Stat::make(
                'Bermasalah',
                number_format($bermasalah)
            )
                ->description('Siswa dengan prioritas penanganan')
                ->descriptionIcon('heroicon-o-x-circle')
                ->icon('heroicon-o-shield-exclamation')
                ->chart([
                    0,
                    1,
                    1,
                    2,
                    2,
                    3,
                    $bermasalah,
                ])
                ->color('danger'),

            Stat::make(
                'Akurasi NB + IG',
                number_format($akurasi, 2) . '%'
            )
                ->description(
                    'Precision: ' .
                    number_format($precision, 2) .
                    '% | Recall: ' .
                    number_format($recall, 2) .
                    '%'
                )
                ->descriptionIcon('heroicon-o-chart-bar')
                ->icon('heroicon-o-presentation-chart-line')
                ->chart([
                    0,
                    20,
                    40,
                    60,
                    70,
                    80,
                    $akurasi,
                ])
                ->color('info'),

            Stat::make(
                'F1-Score',
                number_format($f1Score, 2) . '%'
            )
                ->description(
                    'Keseimbangan precision dan recall'
                )
                ->descriptionIcon('heroicon-o-scale')
                ->icon('heroicon-o-chart-pie')
                ->chart([
                    0,
                    20,
                    40,
                    60,
                    70,
                    80,
                    $f1Score,
                ])
                ->color('primary'),

            Stat::make(
                'Fitur Terpilih',
                number_format($fiturTerpilih)
            )
                ->description(
                    'Fitur terbaik berdasarkan Information Gain'
                )
                ->descriptionIcon('heroicon-o-funnel')
                ->icon('heroicon-o-adjustments-horizontal')
                ->chart([
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    $fiturTerpilih,
                ])
                ->color('gray'),
        ];
    }
}