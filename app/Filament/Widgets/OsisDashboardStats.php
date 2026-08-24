<?php

namespace App\Filament\Widgets;

use App\Models\JenisPelanggaran;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OsisDashboardStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isOsis() ?? false;
    }

    protected function getStats(): array
    {
        $totalSiswa = Siswa::query()->count('id');

        $totalPelanggaran = Pelanggaran::query()
            ->where('diajukan_oleh', auth()->id())
            ->count('id');

        $pelanggaranHariIni = Pelanggaran::query()
            ->where('diajukan_oleh', auth()->id())
            ->whereDate('tanggal', Carbon::today())
            ->count('id');

        $pelanggaranBulanIni = Pelanggaran::query()
            ->where('diajukan_oleh', auth()->id())
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count('id');

        $jenisPelanggaran = JenisPelanggaran::query()->count('id');

        return [
            Stat::make('Total Siswa', number_format($totalSiswa))
                ->description('Data siswa yang dapat dilihat oleh OSIS')
                ->icon('heroicon-o-user-group')
                ->chart([2, 3, 4, 6, 8, 10, $totalSiswa])
                ->color('primary'),

            Stat::make('Total Pelanggaran', number_format($totalPelanggaran))
                ->description('Seluruh laporan yang diajukan oleh akun OSIS ini')
                ->icon('heroicon-o-exclamation-triangle')
                ->chart([1, 2, 3, 5, 8, 10, $totalPelanggaran])
                ->color('warning'),

            Stat::make('Pelanggaran Hari Ini', number_format($pelanggaranHariIni))
                ->description('Laporan yang diajukan akun OSIS ini hari ini')
                ->icon('heroicon-o-calendar-days')
                ->chart([0, 1, 1, 2, 2, 3, $pelanggaranHariIni])
                ->color('danger'),

            Stat::make('Pelanggaran Bulan Ini', number_format($pelanggaranBulanIni))
                ->description('Laporan akun OSIS ini pada bulan berjalan')
                ->icon('heroicon-o-chart-bar')
                ->chart([1, 3, 4, 6, 8, 9, $pelanggaranBulanIni])
                ->color('info'),

            Stat::make('Jenis Pelanggaran', number_format($jenisPelanggaran))
                ->description('Referensi jenis pelanggaran yang tersedia')
                ->icon('heroicon-o-clipboard-document-list')
                ->chart([1, 2, 3, 4, 5, 6, $jenisPelanggaran])
                ->color('gray'),
        ];
    }
}