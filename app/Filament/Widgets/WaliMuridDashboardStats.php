<?php

namespace App\Filament\Widgets;

use App\Models\Pelanggaran;
use App\Models\Penanganan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaliMuridDashboardStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isWaliMurid() ?? false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        $anakIds = $user?->anak()
            ->pluck('siswas.id')
            ->all() ?? [];

        $jumlahAnak = count($anakIds);

        $pelanggaranResmi = empty($anakIds)
            ? 0
            : Pelanggaran::query()
                ->disetujui()
                ->whereIn('siswa_id', $anakIds)
                ->count('id');

        $penanganan = empty($anakIds)
            ? 0
            : Penanganan::query()
                ->whereHas(
                    'pelanggaran',
                    fn ($query) => $query
                        ->disetujui()
                        ->whereIn('siswa_id', $anakIds)
                )
                ->count('id');

        return [
            Stat::make('Anak Terhubung', number_format($jumlahAnak))
                ->description('Siswa yang terhubung dengan akun wali ini')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Pelanggaran Resmi', number_format($pelanggaranResmi))
                ->description('Pelanggaran anak yang sudah disetujui Guru BK')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Riwayat Penanganan', number_format($penanganan))
                ->description('Tindak lanjut kasus yang dapat dilihat wali')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
        ];
    }
}
