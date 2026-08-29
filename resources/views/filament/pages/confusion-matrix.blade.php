<x-filament-panels::page>

    @php
        $labels = $evaluation['labels'];

        $models = [
            'baseline' => 'Naïve Bayes Baseline',
            'information_gain' => 'Naïve Bayes Setelah Seleksi Information Gain',
        ];

        $formatPercent = function ($value) {
            if ($value === null) {
                return '-';
            }

            $value = (float) $value;

            if ($value <= 1) {
                $value *= 100;
            }

            return number_format($value, 2) . '%';
        };
    @endphp

    <div class="space-y-8">

        @foreach ($models as $key => $title)

            @php
                $data = $evaluation[$key];
            @endphp

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">

                <div class="mb-6">
                    <h2 class="text-xl font-bold">
                        {{ $title }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Metode:
                        {{ $data['metode'] }}
                    </p>
                </div>

                @if (! $data['available'])

                    <div class="rounded-lg bg-warning-50 p-4 text-warning-700 dark:bg-warning-500/10">
                        Belum terdapat hasil evaluasi untuk model ini.
                        Jalankan proses klasifikasi terlebih dahulu.
                    </div>

                @else

                    <div class="mb-6 grid gap-4 md:grid-cols-3 lg:grid-cols-6">

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Data Training
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $data['training'] }}
                            </div>
                        </div>

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Data Testing
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $data['testing'] }}
                            </div>
                        </div>

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Accuracy
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $formatPercent($data['accuracy']) }}
                            </div>
                        </div>

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Macro Precision
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $formatPercent($data['precision']) }}
                            </div>
                        </div>

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Macro Recall
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $formatPercent($data['recall']) }}
                            </div>
                        </div>

                        <div class="rounded-lg border p-4">
                            <div class="text-sm text-gray-500">
                                Macro F1-Score
                            </div>
                            <div class="mt-1 text-xl font-bold">
                                {{ $formatPercent($data['f1_score']) }}
                            </div>
                        </div>

                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-center">
                            <thead>
                                <tr>
                                    <th class="border p-3">
                                        Aktual \ Prediksi
                                    </th>

                                    @foreach ($labels as $label)
                                        <th class="border p-3">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($labels as $actual)
                                    <tr>
                                        <th class="border p-3 text-left">
                                            {{ $actual }}
                                        </th>

                                        @foreach ($labels as $predicted)
                                            <td class="border p-4 text-lg font-semibold">
                                                {{ $data['matrix'][$actual][$predicted] ?? 0 }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif

            </div>

        @endforeach

    </div>

</x-filament-panels::page>
