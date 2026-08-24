<?php

namespace App\Filament\Resources\InformationGainResults\Schemas;

use Filament\Schemas\Schema;

class InformationGainResultForm
{
    public static function configure(Schema $schema): Schema
    {
        // Resource bersifat read-only; form dipertahankan agar struktur resource lengkap.
        return $schema->components([]);
    }
}
