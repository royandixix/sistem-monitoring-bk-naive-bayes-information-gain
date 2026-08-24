<?php

namespace App\Filament\Resources\InformationGainResults;

use App\Filament\Resources\InformationGainResults\Pages\ListInformationGainResults;
use App\Filament\Resources\InformationGainResults\Tables\InformationGainResultsTable;
use App\Models\InformationGainResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class InformationGainResultResource extends Resource
{
    protected static ?string $model = InformationGainResult::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedFunnel;

    protected static string|UnitEnum|null $navigationGroup =
        'Algoritma Klasifikasi';

    protected static ?string $navigationLabel =
        'Ranking Information Gain';

    protected static ?string $modelLabel =
        'Information Gain';

    protected static ?string $pluralModelLabel =
        'Ranking Information Gain';

    protected static ?int $navigationSort = 15;

    public static function table(Table $table): Table
    {
        return InformationGainResultsTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'kepala_sekolah',
        ]) ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInformationGainResults::route('/'),
        ];
    }
}
