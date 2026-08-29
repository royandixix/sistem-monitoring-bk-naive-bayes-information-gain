<?php

namespace App\Filament\Widgets;

use App\Models\Klasifikasi;
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
        $latest = Klasifikasi::query()
            ->latest('updated_at')
            ->first([
                'tahun_ajaran',
                'semester',
            ]);

        $query = Klasifikasi::query()
            ->with([
                'siswa.kelas',
            ])
            ->when(
                $latest,
                fn (Builder $query) => $query
                    ->where('tahun_ajaran', $latest->tahun_ajaran)
                    ->where('semester', $latest->semester)
            )
            ->where(function (Builder $query): void {
                $query
                    ->where('fitur_klasifikasi->kerajinan_kategori', 'Berat')
                    ->orWhere('fitur_klasifikasi->kelakuan_kategori', 'Berat')
                    ->orWhere('fitur_klasifikasi->kerapian_kategori', 'Berat');
            })
            ->orderByDesc('total_poin')
            ->orderByDesc('jumlah_pelanggaran');

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

                TextColumn::make('jumlah_pelanggaran')
                    ->label('Jumlah Pelanggaran')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('total_poin')
                    ->label('Total Poin')
                    ->badge()
                    ->color('danger')
                    ->suffix(' poin')
                    ->sortable(),

                TextColumn::make('aspek_berat')
                    ->label('Aspek Berat')
                    ->badge()
                    ->color('danger')
                    ->state(function (Klasifikasi $record): string {
                        $fitur = $record->fitur_klasifikasi ?? [];
                        $aspek = [];

                        if (($fitur['kerajinan_kategori'] ?? null) === 'Berat') {
                            $aspek[] = 'Kerajinan';
                        }

                        if (($fitur['kelakuan_kategori'] ?? null) === 'Berat') {
                            $aspek[] = 'Kelakuan';
                        }

                        if (($fitur['kerapian_kategori'] ?? null) === 'Berat') {
                            $aspek[] = 'Kerapian';
                        }

                        return implode(', ', $aspek) ?: '-';
                    }),

                TextColumn::make('label_aktual')
                    ->label('Label Aktual')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Butuh Perhatian' => 'warning',
                        'Bermasalah' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->badge(),
            ])
            ->striped()
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Tidak ada akumulasi pelanggaran berat')
            ->emptyStateDescription(
                'Belum terdapat siswa dengan kategori Berat pada aspek Kerajinan, Kelakuan, atau Kerapian.'
            );
    }
}
