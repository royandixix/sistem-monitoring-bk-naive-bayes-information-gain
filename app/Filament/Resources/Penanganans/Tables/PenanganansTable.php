<?php

namespace App\Filament\Resources\Penanganans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\Penanganans\PenangananResource;
use App\Models\Penanganan;

class PenanganansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_penanganan', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('pelanggaran.siswa.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('pelanggaran.siswa.kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('pelanggaran.jenisPelanggaran.nama_jenis')
                    ->label('Jenis Pelanggaran')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn ($record): ?string => $record->pelanggaran?->jenisPelanggaran?->nama_jenis),

                TextColumn::make('tindakan')
                    ->label('Tindakan')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Teguran Lisan' => 'info',
                        'Teguran Tertulis' => 'warning',
                        'Pemanggilan Orang Tua' => 'danger',
                        'Konseling' => 'success',
                        'Skorsing' => 'danger',
                        'Lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_penanganan')
                    ->label('Tanggal Penanganan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Ditangani Oleh')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tindakan')
                    ->label('Tindakan')
                    ->options([
                        'Teguran Lisan' => 'Teguran Lisan',
                        'Teguran Tertulis' => 'Teguran Tertulis',
                        'Pemanggilan Orang Tua' => 'Pemanggilan Orang Tua',
                        'Konseling' => 'Konseling',
                        'Skorsing' => 'Skorsing',
                        'Lainnya' => 'Lainnya',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Petugas')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->visible(
                        fn (Penanganan $record): bool =>
                            PenangananResource::canEdit($record)
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(
                            fn (): bool =>
                                PenangananResource::canDeleteAny()
                        )
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data penanganan')
            ->emptyStateDescription('Silakan tambahkan data penanganan siswa terlebih dahulu.');
    }
}
