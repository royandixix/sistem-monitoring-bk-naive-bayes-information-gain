<?php

namespace App\Filament\Resources\Klasifikasis\Pages;

use App\Filament\Resources\Klasifikasis\KlasifikasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKlasifikasi extends EditRecord
{
    protected static string $resource = KlasifikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Klasifikasi')
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
