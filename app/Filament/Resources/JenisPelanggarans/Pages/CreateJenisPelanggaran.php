<?php

namespace App\Filament\Resources\JenisPelanggarans\Pages;

use App\Filament\Resources\JenisPelanggarans\JenisPelanggaranResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateJenisPelanggaran extends CreateRecord
{
    protected static string $resource = JenisPelanggaranResource::class;

    protected static ?string $title = 'Tambah Jenis Pelanggaran';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_jenis'] = strtoupper($data['kode_jenis']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body('Data jenis pelanggaran berhasil ditambahkan.')
            ->success();
    }
}