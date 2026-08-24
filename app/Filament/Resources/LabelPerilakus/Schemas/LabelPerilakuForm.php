<?php

namespace App\Filament\Resources\LabelPerilakus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LabelPerilakuForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(1)
            ->components([
                Section::make(
                    'Label Aktual dari Guru BK'
                )
                    ->description(
                        'Label ini menjadi ground truth untuk proses training dan pengujian model.'
                    )
                    ->columns(2)
                    ->schema([
                        Select::make('siswa_id')
                            ->label('Siswa')
                            ->relationship(
                                'siswa',
                                'nama'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string =>
                                    trim(
                                        ($record->nis ?? '-').
                                        ' - '.
                                        ($record->nama ?? '-').
                                        ' - '.
                                        (
                                            $record
                                                ->kelas
                                                ?->nama_kelas
                                            ?? '-'
                                        )
                                    )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        TextInput::make(
                            'tahun_ajaran'
                        )
                            ->label('Tahun Ajaran')
                            ->default('2025/2026')
                            ->placeholder('2025/2026')
                            ->required()
                            ->maxLength(20),

                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                'Ganjil' => 'Ganjil',
                                'Genap' => 'Genap',
                            ])
                            ->required()
                            ->native(false),

                        Select::make(
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
                            ])
                            ->required()
                            ->native(false),

                        Textarea::make('catatan')
                            ->label(
                                'Dasar Penilaian / Catatan Guru BK'
                            )
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}