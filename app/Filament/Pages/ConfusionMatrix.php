<?php

namespace App\Filament\Pages;

use App\Services\NaiveBayesEvaluationService;
use Filament\Pages\Page;

class ConfusionMatrix extends Page
{
    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-table-cells';

    protected static string|\UnitEnum|null $navigationGroup =
        'Data Mining';

    protected static ?string $navigationLabel =
        'Confusion Matrix';

    protected static ?string $title =
        'Evaluasi Model';

    protected static ?string $slug =
        'confusion-matrix';

    protected static ?int $navigationSort = 4;

    protected string $view =
        'filament.pages.confusion-matrix';

    public array $evaluation = [];

    public function mount(): void
    {
        $this->evaluation = app(
            NaiveBayesEvaluationService::class
        )->evaluate();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
