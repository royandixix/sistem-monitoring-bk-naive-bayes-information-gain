<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    public function getTitle(): string
    {
        return 'Data Kelas';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola data kelas untuk Sistem Monitoring Bimbingan Konseling.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kelas')
                ->icon('heroicon-o-plus'),
        ];
    }
}