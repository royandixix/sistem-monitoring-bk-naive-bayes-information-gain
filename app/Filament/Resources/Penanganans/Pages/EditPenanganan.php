<?php

namespace App\Filament\Resources\Penanganans\Pages;

use App\Filament\Resources\Penanganans\PenangananResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenanganan extends EditRecord
{
    protected static string $resource = PenangananResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isOsis()) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Penanganan')
                ->visible(fn (): bool => PenangananResource::canDelete($this->getRecord()))
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
