<?php

namespace App\Filament\Resources\LabelPerilakus\Pages;

use App\Filament\Resources\LabelPerilakus\LabelPerilakuResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLabelPerilaku extends CreateRecord
{
    protected static string $resource =
        LabelPerilakuResource::class;

    protected static ?string $title =
        'Tambah Label Perilaku';

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {
        $data['labeled_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this
            ->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body(
                'Label perilaku berhasil ditambahkan.'
            )
            ->success();
    }
}