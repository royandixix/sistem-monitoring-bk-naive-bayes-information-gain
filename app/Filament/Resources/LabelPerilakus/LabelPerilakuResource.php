<?php

namespace App\Filament\Resources\LabelPerilakus;

use App\Filament\Resources\LabelPerilakus\Pages\CreateLabelPerilaku;
use App\Filament\Resources\LabelPerilakus\Pages\EditLabelPerilaku;
use App\Filament\Resources\LabelPerilakus\Pages\ListLabelPerilakus;
use App\Filament\Resources\LabelPerilakus\Schemas\LabelPerilakuForm;
use App\Filament\Resources\LabelPerilakus\Tables\LabelPerilakusTable;
use App\Models\LabelPerilaku;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class LabelPerilakuResource extends Resource
{
    protected static ?string $model = LabelPerilaku::class;

    protected static string|BackedEnum|null $navigationIcon =
    Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup =
    'Algoritma Klasifikasi';

    protected static ?string $navigationLabel =
    'Label Perilaku';

    protected static ?string $modelLabel =
    'Label Perilaku';

    protected static ?string $pluralModelLabel =
    'Label Perilaku';

    protected static ?int $navigationSort = 5;

    public static function form(
        Schema $schema
    ): Schema {
        return LabelPerilakuForm::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return LabelPerilakusTable::configure(
            $table
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function canEdit(
        Model $record
    ): bool {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function canDelete(
        Model $record
    ): bool {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' =>
            ListLabelPerilakus::route('/'),

            'create' =>
            CreateLabelPerilaku::route('/create'),

            'edit' =>
            EditLabelPerilaku::route(
                '/{record}/edit'
            ),
        ];
    }
}
