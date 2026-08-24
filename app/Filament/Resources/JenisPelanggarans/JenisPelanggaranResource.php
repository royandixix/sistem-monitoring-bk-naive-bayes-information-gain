<?php

namespace App\Filament\Resources\JenisPelanggarans;

use App\Filament\Resources\JenisPelanggarans\Pages\CreateJenisPelanggaran;
use App\Filament\Resources\JenisPelanggarans\Pages\EditJenisPelanggaran;
use App\Filament\Resources\JenisPelanggarans\Pages\ListJenisPelanggarans;
use App\Filament\Resources\JenisPelanggarans\Schemas\JenisPelanggaranForm;
use App\Filament\Resources\JenisPelanggarans\Tables\JenisPelanggaransTable;
use App\Models\JenisPelanggaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class JenisPelanggaranResource extends Resource
{
    protected static ?string $model = JenisPelanggaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jenis Pelanggaran';

    protected static ?string $modelLabel = 'Jenis Pelanggaran';

    protected static ?string $pluralModelLabel = 'Jenis Pelanggaran';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return JenisPelanggaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JenisPelanggaransTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJenisPelanggarans::route('/'),
            'create' => CreateJenisPelanggaran::route('/create'),
            'edit' => EditJenisPelanggaran::route('/{record}/edit'),
        ];
    }
}