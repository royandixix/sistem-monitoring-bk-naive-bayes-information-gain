<?php

namespace App\Filament\Resources\JenisPelanggarans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JenisPelanggaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Jenis Pelanggaran')
                    ->description('Masukkan informasi mengenai jenis pelanggaran siswa.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('kode_jenis')
                            ->label('Kode Pelanggaran')
                            ->placeholder('Contoh: JP001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->autocomplete(false),

                        TextInput::make('nama_jenis')
                            ->label('Nama Jenis Pelanggaran')
                            ->placeholder('Contoh: Terlambat masuk sekolah')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false),

                        Select::make('aspek_pelanggaran')
                            ->label('Aspek Pelanggaran')
                            ->options([
                                'Kerajinan' => 'Kerajinan',
                                'Kelakuan' => 'Kelakuan',
                                'Kerapian' => 'Kerapian',
                            ])
                            ->default('Kelakuan')
                            ->required()
                            ->native(false),

                        Select::make('tingkat_pelanggaran')
                            ->label('Tingkat Pelanggaran')
                            ->options([
                                'Ringan' => 'Ringan',
                                'Sedang' => 'Sedang',
                                'Berat' => 'Berat',
                            ])
                            ->default('Ringan')
                            ->required()
                            ->native(false),

                        TextInput::make('poin')
                            ->label('Poin Pelanggaran')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan tambahan jika diperlukan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}