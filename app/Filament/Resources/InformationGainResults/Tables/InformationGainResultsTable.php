<?php

namespace App\Filament\Resources\InformationGainResults\Tables;

use App\Models\InformationGainResult;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InformationGainResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ranking')
            ->striped()
            ->columns([
                TextColumn::make('ranking')
                    ->label('Ranking')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('fitur')
                    ->label('Kriteria / Fitur')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'kerajinan_kategori' => 'Kerajinan',
                        'kelakuan_kategori' => 'Kelakuan',
                        'kerapian_kategori' => 'Kerapian',
                        default => (string) $state,
                    })
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gain')
                    ->label('Information Gain')
                    ->numeric(decimalPlaces: 10)
                    ->sortable(),

                TextColumn::make('entropy_before')
                    ->label('Entropy Awal')
                    ->numeric(decimalPlaces: 10)
                    ->toggleable(),

                TextColumn::make('entropy_after')
                    ->label('Entropy Sesudah')
                    ->numeric(decimalPlaces: 10)
                    ->toggleable(),

                IconColumn::make('selected')
                    ->label('Terpilih')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('jumlah_data')
                    ->label('Data Training')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('random_seed')
                    ->label('Seed')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diproses')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(fn (): array => InformationGainResult::query()
                        ->whereNotNull('tahun_ajaran')
                        ->distinct()
                        ->orderByDesc('tahun_ajaran')
                        ->pluck('tahun_ajaran', 'tahun_ajaran')
                        ->toArray()),

                SelectFilter::make('semester')
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                    ]),

                SelectFilter::make('selected')
                    ->label('Status Fitur')
                    ->options([
                        1 => 'Terpilih',
                        0 => 'Tidak Terpilih',
                    ]),
            ])
            ->emptyStateHeading('Belum ada hasil Information Gain')
            ->emptyStateDescription('Jalankan proses Naive Bayes + Information Gain dari menu Klasifikasi.');
    }
}
