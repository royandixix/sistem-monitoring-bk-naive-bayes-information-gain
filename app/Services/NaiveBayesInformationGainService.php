<?php

namespace App\Services;

/**
 * Compatibility wrapper.
 *
 * Seluruh proses akademik Naive Bayes + Information Gain dipusatkan pada
 * PythonNaiveBayesInformationGainService agar tidak ada dua implementasi
 * berbeda yang dapat menghasilkan hasil penelitian yang tidak konsisten.
 */
class NaiveBayesInformationGainService
{
    public function __construct(
        private readonly PythonNaiveBayesInformationGainService $pythonService,
    ) {
    }

    public function run(
        ?string $tahunAjaran = null,
        ?string $semester = null,
        float $trainingRatio = 0.7,
    ): array {
        $tahunAjaran = trim((string) $tahunAjaran);
        $semester = trim((string) $semester);

        if ($tahunAjaran === '' || $semester === '') {
            return [
                'success' => false,
                'message' => 'Tahun ajaran dan semester wajib diisi.',
            ];
        }

        return $this->pythonService->run(
            tahunAjaran: $tahunAjaran,
            semester: $semester,
            trainingRatio: $trainingRatio,
            randomSeed: 42,
        );
    }
}
