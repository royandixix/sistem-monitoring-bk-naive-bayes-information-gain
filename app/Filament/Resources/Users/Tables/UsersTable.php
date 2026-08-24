<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'super_admin' => 'Guru BK',
                        'admin' => 'OSIS',
                        'kepala_sekolah' => 'Kepala Sekolah',
                        'wali_murid' => 'Wali Murid',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'kepala_sekolah' => 'success',
                        'wali_murid' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('jenis_kelamin')
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

                TextColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum diverifikasi')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Guru BK',
                        'admin' => 'OSIS',
                        'kepala_sekolah' => 'Kepala Sekolah',
                        'wali_murid' => 'Wali Murid',
                    ]),

                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload(),
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
            ->emptyStateHeading('Belum ada user')
            ->emptyStateDescription('Silakan tambahkan user untuk mengelola akses sistem.');
    }
}