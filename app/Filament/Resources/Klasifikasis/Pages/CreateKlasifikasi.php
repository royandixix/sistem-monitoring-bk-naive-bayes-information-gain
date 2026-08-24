<?php

namespace App\Filament\Resources\Klasifikasis\Pages;

use App\Filament\Resources\Klasifikasis\KlasifikasiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKlasifikasi extends CreateRecord
{
    protected static string $resource = KlasifikasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
