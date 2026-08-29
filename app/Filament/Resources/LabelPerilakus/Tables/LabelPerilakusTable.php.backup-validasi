<?php

namespace App\Filament\Resources\LabelPerilakus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LabelPerilakusTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'updated_at',
                'desc'
            )
            ->striped()
            ->columns([
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make(
                    'siswa.kelas.nama_kelas'
                )
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make(
                    'tahun_ajaran'
                )
                    ->label('Tahun Ajaran')
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->sortable(),

                TextColumn::make(
                    'label_aktual'
                )
                    ->label('Label Aktual')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state
                        ): string => match ($state) {
                            'Baik' =>
                                'success',

                            'Butuh Perhatian' =>
                                'warning',

                            'Bermasalah' =>
                                'danger',

                            default =>
                                'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make(
                    'labeledBy.name'
                )
                    ->label('Dinilai Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make(
                    'updated_at'
                )
                    ->label('Diperbarui')
                    ->dateTime(
                        'd M Y H:i'
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make(
                    'semester'
                )
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                    ]),

                SelectFilter::make(
                    'label_aktual'
                )
                    ->label('Label Aktual')
                    ->options([
                        'Baik' =>
                            'Baik',

                        'Butuh Perhatian' =>
                            'Butuh Perhatian',

                        'Bermasalah' =>
                            'Bermasalah',
                    ]),

                SelectFilter::make(
                    'siswa_id'
                )
                    ->label('Siswa')
                    ->relationship(
                        'siswa',
                        'nama'
                    )
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
                        ->label(
                            'Hapus Terpilih'
                        )
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading(
                'Belum ada label perilaku'
            )
            ->emptyStateDescription(
                'Tambahkan label aktual dari Guru BK sebelum menjalankan training model.'
            );
    }
}