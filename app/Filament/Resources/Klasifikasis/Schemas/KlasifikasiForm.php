<?php

namespace App\Filament\Resources\Klasifikasis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KlasifikasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Siswa')
                    ->columns(2)
                    ->schema([
                        Select::make('siswa_id')
                            ->label('Siswa')
                            ->relationship('siswa', 'nama')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => trim(($record->nis ?? '-').' - '.($record->nama ?? '-').' - '.($record->kelas?->nama_kelas ?? '-')))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('label_aktual')
                            ->label('Label Aktual')
                            ->options([
                                'Baik' => 'Baik',
                                'Butuh Perhatian' => 'Butuh Perhatian',
                                'Bermasalah' => 'Bermasalah',
                            ])
                            ->native(false),
                    ]),

                Section::make('Rekap Pelanggaran')
                    ->columns(2)
                    ->schema([
                        TextInput::make('jumlah_pelanggaran')
                            ->label('Jumlah Pelanggaran')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('total_poin')
                            ->label('Total Poin')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ]),

                Section::make('Hasil Algoritma')
                    ->columns(2)
                    ->schema([
                        Select::make('hasil_klasifikasi')
                            ->label('Hasil Klasifikasi Akhir')
                            ->options([
                                'Baik' => 'Baik',
                                'Butuh Perhatian' => 'Butuh Perhatian',
                                'Bermasalah' => 'Bermasalah',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('probabilitas')
                            ->label('Probabilitas Akhir')
                            ->numeric()
                            ->step(0.000001),

                        Select::make('hasil_naive_bayes')
                            ->label('Hasil Naive Bayes')
                            ->options([
                                'Baik' => 'Baik',
                                'Butuh Perhatian' => 'Butuh Perhatian',
                                'Bermasalah' => 'Bermasalah',
                            ])
                            ->native(false),

                        TextInput::make('probabilitas_naive_bayes')
                            ->label('Probabilitas Naive Bayes')
                            ->numeric()
                            ->step(0.000001),

                        Select::make('hasil_ig_naive_bayes')
                            ->label('Hasil Naive Bayes + Information Gain')
                            ->options([
                                'Baik' => 'Baik',
                                'Butuh Perhatian' => 'Butuh Perhatian',
                                'Bermasalah' => 'Bermasalah',
                            ])
                            ->native(false),

                        TextInput::make('probabilitas_ig_naive_bayes')
                            ->label('Probabilitas Naive Bayes + Information Gain')
                            ->numeric()
                            ->step(0.000001),

                        TextInput::make('metode')
                            ->label('Metode')
                            ->default('Python Naive Bayes + Information Gain')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),

                Section::make('Detail Perhitungan')
                    ->columns(1)
                    ->schema([
                        Textarea::make('fitur_klasifikasi')
                            ->label('Fitur Klasifikasi')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $state)
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                            ->rows(7),

                        Textarea::make('probabilitas_detail')
                            ->label('Detail Probabilitas')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $state)
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                            ->rows(7),

                        Textarea::make('information_gain_detail')
                            ->label('Detail Information Gain')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $state)
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                            ->rows(10),
                    ]),
            ]);
    }
}
