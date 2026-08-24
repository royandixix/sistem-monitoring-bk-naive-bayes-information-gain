<?php

namespace App\Filament\Resources\LabelPerilakus\Pages;

use App\Filament\Resources\LabelPerilakus\LabelPerilakuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabelPerilakus extends ListRecords
{
    protected static string $resource =
        LabelPerilakuResource::class;

    protected static ?string $title =
        'Label Aktual Perilaku';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Label')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Label dari Guru BK digunakan sebagai ground truth untuk training dan pengujian model.';
    }
}