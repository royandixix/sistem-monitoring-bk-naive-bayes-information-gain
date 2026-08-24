<?php

namespace App\Filament\Resources\EvaluasiModels;

use App\Filament\Resources\EvaluasiModels\Pages\ListEvaluasiModels;
use App\Filament\Resources\EvaluasiModels\Tables\EvaluasiModelsTable;
use App\Models\EvaluasiModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EvaluasiModelResource extends Resource
{
    protected static ?string $model =
        EvaluasiModel::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBarSquare;

    protected static ?string $recordTitleAttribute =
        'metode';

    protected static ?string $navigationLabel =
        'Evaluasi Model';

    protected static string|UnitEnum|null $navigationGroup =
        'Algoritma Klasifikasi';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel =
        'Evaluasi Model';

    protected static ?string $pluralModelLabel =
        'Evaluasi Model';

    public static function table(
        Table $table
    ): Table {
        return EvaluasiModelsTable::configure(
            $table
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'kepala_sekolah',
            ]) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'kepala_sekolah',
            ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(
        Model $record
    ): bool {
        return false;
    }

    public static function canDelete(
        Model $record
    ): bool {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListEvaluasiModels::route('/'),
        ];
    }
}