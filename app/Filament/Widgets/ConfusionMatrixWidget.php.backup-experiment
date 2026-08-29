<?php

namespace App\Filament\Widgets;

use App\Models\EvaluasiModel;
use Filament\Widgets\Widget;

class ConfusionMatrixWidget extends Widget
{
    protected string $view =
        'filament.widgets.confusion-matrix-widget';

    protected int|string|array $columnSpan =
        'full';

    protected static ?int $sort = 30;

    public static function canView(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'kepala_sekolah',
            ]) ?? false;
    }

    protected function getViewData(): array
    {
        $optimized = EvaluasiModel::query()
            ->where(
                'metode',
                'Naive Bayes + Information Gain'
            )
            ->latest()
            ->first();

        $baselineQuery =
            EvaluasiModel::query()
                ->where(
                    'metode',
                    'Naive Bayes'
                );

        if ($optimized?->tahun_ajaran) {
            $baselineQuery
                ->where(
                    'tahun_ajaran',
                    $optimized->tahun_ajaran
                )
                ->where(
                    'semester',
                    $optimized->semester
                );
        }

        return [
            'baseline' =>
                $baselineQuery
                    ->latest()
                    ->first(),

            'optimized' =>
                $optimized,
        ];
    }
}
