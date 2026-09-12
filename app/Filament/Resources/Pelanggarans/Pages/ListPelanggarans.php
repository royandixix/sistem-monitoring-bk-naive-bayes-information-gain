<?php

namespace App\Filament\Resources\Pelanggarans\Pages;

use App\Filament\Resources\Pelanggarans\PelanggaranResource;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListPelanggarans extends ListRecords
{
    protected static string $resource = PelanggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pelanggaran')
                ->visible(fn (): bool => PelanggaranResource::canCreate()),

            Action::make('export_laporan')
                ->label('Export Laporan')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                    'super_admin',
                    'kepala_sekolah',
                ]) ?? false)
                ->modalHeading('Export Laporan Pelanggaran Resmi')
                ->modalDescription('Laporan hanya memuat data pelanggaran yang sudah disetujui Guru BK.')
                ->modalSubmitActionLabel('Download')
                ->schema([
                    Select::make('format')
                        ->label('Format')
                        ->options([
                            'pdf' => 'PDF',
                            'excel' => 'Excel (.xls)',
                        ])
                        ->default('pdf')
                        ->required()
                        ->native(false),

                    TextInput::make('tahun_ajaran')
                        ->label('Tahun Ajaran')
                        ->default('2025/2026'),

                    Select::make('semester')
                        ->label('Semester')
                        ->options([
                            'Ganjil' => 'Ganjil',
                            'Genap' => 'Genap',
                        ])
                        ->native(false),

                    Select::make('kelas_id')
                        ->label('Kelas (opsional)')
                        ->options(fn (): array => Kelas::query()
                            ->orderBy('nama_kelas')
                            ->pluck('nama_kelas', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('siswa_id')
                        ->label('Siswa (opsional)')
                        ->options(fn (): array => Siswa::query()
                            ->orderBy('nama')
                            ->get()
                            ->mapWithKeys(fn (Siswa $siswa): array => [
                                $siswa->id => $siswa->nis.' - '.$siswa->nama,
                            ])
                            ->all())
                        ->searchable()
                        ->preload()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    session()->put('export_pelanggaran_filters', $data);

                    if (($data['format'] ?? 'pdf') === 'excel') {
                        return redirect()->route('export.pelanggaran.excel');
                    }

                    return redirect()->route('export.pelanggaran.pdf');
                }),
        ];
    }
}