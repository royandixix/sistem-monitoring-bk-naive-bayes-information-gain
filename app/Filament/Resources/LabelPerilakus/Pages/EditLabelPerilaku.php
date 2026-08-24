<?php

namespace App\Filament\Resources\LabelPerilakus\Pages;

use App\Filament\Resources\LabelPerilakus\LabelPerilakuResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLabelPerilaku extends EditRecord
{
    protected static string $resource =
        LabelPerilakuResource::class;

    protected static ?string $title =
        'Edit Label Perilaku';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation(),
        ];
    }

    protected function mutateFormDataBeforeSave(
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

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body(
                'Label perilaku berhasil diperbarui.'
            )
            ->success();
    }
}