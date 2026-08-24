<?php

namespace App\Filament\Resources\InformationGainResults\Pages;

use App\Filament\Resources\InformationGainResults\InformationGainResultResource;
use Filament\Resources\Pages\ListRecords;

class ListInformationGainResults extends ListRecords
{
    protected static string $resource = InformationGainResultResource::class;

    protected static ?string $title = 'Ranking Information Gain';

    public function getSubheading(): ?string
    {
        return 'Nilai gain dihitung hanya dari tiga kriteria penelitian: Kerajinan, Kelakuan, dan Kerapian.';
    }
}
