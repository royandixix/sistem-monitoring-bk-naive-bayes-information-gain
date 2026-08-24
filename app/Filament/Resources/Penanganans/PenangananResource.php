<?php

namespace App\Filament\Resources\Penanganans;

use App\Filament\Resources\Penanganans\Pages\CreatePenanganan;
use App\Filament\Resources\Penanganans\Pages\EditPenanganan;
use App\Filament\Resources\Penanganans\Pages\ListPenanganans;
use App\Filament\Resources\Penanganans\Schemas\PenangananForm;
use App\Filament\Resources\Penanganans\Tables\PenanganansTable;
use App\Models\Penanganan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PenangananResource extends Resource
{
    protected static ?string $model = Penanganan::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup =
        'Bimbingan Konseling';

    protected static ?string $navigationLabel = 'Data Penanganan';

    protected static ?string $modelLabel = 'Penanganan';

    protected static ?string $pluralModelLabel = 'Data Penanganan';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return PenangananForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenanganansTable::configure($table);
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
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'admin',
        ]) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isGuruBk()) {
            return true;
        }

        return $user->isOsis() && (int) $record->user_id === (int) $user->id;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'pelanggaran.siswa.kelas',
                'pelanggaran.jenisPelanggaran',
                'user',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isGuruBk()) {
            return $query;
        }

        if ($user->isKepalaSekolah()) {
            return $query->whereHas(
                'pelanggaran',
                fn (Builder $pelanggaranQuery): Builder =>
                    $pelanggaranQuery->disetujui()
            );
        }

        if ($user->isOsis()) {
            // OSIS dapat melihat penanganan kasus resmi dan menambah catatan penanganan.
            return $query->whereHas(
                'pelanggaran',
                fn (Builder $pelanggaranQuery): Builder =>
                    $pelanggaranQuery->disetujui()
            );
        }

        if ($user->isWaliMurid()) {
            return $query
                ->whereHas(
                    'pelanggaran',
                    fn (Builder $pelanggaranQuery): Builder =>
                        $pelanggaranQuery->disetujui()
                )
                ->whereHas(
                    'pelanggaran.siswa.waliMurids',
                    fn (Builder $waliQuery): Builder =>
                        $waliQuery->where('users.id', $user->id)
                );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPenanganans::route('/'),
            'create' => CreatePenanganan::route('/create'),
            'edit' => EditPenanganan::route('/{record}/edit'),
        ];
    }
}
