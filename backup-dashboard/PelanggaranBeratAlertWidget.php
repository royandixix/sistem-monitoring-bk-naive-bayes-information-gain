<?php

namespace App\Filament\Widgets;

use App\Models\Pelanggaran;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PelanggaranBeratAlertWidget extends TableWidget
{
    protected static ?string $heading = 'Peringatan Pelanggaran Berat';

    protected static ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'kepala_sekolah',
        ]) ?? false;
    }

    public function table(Table $table): Table
    {
        $query = Pelanggaran::query()
            ->with([
                'siswa.kelas',
                'jenisPelanggaran',
            ])
            ->where(
                'status_pengajuan',
                Pelanggaran::STATUS_DISETUJUI
            )
            ->whereHas(
                'jenisPelanggaran',
                function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query
                            ->where('tingkat_pelanggaran', 'Berat')
                            ->orWhere('poin', '>', 15);
                    });
                }
            )
            ->latest('tanggal');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable(),

                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('siswa.kelas.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('jenisPelanggaran.nama_jenis')
                    ->label('Pelanggaran')
                    ->wrap(),

                TextColumn::make('jenisPelanggaran.aspek_pelanggaran')
                    ->label('Aspek')
                    ->badge()
                    ->color('info'),

                TextColumn::make('jenisPelanggaran.poin')
                    ->label('Poin')
                    ->badge()
                    ->color('danger')
                    ->suffix(' poin'),

                TextColumn::make('jenisPelanggaran.tingkat_pelanggaran')
                    ->label('Tingkat')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(
                        fn (?string $state): string => $state ?: 'Berat'
                    ),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->badge(),
            ])
            ->striped()
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Tidak ada pelanggaran berat')
            ->emptyStateDescription(
                'Belum terdapat pelanggaran berat yang telah disetujui Guru BK.'
            );
    }
}
