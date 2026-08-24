<?php

namespace App\Filament\Resources\Pelanggarans\Schemas;

use App\Models\JenisPelanggaran;
use App\Models\Siswa;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PelanggaranForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(1)
            ->components([
                Section::make(
                    'Laporan Pelanggaran Siswa'
                )
                    ->description(
                        'Laporan yang dibuat OSIS harus diperiksa dan disetujui oleh Guru BK.'
                    )
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Select::make('siswa_id')
                            ->label('Siswa')
                            ->relationship(
                                name: 'siswa',
                                titleAttribute: 'nama',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder =>
                                        $query
                                            ->with('kelas')
                                            ->where(
                                                'status',
                                                'Aktif'
                                            )
                                            ->orderBy('nis')
                            )
                            ->getOptionLabelFromRecordUsing(
                                function (
                                    Siswa $record
                                ): string {
                                    $kelas =
                                        $record
                                            ->kelas
                                            ?->nama_kelas
                                        ?? '-';

                                    return
                                        "{$record->nis} - " .
                                        "{$record->nama} - " .
                                        "{$kelas}";
                                }
                            )
                            ->searchable([
                                'nis',
                                'nama',
                            ])
                            ->preload()
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),

                        Select::make(
                            'jenis_pelanggaran_id'
                        )
                            ->label(
                                'Jenis Pelanggaran'
                            )
                            ->relationship(
                                name:
                                    'jenisPelanggaran',
                                titleAttribute:
                                    'nama_jenis',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder =>
                                        $query
                                            ->orderBy(
                                                'kode_jenis'
                                            )
                            )
                            ->getOptionLabelFromRecordUsing(
                                function (
                                    JenisPelanggaran $record
                                ): string {
                                    return
                                        "{$record->kode_jenis} - " .
                                        "{$record->nama_jenis} - " .
                                        "{$record->poin} poin";
                                }
                            )
                            ->searchable([
                                'kode_jenis',
                                'nama_jenis',
                            ])
                            ->preload()
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),

                        DatePicker::make('tanggal')
                            ->label(
                                'Tanggal Kejadian'
                            )
                            ->default(today())
                            ->maxDate(today())
                            ->native(false)
                            ->required(),

                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                'Ganjil' => 'Ganjil',
                                'Genap' => 'Genap',
                            ])
                            ->default('Genap')
                            ->native(false)
                            ->required(),

                        Select::make('tahun_ajaran')
                            ->label('Tahun Ajaran')
                            ->options(
                                function (): array {
                                    $tahun =
                                        now()->year;

                                    return collect(
                                        range(
                                            $tahun - 2,
                                            $tahun + 2
                                        )
                                    )
                                        ->mapWithKeys(
                                            function (
                                                int $item
                                            ): array {
                                                $periode =
                                                    $item .
                                                    '/' .
                                                    ($item + 1);

                                                return [
                                                    $periode =>
                                                        $periode,
                                                ];
                                            }
                                        )
                                        ->all();
                                }
                            )
                            ->default('2025/2026')
                            ->native(false)
                            ->required(),

                        Textarea::make('keterangan')
                            ->label(
                                'Kronologi / Keterangan'
                            )
                            ->placeholder(
                                'Tuliskan kronologi kejadian secara jelas.'
                            )
                            ->required()
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}