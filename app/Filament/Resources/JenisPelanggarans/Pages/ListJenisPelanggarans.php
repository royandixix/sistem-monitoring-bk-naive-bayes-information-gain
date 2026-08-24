<?php

namespace App\Filament\Resources\JenisPelanggarans\Pages;

use App\Filament\Resources\JenisPelanggarans\JenisPelanggaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJenisPelanggarans extends ListRecords
{
    protected static string $resource = JenisPelanggaranResource::class;

    protected static ?string $title = 'Data Jenis Pelanggaran';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jenis Pelanggaran')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}