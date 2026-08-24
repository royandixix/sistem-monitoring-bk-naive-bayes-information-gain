<?php

namespace App\Filament\Resources\EvaluasiModels\Pages;

use App\Filament\Resources\EvaluasiModels\EvaluasiModelResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvaluasiModel extends EditRecord
{
    protected static string $resource = EvaluasiModelResource::class;

    protected static ?string $title = 'Edit Evaluasi Model';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Data Evaluasi')
                ->modalDescription('Apakah Anda yakin ingin menghapus data evaluasi model ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->successNotificationTitle('Data evaluasi berhasil dihapus.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body('Perubahan data evaluasi model berhasil disimpan.')
            ->success();
    }
}