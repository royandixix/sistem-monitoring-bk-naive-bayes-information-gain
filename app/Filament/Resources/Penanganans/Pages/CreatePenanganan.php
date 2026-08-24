<?php

namespace App\Filament\Resources\Penanganans\Pages;

use App\Filament\Resources\Penanganans\PenangananResource;
use App\Models\Pelanggaran;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePenanganan extends CreateRecord
{
    protected static string $resource = PenangananResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pelanggaran = Pelanggaran::query()->find($data['pelanggaran_id'] ?? null);

        if (! $pelanggaran?->isDisetujui()) {
            throw ValidationException::withMessages([
                'data.pelanggaran_id' => 'Penanganan hanya dapat dibuat untuk pelanggaran yang sudah disetujui Guru BK.',
            ]);
        }

        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
