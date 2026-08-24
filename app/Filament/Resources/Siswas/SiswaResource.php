<?php

namespace App\Filament\Resources\Siswas;

use App\Filament\Resources\Siswas\Pages\CreateSiswa;
use App\Filament\Resources\Siswas\Pages\EditSiswa;
use App\Filament\Resources\Siswas\Pages\ListSiswas;
use App\Filament\Resources\Siswas\Schemas\SiswaForm;
use App\Filament\Resources\Siswas\Tables\SiswasTable;
use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Data Siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Data Siswa';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return SiswaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiswasTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'admin',
            'kepala_sekolah',
        ]) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'admin',
            'kepala_sekolah',
        ]) ?? false;
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
            'index' => ListSiswas::route('/'),
            'create' => CreateSiswa::route('/create'),
            'edit' => EditSiswa::route('/{record}/edit'),
        ];
    }
}