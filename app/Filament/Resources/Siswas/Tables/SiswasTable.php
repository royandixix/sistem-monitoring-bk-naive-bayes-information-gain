<?php

namespace App\Filament\Resources\Siswas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama')
            ->striped()
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jk')
                    ->label('JK')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('nama_ayah')
                    ->label('Nama Ayah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nama_ibu')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('no_hp_ortu')
                    ->label('HP Orang Tua')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Lulus' => 'info',
                        'Pindah' => 'warning',
                        'Keluar' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

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
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('jk')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Lulus' => 'Lulus',
                        'Pindah' => 'Pindah',
                        'Keluar' => 'Keluar',
                    ]),
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
            ->emptyStateHeading('Belum ada data siswa')
            ->emptyStateDescription('Silakan tambahkan data siswa terlebih dahulu.');
    }
}
