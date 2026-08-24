<?php

namespace App\Filament\Resources\Klasifikasis;

use App\Filament\Resources\Klasifikasis\Pages\ListKlasifikasis;
use App\Filament\Resources\Klasifikasis\Tables\KlasifikasisTable;
use App\Models\Klasifikasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class KlasifikasiResource extends Resource
{
    protected static ?string $model = Klasifikasi::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup =
        'Algoritma Klasifikasi';

    protected static ?string $navigationLabel =
        'Klasifikasi Naive Bayes';

    protected static ?string $modelLabel = 'Klasifikasi';

    protected static ?string $pluralModelLabel =
        'Klasifikasi Naive Bayes';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return KlasifikasisTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'admin',
            'kepala_sekolah',
            'wali_murid',
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['siswa.kelas']);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isWaliMurid()) {
            return $query->whereHas(
                'siswa.waliMurids',
                fn (Builder $waliQuery): Builder =>
                    $waliQuery->where('users.id', $user->id)
            );
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKlasifikasis::route('/'),
        ];
    }
}
