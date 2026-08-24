<?php

namespace App\Filament\Resources\Penanganans\Pages;

use App\Filament\Resources\Penanganans\PenangananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenanganans extends ListRecords
{
    protected static string $resource = PenangananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Penanganan'),
        ];
    }
}
