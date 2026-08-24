<?php
namespace App\Filament\Resources\Kelas\Tables;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class KelasTable
{
    public static function configure(Table $table):Table
    {
        return $table
            ->defaultSort('nama_kelas')
            ->striped()
            ->columns([
                TextColumn::make('kode_kelas')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->tooltip(fn($record):?string=>$record->created_at?->format('d M Y H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->tooltip(fn($record):?string=>$record->updated_at?->format('d M Y H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Kelas')
                        ->modalDescription('Data kelas yang masih digunakan oleh siswa tidak dapat dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ]),
            ])
            ->emptyStateHeading('Belum ada data kelas')
            ->emptyStateDescription('Silakan tambahkan kelas terlebih dahulu sebelum menginput data siswa.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}