<?php

namespace App\Filament\Resources\Penanganans\Schemas;

use App\Models\Pelanggaran;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PenangananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Pelanggaran')
                    ->description('Penanganan hanya dapat dibuat untuk pelanggaran yang sudah disetujui Guru BK.')
                    ->columns(2)
                    ->schema([
                        Select::make('pelanggaran_id')
                            ->label('Pelanggaran Siswa')
                            ->relationship(
                                name: 'pelanggaran',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn (Builder $query): Builder =>
                                    $query
                                        ->disetujui()
                                        ->with(['siswa', 'jenisPelanggaran'])
                                        ->latest('tanggal')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Pelanggaran $record): string => trim(
                                    ($record->siswa?->nama ?? 'Siswa tidak ditemukan').
                                    ' - '.
                                    ($record->jenisPelanggaran?->nama_jenis ?? 'Pelanggaran tidak ditemukan').
                                    ' - '.
                                    ($record->tanggal?->format('d-m-Y') ?? '-')
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('tanggal_penanganan')
                            ->label('Tanggal Penanganan')
                            ->default(today())
                            ->maxDate(today())
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Detail Penanganan')
                    ->columns(2)
                    ->schema([
                        Select::make('tindakan')
                            ->label('Tindakan')
                            ->options([
                                'Teguran Lisan' => 'Teguran Lisan',
                                'Teguran Tertulis' => 'Teguran Tertulis',
                                'Pemanggilan Orang Tua' => 'Pemanggilan Orang Tua',
                                'Konseling' => 'Konseling',
                                'Skorsing' => 'Skorsing',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('user_id')
                            ->label('Petugas Penanganan')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id())
                            ->disabled(fn (): bool => auth()->user()?->isOsis() ?? false)
                            ->dehydrated()
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Catatan')
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Penanganan')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
