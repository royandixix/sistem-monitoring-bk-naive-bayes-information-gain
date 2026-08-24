<?php

namespace App\Filament\Resources\JenisPelanggarans\Pages;

use App\Filament\Resources\JenisPelanggarans\JenisPelanggaranResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJenisPelanggaran extends EditRecord
{
    protected static string $resource = JenisPelanggaranResource::class;

    protected static ?string $title = 'Edit Jenis Pelanggaran';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Jenis Pelanggaran')
                ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->successNotificationTitle('Data berhasil dihapus.'),
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
            ->body('Perubahan data jenis pelanggaran berhasil disimpan.')
            ->success();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['kode_jenis'] = strtoupper($data['kode_jenis']);

        return $data;
    }
}