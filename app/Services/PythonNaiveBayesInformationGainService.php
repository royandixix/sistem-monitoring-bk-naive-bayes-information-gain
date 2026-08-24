<?php

namespace App\Services;

use App\Models\EvaluasiModel;
use App\Models\InformationGainResult;
use App\Models\Klasifikasi;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

class PythonNaiveBayesInformationGainService
{
    private array $classes = [
        'Baik',
        'Butuh Perhatian',
        'Bermasalah',
    ];

    private array $featureKeys = [
        'jumlah_pelanggaran_kategori',
        'total_poin_kategori',
        'kelakuan_kategori',
        'kerajinan_kategori',
        'kerapian_kategori',
        'kehadiran_kategori',
        'lainnya_kategori',
        'tingkat_dominan',
        'semester_terakhir',
    ];

    public function run(
        string $tahunAjaran,
        string $semester,
        float $trainingRatio = 0.8,
        int $randomSeed = 42,
    ): array {
        $tahunAjaran = trim($tahunAjaran);
        $semester = trim($semester);
        $trainingRatio = min(max($trainingRatio, 0.5), 0.9);
        $randomSeed = max(1, $randomSeed);

        if ($tahunAjaran === '') {
            return $this->failed('Tahun ajaran wajib diisi.');
        }

        if (! in_array($semester, ['Ganjil', 'Genap'], true)) {
            return $this->failed('Semester harus Ganjil atau Genap.');
        }

        $allSamples = $this->buildSamples($tahunAjaran, $semester);

        $labeledSamples = $allSamples
            ->filter(
                fn (array $sample): bool => in_array(
                    $sample['label'],
                    $this->classes,
                    true
                )
            )
            ->values();

        if ($allSamples->isEmpty()) {
            return $this->failed(
                'Tidak ada siswa aktif yang dapat diproses.'
            );
        }

        $classCounts = $labeledSamples->countBy('label');

        $insufficientClasses = collect($this->classes)
            ->filter(
                fn (string $class): bool => (int) (
                    $classCounts[$class] ?? 0
                ) < 2
            )
            ->values();

        if ($insufficientClasses->isNotEmpty()) {
            $details = collect($this->classes)
                ->map(
                    fn (string $class): string => $class.': '.
                        (int) ($classCounts[$class] ?? 0)
                )
                ->implode(', ');

            return $this->failed(
                'Data label aktual belum cukup. Setiap kelas minimal memiliki 2 siswa. '.
                'Jumlah saat ini: '.$details.
                '. Isi menu Label Perilaku terlebih dahulu.'
            );
        }

        $result = $this->callPython([
            'labeled_samples' => $labeledSamples->all(),
            'prediction_samples' => $allSamples->all(),
            'features' => $this->featureKeys,
            'classes' => $this->classes,
            'training_ratio' => $trainingRatio,
            'random_seed' => $randomSeed,
        ]);

        if (! ($result['success'] ?? false)) {
            return $result;
        }

        DB::transaction(function () use (
            $result,
            $tahunAjaran,
            $semester,
            $trainingRatio,
            $randomSeed,
        ): void {
            $this->saveInformationGainResults(
                results: $result['gain_results'] ?? [],
                selectedFeatures: $result['selected_features'] ?? [],
                jumlahData: (int) ($result['training_count'] ?? 0),
                tahunAjaran: $tahunAjaran,
                semester: $semester,
                randomSeed: $randomSeed,
            );

            foreach (($result['predictions'] ?? []) as $prediction) {
                Klasifikasi::query()->updateOrCreate(
                    [
                        'siswa_id' => $prediction['siswa_id'],
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                    ],
                    [
                        'jumlah_pelanggaran' =>
                            $prediction['jumlah_pelanggaran'],

                        'total_poin' =>
                            $prediction['total_poin'],

                        'hasil_klasifikasi' =>
                            $prediction['optimized']['class'],

                        'label_aktual' =>
                            $prediction['label'] ?: null,

                        'hasil_naive_bayes' =>
                            $prediction['baseline']['class'],

                        'probabilitas_naive_bayes' =>
                            $prediction['baseline']['probability'],

                        'hasil_ig_naive_bayes' =>
                            $prediction['optimized']['class'],

                        'probabilitas_ig_naive_bayes' =>
                            $prediction['optimized']['probability'],

                        'probabilitas' =>
                            $prediction['optimized']['probability'],

                        'probabilitas_detail' =>
                            $prediction['optimized']['probabilities'],

                        'fitur_klasifikasi' =>
                            $prediction['features'],

                        'information_gain_detail' => [
                            'selected_features' =>
                                $result['selected_features'] ?? [],

                            'ranking' =>
                                $result['gain_results'] ?? [],

                            'training_ratio' =>
                                $trainingRatio,

                            'random_seed' =>
                                $randomSeed,
                        ],

                        'metode' =>
                            'Naive Bayes + Information Gain',
                    ]
                );
            }

            $this->saveEvaluation(
                method: 'Naive Bayes',
                evaluation: $result['baseline_evaluation'],
                trainingCount: (int) $result['training_count'],
                testingCount: (int) $result['testing_count'],
                tahunAjaran: $tahunAjaran,
                semester: $semester,
                trainingRatio: $trainingRatio,
                randomSeed: $randomSeed,
            );

            $this->saveEvaluation(
                method: 'Naive Bayes + Information Gain',
                evaluation: $result['optimized_evaluation'],
                trainingCount: (int) $result['training_count'],
                testingCount: (int) $result['testing_count'],
                tahunAjaran: $tahunAjaran,
                semester: $semester,
                trainingRatio: $trainingRatio,
                randomSeed: $randomSeed,
            );
        });

        return $result;
    }

