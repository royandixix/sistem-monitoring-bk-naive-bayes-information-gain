<?php

namespace App\Filament\Resources\JenisPelanggarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JenisPelanggaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode_jenis')
            ->striped()
            ->columns([
                TextColumn::make('kode_jenis')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('nama_jenis')
                    ->label('Jenis Pelanggaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('aspek_pelanggaran')
                    ->label('Aspek')
                    ->badge()
                    ->sortable(),

                TextColumn::make('tingkat_pelanggaran')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Berat' => 'danger',
                        'Sedang' => 'warning',
                        'Ringan' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('poin')
                    ->label('Poin')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 16 => 'danger',
                        $state >= 5 => 'warning',
                        default => 'success',
                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('d M Y H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->tooltip(fn ($record) => $record->updated_at?->format('d M Y H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Silakan tambahkan jenis pelanggaran terlebih dahulu.');
    }
}