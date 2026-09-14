<?php

namespace App\Filament\Resources\Pelanggarans\Pages;

use App\Filament\Resources\Pelanggarans\PelanggaranResource;
use App\Models\Kelas;
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
                ->visible(fn(): bool => PelanggaranResource::canCreate()),


            Action::make('export_laporan')

                ->label('Export Laporan')

                ->icon('heroicon-o-arrow-down-tray')

                ->color('success')

                ->visible(
                    fn(): bool =>
                    auth()->user()?->hasAnyRole([
                        'super_admin',
                        'kepala_sekolah'
                    ]) ?? false
                )


                ->modalHeading('Export Laporan Pelanggaran Resmi')


                ->modalDescription(
                    'Laporan hanya mengambil data pelanggaran yang sudah disetujui.'
                )


                ->modalSubmitActionLabel('Download')


                ->schema([


                    Select::make('format')

                        ->label('Format')

                        ->options([
                            'pdf'=>'PDF',
                            'excel'=>'Excel'
                        ])

                        ->default('pdf')

                        ->required(),



                    TextInput::make('tahun_ajaran')

                        ->label('Tahun Ajaran')

                        ->default('2025/2026'),



                    Select::make('semester')

                        ->label('Semester')

                        ->options([
                            'Ganjil'=>'Ganjil',
                            'Genap'=>'Genap'
                        ])

                        ->default('Ganjil'),



                    Select::make('kelas_id')

                        ->label('Kelas')

                        ->options(

                            fn(): array =>

                            Kelas::query()

                                ->orderBy('nama_kelas')

                                ->pluck(
                                    'nama_kelas',
                                    'id'
                                )

                                ->toArray()

                        )

                        ->searchable()

                        ->preload(),



                    Select::make('siswa_id')

                        ->label('Siswa')

                        ->options(

                            fn(): array =>

                            Siswa::query()

                                ->orderBy('nama')

                                ->get()

                                ->mapWithKeys(

                                    fn(Siswa $siswa): array => [

                                        $siswa->id =>

                                        $siswa->nis.' - '.$siswa->nama

                                    ]

                                )

                                ->toArray()

                        )

                        ->searchable()

                        ->preload(),

                ])



                ->action(function(array $data){

                    session()->put(
                        'export_pelanggaran_filters',
                        $data
                    );


                    if(($data['format'] ?? 'pdf') === 'excel'){

                        return redirect()
                            ->to('/admin/export-pelanggaran-excel');

                    }


                    return redirect()
                        ->to('/admin/export-pelanggaran-pdf');

                }),


        ];
    }
}