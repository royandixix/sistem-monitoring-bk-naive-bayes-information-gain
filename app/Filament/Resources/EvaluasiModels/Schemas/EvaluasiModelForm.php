<?php

namespace App\Filament\Resources\EvaluasiModels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EvaluasiModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('metode')
                    ->label('Metode Evaluasi')
                    ->placeholder('Contoh: Decision Tree, Random Forest')
                    ->required()
                    ->maxLength(100),

                TextInput::make('jumlah_data_training')
                    ->label('Jumlah Data Training')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->placeholder('1000'),

                TextInput::make('jumlah_data_testing')
                    ->label('Jumlah Data Testing')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->placeholder('250'),

                TextInput::make('akurasi')
                    ->label('Akurasi')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                TextInput::make('precision')
                    ->label('Precision')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                TextInput::make('recall')
                    ->label('Recall')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                TextInput::make('f1_score')
                    ->label('F1-Score')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                Textarea::make('confusion_matrix')
                    ->label('Confusion Matrix')
                    ->rows(6)
                    ->placeholder('Masukkan hasil confusion matrix...')
                    ->columnSpanFull(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(4)
                    ->placeholder('Tambahkan catatan atau hasil analisis...')
                    ->columnSpanFull(),
            ]);
    }
}