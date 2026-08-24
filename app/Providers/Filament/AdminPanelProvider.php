<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\PelanggaranBeratAlertWidget;

use App\Filament\Widgets\ConfusionMatrixWidget;
use App\Filament\Widgets\EvaluasiModelChart;
use App\Filament\Widgets\InformationGainRankingChart;
use App\Filament\Widgets\KlasifikasiComparisonChart;
use App\Filament\Widgets\KlasifikasiHasilChart;
use App\Filament\Widgets\KlasifikasiStatsOverview;
use App\Filament\Widgets\LatestKlasifikasiTable;
use App\Filament\Widgets\OsisDashboardStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Sistem Monitoring BK')
            ->login(\App\Filament\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                OsisDashboardStats::class,
                KlasifikasiStatsOverview::class,
                KlasifikasiHasilChart::class,
                KlasifikasiComparisonChart::class,
                InformationGainRankingChart::class,
                EvaluasiModelChart::class,
                ConfusionMatrixWidget::class,
                LatestKlasifikasiTable::class,
                PelanggaranBeratAlertWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}