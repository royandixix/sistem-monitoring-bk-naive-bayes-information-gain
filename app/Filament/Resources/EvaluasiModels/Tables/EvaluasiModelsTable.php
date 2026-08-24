<?php

namespace App\Filament\Resources\EvaluasiModels\Tables;

use App\Models\EvaluasiModel;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class EvaluasiModelsTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'created_at',
                'desc'
            )
            ->striped()
            ->columns([
                TextColumn::make('metode')
                    ->label('Metode')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state
                        ): string => match ($state) {
                            'Naive Bayes + Information Gain' =>
                                'success',

                            'Naive Bayes' =>
                                'info',

                            default =>
                                'gray',
                        }
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make(
                    'tahun_ajaran'
                )
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make(
                    'jumlah_data_training'
                )
                    ->label('Training')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make(
                    'jumlah_data_testing'
                )
                    ->label('Testing')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('akurasi')
                    ->label('Akurasi')
                    ->suffix('%')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('precision')
                    ->label('Precision')
                    ->suffix('%')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('recall')
                    ->label('Recall')
                    ->suffix('%')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('f1_score')
                    ->label('F1 Score')
                    ->suffix('%')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make(
                    'training_ratio'
                )
                    ->label('Rasio Training')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state !== null
                            ? number_format(
                                (float) $state * 100,
                                0
                            ).'%'
                            : '-'
                    )
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),

                TextColumn::make(
                    'random_seed'
                )
                    ->label('Seed')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),

                TextColumn::make(
                    'created_at'
                )
                    ->label('Diproses')
                    ->dateTime(
                        'd M Y H:i'
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('metode')
                    ->options([
                        'Naive Bayes' =>
                            'Naive Bayes',

                        'Naive Bayes + Information Gain' =>
                            'Naive Bayes + Information Gain',
                    ]),

                SelectFilter::make(
                    'tahun_ajaran'
                )
                    ->label('Tahun Ajaran')
                    ->options(
                        fn (): array =>
                            EvaluasiModel::query()
                                ->whereNotNull(
                                    'tahun_ajaran'
                                )
                                ->distinct()
                                ->orderByDesc(
                                    'tahun_ajaran'
                                )
                                ->pluck(
                                    'tahun_ajaran',
                                    'tahun_ajaran'
                                )
                                ->toArray()
                    ),

                SelectFilter::make(
                    'semester'
                )
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                    ]),
            ])
            ->recordActions([
                Action::make(
                    'lihat_confusion_matrix'
                )
                    ->label('Confusion Matrix')
                    ->icon(
                        'heroicon-o-table-cells'
                    )
                    ->color('primary')
                    ->modalHeading(
                        fn (
                            EvaluasiModel $record
                        ): string =>
                            'Confusion Matrix - '.
                            $record->metode
                    )
                    ->modalDescription(
                        'Baris menunjukkan kelas aktual, sedangkan kolom menunjukkan kelas hasil prediksi.'
                    )
                    ->modalWidth(
                        Width::FiveExtraLarge
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(
                        'Tutup'
                    )
                    ->modalContent(
                        fn (
                            EvaluasiModel $record
                        ): View => view(
                            'filament.resources.evaluasi-models.confusion-matrix',
                            [
                                'record' => $record,
                            ]
                        )
                    ),
            ])
            ->emptyStateHeading(
                'Belum ada hasil evaluasi'
            )
            ->emptyStateDescription(
                'Jalankan proses klasifikasi untuk menghasilkan evaluasi model.'
            );
    }
}
