<?php
namespace App\Filament\Resources\Kelas\Schemas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
class KelasForm
{
    public static function configure(Schema $schema):Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kelas')
                    ->label('Kode Kelas')
                    ->placeholder('Contoh: VII-A')
                    ->required()
                    ->maxLength(50)
                    ->autocomplete(false),
                TextInput::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->placeholder('Contoh: VII A')
                    ->required()
                    ->maxLength(100)
                    ->autocomplete(false),
                Select::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(function():array{
                        $tahunSekarang=now()->year;
                        return collect(range($tahunSekarang-3,$tahunSekarang+3))
                            ->mapWithKeys(function(int $tahun):array{
                                $tahunAjaran=$tahun.'/'.($tahun+1);
                                return[$tahunAjaran=>$tahunAjaran];
                            })
                            ->all();
                    })
                    ->default(function():string{
                        $tahun=now()->year;
                        if(now()->month<7){
                            $tahun--;
                        }
                        return $tahun.'/'.($tahun+1);
                    })
                    ->placeholder('Pilih tahun ajaran')
                    ->native(false)
                    ->searchable()
                    ->required(),
            ])
            ->columns([
                'default'=>1,
                'md'=>2,
            ]);
    }
}