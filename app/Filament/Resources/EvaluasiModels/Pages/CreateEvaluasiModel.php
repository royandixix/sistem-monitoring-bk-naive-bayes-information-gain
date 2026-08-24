<?php

namespace App\Filament\Resources\EvaluasiModels\Pages;

use App\Filament\Resources\EvaluasiModels\EvaluasiModelResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluasiModel extends CreateRecord
{
    protected static string $resource = EvaluasiModelResource::class;

    protected static ?string $title = 'Tambah Evaluasi Model';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body('Data evaluasi model berhasil ditambahkan.')
            ->success();
    }
}