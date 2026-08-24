<?php

namespace App\Filament\Resources\Kelas;

use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup =
        'Master Data';

    protected static ?string $navigationLabel =
        'Kelas';

    protected static ?string $modelLabel =
        'Kelas';

    protected static ?string $pluralModelLabel =
        'Data Kelas';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute =
        'nama_kelas';

    public static function form(Schema $schema): Schema
    {
        return KelasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasTable::configure($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses sidebar
    |--------------------------------------------------------------------------
    | Menu Kelas hanya muncul untuk Guru BK.
    */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses halaman daftar
    |--------------------------------------------------------------------------
    */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses menambah kelas
    |--------------------------------------------------------------------------
    */
    public static function canCreate(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses mengedit kelas
    |--------------------------------------------------------------------------
    */
    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses menghapus satu kelas
    |--------------------------------------------------------------------------
    */
    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses menghapus banyak kelas
    |--------------------------------------------------------------------------
    */
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
            'edit' => EditKelas::route('/{record}/edit'),
        ];
    }
}