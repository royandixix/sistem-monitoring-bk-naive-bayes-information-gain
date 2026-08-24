<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKelas extends CreateRecord
{
    protected static string $resource = KelasResource::class;

    public function getTitle(): string
    {
        return 'Tambah Data Kelas';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nama_kelas'] = strtoupper($data['nama_kelas']);

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data kelas berhasil ditambahkan.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}