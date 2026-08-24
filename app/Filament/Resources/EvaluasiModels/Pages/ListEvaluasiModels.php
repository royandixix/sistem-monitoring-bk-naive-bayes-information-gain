<?php

namespace App\Filament\Resources\EvaluasiModels\Pages;

use App\Filament\Resources\EvaluasiModels\EvaluasiModelResource;
use Filament\Resources\Pages\ListRecords;

class ListEvaluasiModels extends ListRecords
{
    protected static string $resource =
        EvaluasiModelResource::class;

    protected static ?string $title =
        'Evaluasi Model';

    public function getSubheading(): ?string
    {
        return 'Nilai evaluasi dibuat otomatis oleh proses algoritma dan tidak dapat diedit manual.';
    }
}