    private function callPython(array $payload): array
    {
        $python = trim(
            (string) env('PYTHON_BIN', 'python3')
        );

        $script = base_path(
            'python/naive_bayes_ig.py'
        );

        if ($python === '') {
            return $this->failed(
                'PYTHON_BIN pada file .env belum diisi.'
            );
        }

        if (! file_exists($script)) {
            return $this->failed(
                'File python/naive_bayes_ig.py tidak ditemukan.'
            );
        }

        try {
            $process = new Process(
                [
                    $python,
                    $script,
                ],
                base_path()
            );

            $process->setInput(
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE |
                    JSON_THROW_ON_ERROR
                )
            );

            $process->setTimeout(120);
            $process->run();
        } catch (Throwable $exception) {
            return $this->failed(
                'Gagal menjalankan Python: '.
                $exception->getMessage()
            );
        }

        if (! $process->isSuccessful()) {
            $error = trim(
                $process->getErrorOutput()
            );

            return $this->failed(
                $error !== ''
                    ? $error
                    : 'Python gagal menjalankan proses klasifikasi.'
            );
        }

        try {
            $output = json_decode(
                trim($process->getOutput()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $exception) {
            return $this->failed(
                'Output Python tidak valid: '.
                $exception->getMessage()
            );
        }

        if (! is_array($output)) {
            return $this->failed(
                'Output Python tidak berbentuk array JSON.'
            );
        }

        return $output;
    }

    private function buildSamples(
        string $tahunAjaran,
        string $semester
    ): Collection {
        return Siswa::query()
            ->with([
                'pelanggarans' => function ($query) use (
                    $tahunAjaran,
                    $semester
                ): void {
                    $query
                        ->where(
                            'tahun_ajaran',
                            $tahunAjaran
                        )
                        ->where(
                            'semester',
                            $semester
                        )
                        ->with(
                            'jenisPelanggaran'
                        )
                        ->orderBy('tanggal')
                        ->orderBy('id');
                },

                'labelPerilakus' => function ($query) use (
                    $tahunAjaran,
                    $semester
                ): void {
                    $query
                        ->where(
                            'tahun_ajaran',
                            $tahunAjaran
                        )
                        ->where(
                            'semester',
                            $semester
                        );
                },
            ])
            ->where('status', 'Aktif')
            ->orderBy('id')
            ->get()
            ->map(function (
                Siswa $siswa
            ) use (
                $semester
            ): array {
                $pelanggarans =
                    $siswa->pelanggarans;

                $jumlahPelanggaran =
                    $pelanggarans->count();

                $totalPoin =
                    $pelanggarans->sum(
                        fn ($item): int =>
                            (int) (
                                $item
                                    ->jenisPelanggaran
                                    ?->poin ?? 0
                            )
                    );

                $aspekCounts =
                    $this->countAspects(
                        $pelanggarans
                    );

                $tingkatCounts =
                    $this->countLevels(
                        $pelanggarans
                    );

                $features =
                    $this->makeFeatures(
                        jumlahPelanggaran:
                            $jumlahPelanggaran,

                        totalPoin:
                            $totalPoin,

                        aspekCounts:
                            $aspekCounts,

                        tingkatCounts:
                            $tingkatCounts,

                        semesterTerakhir:
                            $semester,
                    );

                $label =
                    $siswa
                        ->labelPerilakus
                        ->first()
                        ?->label_aktual;

                return [
                    'siswa_id' =>
                        $siswa->id,

                    'jumlah_pelanggaran' =>
                        $jumlahPelanggaran,

                    'total_poin' =>
                        $totalPoin,

                    'features' =>
                        $features,

                    'label' =>
                        $label,
                ];
            })
            ->values();
    }

    private function countAspects(
        Collection $pelanggarans
    ): array {
        $counts = [
            'Kelakuan' => 0,
            'Kerajinan' => 0,
            'Kerapian' => 0,
            'Kehadiran' => 0,
            'Lainnya' => 0,
        ];

        foreach ($pelanggarans as $pelanggaran) {
            $aspek =
                $pelanggaran
                    ->jenisPelanggaran
                    ?->aspek_pelanggaran
                ?: 'Lainnya';

            if (! array_key_exists(
                $aspek,
                $counts
            )) {
                $aspek = 'Lainnya';
            }

            $counts[$aspek]++;
        }

        return $counts;
    }

    private function countLevels(
        Collection $pelanggarans
    ): array {
        $counts = [
            'Ringan' => 0,
            'Sedang' => 0,
            'Berat' => 0,
        ];

        foreach ($pelanggarans as $pelanggaran) {
            $poin = (int) (
                $pelanggaran
                    ->jenisPelanggaran
                    ?->poin ?? 0
            );

            $tingkat =
                $pelanggaran
                    ->jenisPelanggaran
                    ?->tingkat_pelanggaran
                ?: $this->levelFromPoint($poin);

            if (! array_key_exists(
                $tingkat,
                $counts
            )) {
                $tingkat =
                    $this->levelFromPoint($poin);
            }

            $counts[$tingkat]++;
        }

        return $counts;
    }

    private function makeFeatures(
        int $jumlahPelanggaran,
        int $totalPoin,
        array $aspekCounts,
        array $tingkatCounts,
        string $semesterTerakhir,
    ): array {
        return [
            'jumlah_pelanggaran_kategori' =>
                $this->countCategory(
                    $jumlahPelanggaran
                ),

            'total_poin_kategori' =>
                $this->pointCategory(
                    $totalPoin
                ),

            'kelakuan_kategori' =>
                $this->countCategory(
                    $aspekCounts['Kelakuan'] ?? 0
                ),

            'kerajinan_kategori' =>
                $this->countCategory(
                    $aspekCounts['Kerajinan'] ?? 0
                ),

            'kerapian_kategori' =>
                $this->countCategory(
                    $aspekCounts['Kerapian'] ?? 0
                ),

            'kehadiran_kategori' =>
                $this->countCategory(
                    $aspekCounts['Kehadiran'] ?? 0
                ),

            'lainnya_kategori' =>
                $this->countCategory(
                    $aspekCounts['Lainnya'] ?? 0
                ),

            'tingkat_dominan' =>
                $this->dominantLevel(
                    $tingkatCounts
                ),

            'semester_terakhir' =>
                $semesterTerakhir,
        ];
    }

    private function countCategory(
        int $value
    ): string {
        return match (true) {
            $value <= 0 => 'Tidak Ada',
            $value <= 2 => 'Rendah',
            $value <= 5 => 'Sedang',
            default => 'Tinggi',
        };
    }

    private function pointCategory(
        int $value
    ): string {
        return match (true) {
            $value <= 0 => 'Tidak Ada',
            $value <= 15 => 'Rendah',
            $value <= 40 => 'Sedang',
            default => 'Tinggi',
        };
    }

    private function levelFromPoint(
        int $point
    ): string {
        return match (true) {
            $point <= 4 => 'Ringan',
            $point <= 15 => 'Sedang',
            default => 'Berat',
        };
    }

    private function dominantLevel(
        array $counts
    ): string {
        arsort($counts);

        $key = array_key_first(
            $counts
        );

        return ($counts[$key] ?? 0) > 0
            ? $key
            : 'Tidak Ada';
    }

    private function saveInformationGainResults(
        array $results,
        array $selectedFeatures,
        int $jumlahData,
        string $tahunAjaran,
        string $semester,
        int $randomSeed,
    ): void {
        InformationGainResult::query()
            ->where(
                'tahun_ajaran',
                $tahunAjaran
            )
            ->where(
                'semester',
                $semester
            )
            ->delete();

        foreach ($results as $result) {
            InformationGainResult::query()
                ->create([
                    'tahun_ajaran' =>
                        $tahunAjaran,

                    'semester' =>
                        $semester,

                    'fitur' =>
                        $result['feature'],

                    'gain' =>
                        $result['gain'],

                    'entropy_before' =>
                        $result['entropy_before'],

                    'entropy_after' =>
                        $result['entropy_after'],

                    'selected' =>
                        in_array(
                            $result['feature'],
                            $selectedFeatures,
                            true
                        ),

                    'metode' =>
                        'Information Gain',

                    'jumlah_data' =>
                        $jumlahData,

                    'ranking' =>
                        $result['ranking'],

                    'random_seed' =>
                        $randomSeed,

                    'detail' =>
                        $result['values'],
                ]);
        }
    }

    private function saveEvaluation(
        string $method,
        array $evaluation,
        int $trainingCount,
        int $testingCount,
        string $tahunAjaran,
        string $semester,
        float $trainingRatio,
        int $randomSeed,
    ): void {
        EvaluasiModel::query()->create([
            'metode' =>
                $method,

            'tahun_ajaran' =>
                $tahunAjaran,

            'semester' =>
                $semester,

            'jumlah_data_training' =>
                $trainingCount,

            'jumlah_data_testing' =>
                $testingCount,

            'training_ratio' =>
                $trainingRatio,

            'random_seed' =>
                $randomSeed,

            'akurasi' =>
                $evaluation['akurasi'],

            'precision' =>
                $evaluation['precision'],

            'recall' =>
                $evaluation['recall'],

            'f1_score' =>
                $evaluation['f1_score'],

            'confusion_matrix' =>
                $evaluation[
                    'confusion_matrix'
                ],

            'selected_features' =>
                $evaluation['features'] ?? [],

            'keterangan' =>
                sprintf(
                    'Rasio training %.2f, random seed %d. Fitur: %s',
                    $trainingRatio,
                    $randomSeed,
                    implode(
                        ', ',
                        $evaluation['features'] ?? []
                    )
                ),
        ]);
    }

    private function failed(
        string $message
    ): array {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}