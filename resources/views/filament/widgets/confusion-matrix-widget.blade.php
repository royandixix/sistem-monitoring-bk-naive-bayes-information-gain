<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Confusion Matrix dan Evaluasi Model Klasifikasi
        </x-slot>

        <x-slot name="description">
            Perbandingan hasil pengujian Naïve Bayes baseline dan
            Naïve Bayes setelah seleksi fitur Information Gain
            menggunakan data testing yang sama.
        </x-slot>

        <div style="
            display:grid;
            gap:30px;
        ">

            @include(
                'filament.partials.confusion-matrix-detail',
                [
                    'record' => $baseline,
                    'title' => 'Naïve Bayes Baseline',
                ]
            )

            @include(
                'filament.partials.confusion-matrix-detail',
                [
                    'record' => $optimized,
                    'title' =>
                        'Naïve Bayes + Information Gain',
                ]
            )

            @if (
                $baseline
                && $optimized
            )

                @php
                    $baselineAccuracy =
                        (float) (
                            $baseline->akurasi
                            ?? 0
                        );

                    $optimizedAccuracy =
                        (float) (
                            $optimized->akurasi
                            ?? 0
                        );

                    $baselinePrecision =
                        (float) (
                            $baseline->precision
                            ?? 0
                        );

                    $optimizedPrecision =
                        (float) (
                            $optimized->precision
                            ?? 0
                        );

                    $baselineRecall =
                        (float) (
                            $baseline->recall
                            ?? 0
                        );

                    $optimizedRecall =
                        (float) (
                            $optimized->recall
                            ?? 0
                        );

                    $baselineF1 =
                        (float) (
                            $baseline->f1_score
                            ?? 0
                        );

                    $optimizedF1 =
                        (float) (
                            $optimized->f1_score
                            ?? 0
                        );

                    $accuracyDelta =
                        $optimizedAccuracy
                        - $baselineAccuracy;
                @endphp

                <div style="
                    border:1px solid rgba(156,163,175,.25);
                    border-radius:16px;
                    overflow:hidden;
                ">

                    <div style="
                        padding:17px 19px;
                        background:rgba(148,163,184,.08);
                        border-bottom:1px solid rgba(156,163,175,.22);
                    ">

                        <div style="
                            font-size:17px;
                            font-weight:900;
                        ">
                            Perbandingan Akhir Kedua Model
                        </div>

                        <div style="
                            font-size:12px;
                            opacity:.70;
                            margin-top:4px;
                        ">
                            Naïve Bayes baseline dan
                            Naïve Bayes + Information Gain
                            dibandingkan menggunakan data
                            testing yang sama.
                        </div>

                    </div>

                    <div style="
                        padding:18px;
                        display:grid;
                        gap:16px;
                    ">

                        <div style="
                            overflow-x:auto;
                            border:1px solid rgba(156,163,175,.20);
                            border-radius:12px;
                        ">

                            <table style="
                                width:100%;
                                border-collapse:collapse;
                                text-align:center;
                                min-width:720px;
                            ">

                                <thead>
                                    <tr>

                                        <th style="
                                            padding:12px;
                                            background:rgba(148,163,184,.12);
                                            text-align:left;
                                        ">
                                            Model
                                        </th>

                                        <th style="
                                            padding:12px;
                                            background:rgba(148,163,184,.12);
                                        ">
                                            Accuracy
                                        </th>

                                        <th style="
                                            padding:12px;
                                            background:rgba(148,163,184,.12);
                                        ">
                                            Macro Precision
                                        </th>

                                        <th style="
                                            padding:12px;
                                            background:rgba(148,163,184,.12);
                                        ">
                                            Macro Recall
                                        </th>

                                        <th style="
                                            padding:12px;
                                            background:rgba(148,163,184,.12);
                                        ">
                                            Macro F1-Score
                                        </th>

                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>

                                        <th style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                            text-align:left;
                                        ">
                                            Naïve Bayes Baseline
                                        </th>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                            font-weight:800;
                                        ">
                                            {{
                                                number_format(
                                                    $baselineAccuracy,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $baselinePrecision,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $baselineRecall,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $baselineF1,
                                                    2
                                                )
                                            }}%
                                        </td>

                                    </tr>

                                    <tr>

                                        <th style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                            text-align:left;
                                        ">
                                            Naïve Bayes +
                                            Information Gain
                                        </th>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                            font-weight:800;
                                        ">
                                            {{
                                                number_format(
                                                    $optimizedAccuracy,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $optimizedPrecision,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $optimizedRecall,
                                                    2
                                                )
                                            }}%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            {{
                                                number_format(
                                                    $optimizedF1,
                                                    2
                                                )
                                            }}%
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <div style="
                            padding:16px 18px;
                            border:1px solid rgba(59,130,246,.20);
                            border-radius:12px;
                            background:rgba(59,130,246,.05);
                            font-size:13px;
                            line-height:1.9;
                        ">

                            <strong>
                                Interpretasi Perbandingan:
                            </strong>

                            model Naïve Bayes baseline
                            memperoleh Accuracy sebesar

                            <strong>
                                {{
                                    number_format(
                                        $baselineAccuracy,
                                        2
                                    )
                                }}%
                            </strong>,

                            sedangkan model Naïve Bayes
                            + Information Gain memperoleh
                            Accuracy sebesar

                            <strong>
                                {{
                                    number_format(
                                        $optimizedAccuracy,
                                        2
                                    )
                                }}%
                            </strong>.

                            @if (
                                $accuracyDelta > 0
                            )

                                Penerapan seleksi fitur
                                Information Gain meningkatkan
                                Accuracy sebesar

                                <strong>
                                    {{
                                        number_format(
                                            $accuracyDelta,
                                            2
                                        )
                                    }}
                                    poin persentase
                                </strong>

                                pada pengujian ini.

                            @elseif (
                                $accuracyDelta < 0
                            )

                                Accuracy setelah penerapan
                                seleksi fitur Information Gain
                                mengalami penurunan sebesar

                                <strong>
                                    {{
                                        number_format(
                                            abs(
                                                $accuracyDelta
                                            ),
                                            2
                                        )
                                    }}
                                    poin persentase
                                </strong>.

                            @else

                                Kedua model menghasilkan
                                Accuracy yang sama. Dengan
                                demikian, pada pengujian ini
                                seleksi fitur Information Gain
                                belum menghasilkan perubahan
                                terhadap tingkat Accuracy
                                model.

                            @endif

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </x-filament::section>

</x-filament-widgets::widget>
