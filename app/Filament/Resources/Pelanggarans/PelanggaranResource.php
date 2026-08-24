<?php

namespace App\Filament\Resources\Pelanggarans;

use App\Filament\Resources\Pelanggarans\Pages\CreatePelanggaran;
use App\Filament\Resources\Pelanggarans\Pages\EditPelanggaran;
use App\Filament\Resources\Pelanggarans\Pages\ListPelanggarans;
use App\Filament\Resources\Pelanggarans\Schemas\PelanggaranForm;
use App\Filament\Resources\Pelanggarans\Tables\PelanggaransTable;
use App\Models\Pelanggaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PelanggaranResource extends Resource
{
    protected static ?string $model =
        Pelanggaran::class;

    protected static string|BackedEnum|null
        $navigationIcon =
            Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null
        $navigationGroup =
            'Bimbingan Konseling';

    protected static ?string $navigationLabel =
        'Laporan Pelanggaran';

    protected static ?string $modelLabel =
        'Laporan Pelanggaran';

    protected static ?string $pluralModelLabel =
        'Laporan Pelanggaran';

    protected static ?int $navigationSort = 10;

    public static function form(
        Schema $schema
    ): Schema {
        return PelanggaranForm::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return PelanggaransTable::configure(
            $table
        );
    }

    public static function shouldRegisterNavigation():
        bool {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'admin',
                'kepala_sekolah',
                'wali_murid',
            ]) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'admin',
                'kepala_sekolah',
                'wali_murid',
            ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'admin',
            ]) ?? false;
    }

    public static function canEdit(
        Model $record
    ): bool {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isGuruBk()) {
            return true;
        }

        if (! $user->isOsis()) {
            return false;
        }

        return
            (int) $record->diajukan_oleh ===
                (int) $user->id
            &&
            in_array(
                $record->status_pengajuan,
                [
                    Pelanggaran::STATUS_MENUNGGU,
                    Pelanggaran::STATUS_DITOLAK,
                ],
                true
            );
    }

    public static function canDelete(
        Model $record
    ): bool {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isGuruBk()) {
            return true;
        }

        if (! $user->isOsis()) {
            return false;
        }

        return
            (int) $record->diajukan_oleh ===
                (int) $user->id
            &&
            in_array(
                $record->status_pengajuan,
                [
                    Pelanggaran::STATUS_MENUNGGU,
                    Pelanggaran::STATUS_DITOLAK,
                ],
                true
            );
    }

    public static function canDeleteAny(): bool
    {
        return auth()
            ->user()
            ?->isGuruBk() ?? false;
    }

    public static function getEloquentQuery():
        Builder {
        $query = parent::getEloquentQuery()
            ->with([
                'siswa.kelas',
                'jenisPelanggaran',
                'diajukanOleh',
                'diprosesOleh',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw(
                '1 = 0'
            );
        }

        /*
         * Guru BK melihat seluruh pengajuan.
         */
        if ($user->isGuruBk()) {
            return $query;
        }

        /*
         * OSIS hanya melihat laporan
         * yang dibuat oleh akunnya sendiri.
         */
        if ($user->isOsis()) {
            return $query->where(
                'diajukan_oleh',
                $user->id
            );
        }

        /*
         * Kepala Sekolah hanya melihat
         * laporan yang sudah disetujui.
         */
        if (
            $user->isKepalaSekolah()
        ) {
            return $query->disetujui();
        }

        /*
         * Wali Murid hanya melihat
         * laporan resmi milik anaknya.
         */
        if ($user->isWaliMurid()) {
            return $query
                ->disetujui()
                ->whereHas(
                    'siswa.waliMurids',
                    fn (
                        Builder $waliQuery
                    ): Builder =>
                        $waliQuery->where(
                            'users.id',
                            $user->id
                        )
                );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListPelanggarans::route('/'),

            'create' =>
                CreatePelanggaran::route(
                    '/create'
                ),

            'edit' =>
                EditPelanggaran::route(
                    '/{record}/edit'
                ),
        ];
    }
}