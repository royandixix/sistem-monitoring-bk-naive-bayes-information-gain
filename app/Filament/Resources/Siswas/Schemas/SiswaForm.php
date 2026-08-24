<?php

namespace App\Filament\Resources\Siswas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Siswa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nis')
                            ->label('NIS')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->autocomplete(false),

                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false),

                        Select::make('jk')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->relationship('kelas', 'nama_kelas')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(100)
                            ->autocomplete(false),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->native(false),

                        Select::make('status')
                            ->label('Status Siswa')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Lulus' => 'Lulus',
                                'Pindah' => 'Pindah',
                                'Keluar' => 'Keluar',
                            ])
                            ->default('Aktif')
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Data Orang Tua')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah')
                            ->maxLength(255)
                            ->autocomplete(false),

                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu')
                            ->maxLength(255)
                            ->autocomplete(false),

                        TextInput::make('no_hp_ortu')
                            ->label('Nomor HP Orang Tua')
                            ->tel()
                            ->maxLength(30)
                            ->autocomplete(false),
                    ]),

                Section::make('Alamat')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
