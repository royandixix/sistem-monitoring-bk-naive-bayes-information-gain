<?php

namespace App\Services;

class KlasifikasiAutoRefreshService
{
    public function __construct(
        private readonly PythonNaiveBayesInformationGainService $service,
    ) {
    }

    /**
     * Memperbarui hasil klasifikasi setelah data pelanggaran resmi berubah.
     * Jika knowledge model sudah tersedia, proses hanya melakukan prediksi
     * real-time. Training 70:30 dilakukan otomatis hanya saat model periode
     * tersebut memang belum pernah dibentuk.
     */
    public function refresh(?string $tahunAjaran, ?string $semester): array
    {
        $tahunAjaran = trim((string) $tahunAjaran);
        $semester = trim((string) $semester);

        if ($tahunAjaran === '' || ! in_array($semester, ['Ganjil', 'Genap'], true)) {
            return [
                'success' => false,
                'message' => 'Periode pelanggaran tidak valid untuk refresh klasifikasi.',
            ];
        }

        $prediction = $this->service->predictUsingStoredModel(
            tahunAjaran: $tahunAjaran,
            semester: $semester,
        );

        if ($prediction['success'] ?? false) {
            return $prediction;
        }

        if (! ($prediction['needs_training'] ?? false)) {
            return $prediction;
        }

        return $this->service->run(
            tahunAjaran: $tahunAjaran,
            semester: $semester,
            trainingRatio: 0.7,
            randomSeed: 42,
        );
    }
}
