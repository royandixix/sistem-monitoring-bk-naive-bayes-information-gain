<?php

namespace App\Filament\Resources\Klasifikasis\Pages;

use App\Filament\Resources\Klasifikasis\KlasifikasiResource;
use App\Services\PythonNaiveBayesInformationGainService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListKlasifikasis extends ListRecords
{
    protected static string $resource =
        KlasifikasiResource::class;

    protected static ?string $title =
        'Klasifikasi Perilaku Siswa';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('proses_algoritma')
                ->label(
                    'Proses Naive Bayes + Information Gain'
                )
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->visible(
                    fn (): bool =>
                        auth()
                            ->user()
                            ?->isGuruBk()
                        ?? false
                )
                ->modalHeading(
                    'Proses Klasifikasi Naive Bayes + Information Gain'
                )
                ->modalDescription(
                    'Hanya pelanggaran yang sudah disetujui Guru BK yang dihitung. Pembagian data ditetapkan 70% training dan 30% testing sesuai metode penelitian.'
                )
                ->modalSubmitActionLabel(
                    'Proses Sekarang'
                )
                ->schema([
                    TextInput::make(
                        'tahun_ajaran'
                    )
                        ->label('Tahun Ajaran')
                        ->default('2025/2026')
                        ->placeholder('2025/2026')
                        ->required()
                        ->maxLength(20),

                    Select::make('semester')
                        ->label('Semester')
                        ->options([
                            'Ganjil' => 'Ganjil',
                            'Genap' => 'Genap',
                        ])
                        ->required()
                        ->native(false),

                    TextInput::make(
                        'random_seed'
                    )
                        ->label('Random Seed')
                        ->helperText(
                            'Gunakan nilai tetap agar hasil pembagian data dapat direproduksi.'
                        )
                        ->numeric()
                        ->integer()
                        ->default(42)
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (
                    array $data
                ): void {
                    try {
                        $result = app(
                            PythonNaiveBayesInformationGainService::class
                        )->run(
                            tahunAjaran:
                                (string) $data[
                                    'tahun_ajaran'
                                ],

                            semester:
                                (string) $data[
                                    'semester'
                                ],

                            trainingRatio: 0.7,

                            randomSeed:
                                (int) $data[
                                    'random_seed'
                                ],
                        );
                    } catch (
                        Throwable $exception
                    ) {
                        Notification::make()
                            ->title(
                                'Proses klasifikasi gagal'
                            )
                            ->body(
                                $exception
                                    ->getMessage()
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    if (! (
                        $result['success'] ?? false
                    )) {
                        Notification::make()
                            ->title(
                                'Proses klasifikasi gagal'
                            )
                            ->body(
                                $result['message']
                                ?? 'Terjadi kesalahan saat memproses klasifikasi.'
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    $accuracy = (float) data_get(
                        $result,
                        'optimized_evaluation.akurasi',
                        0
                    );

                    $selectedFeatures = collect(
                        $result[
                            'selected_features'
                        ] ?? []
                    )
                        ->map(
                            fn (string $feature): string => match ($feature) {
                                'kerajinan_kategori' => 'Kerajinan',
                                'kelakuan_kategori' => 'Kelakuan',
                                'kerapian_kategori' => 'Kerapian',
                                default => $feature,
                            }
                        )
                        ->implode(', ');

                    Notification::make()
                        ->title(
                            'Klasifikasi berhasil diproses'
                        )
                        ->body(
                            'Total siswa: '.
                            number_format(
                                (int) (
                                    $result[
                                        'total_samples'
                                    ] ?? 0
                                )
                            ).
                            '. Training 70%: '.
                            number_format(
                                (int) (
                                    $result[
                                        'training_count'
                                    ] ?? 0
                                )
                            ).
                            ', testing 30%: '.
                            number_format(
                                (int) (
                                    $result[
                                        'testing_count'
                                    ] ?? 0
                                )
                            ).
                            '. Akurasi NB + IG: '.
                            number_format(
                                $accuracy,
                                2
                            ).
                            '%'.
                            (
                                $selectedFeatures !== ''
                                ? '. Fitur terpilih: '.
                                    $selectedFeatures
                                : ''
                            )
                        )
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Metode penelitian dikunci pada pembagian 70% data training dan 30% data testing. Hasil klasifikasi dibuat otomatis dan tidak dapat diedit secara manual.';
    }
}