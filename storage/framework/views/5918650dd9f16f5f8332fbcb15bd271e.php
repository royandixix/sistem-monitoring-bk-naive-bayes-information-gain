<?php
    $rawMatrix = $record?->confusion_matrix;

    if (is_array($rawMatrix)) {
        $matrix = $rawMatrix;
    } elseif (is_string($rawMatrix)) {
        $decoded = json_decode($rawMatrix, true);
        $matrix = is_array($decoded) ? $decoded : [];
    } else {
        $matrix = [];
    }

    $preferredOrder = [
        'Baik',
        'Butuh Perhatian',
        'Bermasalah',
    ];

    $availableClasses = array_keys($matrix);

    $classes = array_values(array_filter(
        $preferredOrder,
        fn ($class) => in_array(
            $class,
            $availableClasses,
            true
        )
    ));

    foreach ($availableClasses as $class) {
        if (! in_array($class, $classes, true)) {
            $classes[] = $class;
        }
    }

    $rowTotals = [];
    $columnTotals = [];
    $total = 0;
    $correct = 0;
    $maxCell = 0;

    foreach ($classes as $actual) {
        $rowTotals[$actual] = 0;

        foreach ($classes as $predicted) {
            $value = (int) data_get(
                $matrix,
                $actual . '.' . $predicted,
                0
            );

            $rowTotals[$actual] += $value;

            $columnTotals[$predicted] =
                ($columnTotals[$predicted] ?? 0)
                + $value;

            $total += $value;

            $maxCell = max(
                $maxCell,
                $value
            );

            if ($actual === $predicted) {
                $correct += $value;
            }
        }
    }

    $incorrect = max(
        0,
        $total - $correct
    );

    $computedAccuracy =
        $total > 0
            ? ($correct / $total) * 100
            : 0;

    $perClass = [];

    foreach ($classes as $class) {
        $tp = (int) data_get(
            $matrix,
            $class . '.' . $class,
            0
        );

        $fp = max(
            0,
            (int) ($columnTotals[$class] ?? 0)
            - $tp
        );

        $fn = max(
            0,
            (int) ($rowTotals[$class] ?? 0)
            - $tp
        );

        $precisionClass =
            ($tp + $fp) > 0
                ? ($tp / ($tp + $fp)) * 100
                : 0;

        $recallClass =
            ($tp + $fn) > 0
                ? ($tp / ($tp + $fn)) * 100
                : 0;

        $f1Class =
            ($precisionClass + $recallClass) > 0
                ? (
                    2
                    * $precisionClass
                    * $recallClass
                ) / (
                    $precisionClass
                    + $recallClass
                )
                : 0;

        $misclassified = [];

        foreach ($classes as $predicted) {
            if ($predicted === $class) {
                continue;
            }

            $count = (int) data_get(
                $matrix,
                $class . '.' . $predicted,
                0
            );

            if ($count > 0) {
                $misclassified[] =
                    $count
                    . ' data diprediksi sebagai '
                    . $predicted;
            }
        }

        $perClass[$class] = [
            'tp' => $tp,
            'fp' => $fp,
            'fn' => $fn,

            'precision' =>
                $precisionClass,

            'recall' =>
                $recallClass,

            'f1' =>
                $f1Class,

            'support' =>
                (int) ($rowTotals[$class] ?? 0),

            'wrong' =>
                max(
                    0,
                    (int) ($rowTotals[$class] ?? 0)
                    - $tp
                ),

            'misclassified' =>
                $misclassified,
        ];
    }

    $selectedFeatures =
        is_array($record?->selected_features)
            ? $record->selected_features
            : [];

    $methodName = (string) (
        $record?->metode
        ?? $title
        ?? 'Model'
    );

    $isOptimized =
        str_contains(
            strtolower($methodName),
            'information gain'
        );

    $igResults = collect();

    if ($record && $isOptimized) {
        $igResults =
            \App\Models\InformationGainResult::query()
                ->where(
                    'tahun_ajaran',
                    $record->tahun_ajaran
                )
                ->where(
                    'semester',
                    $record->semester
                )
                ->orderBy('ranking')
                ->get();
    }

    $allGainZero =
        $igResults->isNotEmpty()
        && $igResults->every(
            fn ($item) =>
                (float) $item->gain <= 0
        );
?>

<div style="
    display:grid;
    gap:24px;
">

    <div style="
        padding:16px 18px;
        border:1px solid rgba(59,130,246,.20);
        border-radius:14px;
        background:rgba(59,130,246,.05);
        font-size:13px;
        line-height:1.8;
    ">
        <div style="
            font-size:16px;
            font-weight:800;
            margin-bottom:6px;
        ">
            <?php echo e($title ?? $methodName); ?>

        </div>

        Confusion Matrix digunakan untuk membandingkan
        <strong>kelas aktual</strong> siswa dengan
        <strong>kelas hasil prediksi</strong> model pada
        data testing. Baris pada matriks menunjukkan kelas
        aktual, sedangkan kolom menunjukkan kelas hasil
        prediksi. Penelitian ini menggunakan tiga kelas
        perilaku, yaitu <strong>Baik</strong>,
        <strong>Butuh Perhatian</strong>, dan
        <strong>Bermasalah</strong>.
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record): ?>

        <div style="
            display:grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(145px,1fr)
                );
            gap:10px;
        ">

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Tahun Ajaran
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php echo e($record->tahun_ajaran ?: '-'); ?>

                </div>
            </div>

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Semester
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php echo e($record->semester ?: '-'); ?>

                </div>
            </div>

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Data Training
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php echo e((int) (
                            $record
                                ->jumlah_data_training
                            ?? 0
                        )); ?>

                </div>
            </div>

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Data Testing
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php echo e($total
                        ?: (int) (
                            $record
                                ->jumlah_data_testing
                            ?? 0
                        )); ?>

                </div>
            </div>

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Hold-out Validation
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php
                        $ratio = (float) ($record->training_ratio ?? 0.70);
                        $ratio = $ratio <= 1 ? $ratio * 100 : $ratio;
                    ?>
                    <?php echo e(number_format($ratio, 0)); ?> :
                    <?php echo e(number_format(100 - $ratio, 0)); ?>

                </div>
            </div>

            <div style="
                padding:13px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.65;
                ">
                    Random Seed
                </div>

                <div style="
                    font-size:17px;
                    font-weight:800;
                    margin-top:3px;
                ">
                    <?php echo e($record->random_seed
                        ?? '-'); ?>

                </div>
            </div>

        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedFeatures) > 0): ?>

            <div style="
                padding:14px 16px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
                background:rgba(148,163,184,.05);
                font-size:13px;
                line-height:1.8;
            ">
                <strong>
                    Fitur yang digunakan model:
                </strong>

                <?php echo e(implode(
                        ', ',
                        $selectedFeatures
                    )); ?>.
            </div>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
        $record
        && count($classes) > 0
    ): ?>

        <div style="
            display:grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(150px,1fr)
                );
            gap:10px;
        ">

            <div style="
                padding:14px;
                border:1px solid rgba(34,197,94,.25);
                border-radius:12px;
                background:rgba(34,197,94,.07);
            ">
                <div style="
                    font-size:11px;
                    opacity:.68;
                ">
                    Prediksi Benar
                </div>

                <div style="
                    font-size:24px;
                    font-weight:900;
                ">
                    <?php echo e($correct); ?>

                </div>
            </div>

            <div style="
                padding:14px;
                border:1px solid rgba(239,68,68,.22);
                border-radius:12px;
                background:rgba(239,68,68,.06);
            ">
                <div style="
                    font-size:11px;
                    opacity:.68;
                ">
                    Prediksi Salah
                </div>

                <div style="
                    font-size:24px;
                    font-weight:900;
                ">
                    <?php echo e($incorrect); ?>

                </div>
            </div>

            <div style="
                padding:14px;
                border:1px solid rgba(59,130,246,.22);
                border-radius:12px;
                background:rgba(59,130,246,.06);
            ">
                <div style="
                    font-size:11px;
                    opacity:.68;
                ">
                    Accuracy
                </div>

                <div style="
                    font-size:24px;
                    font-weight:900;
                ">
                    <?php echo e(number_format(
                            (float) (
                                $record->akurasi
                                ?? 0
                            ),
                            2
                        )); ?>%
                </div>
            </div>

            <div style="
                padding:14px;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">
                <div style="
                    font-size:11px;
                    opacity:.68;
                ">
                    Jumlah Kelas
                </div>

                <div style="
                    font-size:24px;
                    font-weight:900;
                ">
                    <?php echo e(count($classes)); ?>

                </div>
            </div>

        </div>

        <div>

            <div style="
                font-size:15px;
                font-weight:800;
                margin-bottom:10px;
            ">
                Tabel Confusion Matrix 3×3
            </div>

            <div style="
                overflow-x:auto;
                border:1px solid rgba(156,163,175,.22);
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
                                Aktual \ Prediksi
                            </th>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $predicted): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                                <th style="
                                    padding:12px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    <?php echo e($predicted); ?>

                                </th>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <th style="
                                padding:12px;
                                background:rgba(148,163,184,.12);
                            ">
                                Total Aktual
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actual): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                            <tr>

                                <th style="
                                    padding:12px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    background:rgba(148,163,184,.06);
                                    text-align:left;
                                ">
                                    <?php echo e($actual); ?>

                                </th>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $predicted): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                                    <?php
                                        $value =
                                            (int)
                                            data_get(
                                                $matrix,
                                                $actual
                                                    . '.'
                                                    . $predicted,
                                                0
                                            );

                                        $isCorrect =
                                            $actual
                                            === $predicted;
                                    ?>

                                    <td style="
                                        padding:16px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                        font-size:18px;
                                        font-weight:900;

                                        <?php echo e($isCorrect
                                                ? 'background:rgba(34,197,94,.14);color:rgb(22,163,74);'
                                                : (
                                                    $value > 0
                                                        ? 'background:rgba(239,68,68,.10);color:rgb(220,38,38);'
                                                        : ''
                                                )); ?>

                                    ">
                                        <?php echo e($value); ?>

                                    </td>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                                <td style="
                                    padding:12px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    background:rgba(148,163,184,.06);
                                    font-weight:800;
                                ">
                                    <?php echo e($rowTotals[
                                            $actual
                                        ] ?? 0); ?>

                                </td>

                            </tr>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        <tr>

                            <th style="
                                padding:12px;
                                border-top:1px solid rgba(156,163,175,.22);
                                background:rgba(148,163,184,.12);
                                text-align:left;
                            ">
                                Total Prediksi
                            </th>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $predicted): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                                <td style="
                                    padding:12px;
                                    border-top:1px solid rgba(156,163,175,.22);
                                    background:rgba(148,163,184,.12);
                                    font-weight:800;
                                ">
                                    <?php echo e($columnTotals[
                                            $predicted
                                        ] ?? 0); ?>

                                </td>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <td style="
                                padding:12px;
                                border-top:1px solid rgba(156,163,175,.22);
                                background:rgba(148,163,184,.15);
                                font-weight:900;
                            ">
                                <?php echo e($total); ?>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div style="
                margin-top:8px;
                font-size:12px;
                line-height:1.7;
                opacity:.72;
            ">
                <strong>Keterangan:</strong>
                nilai pada diagonal utama menunjukkan
                data yang berhasil diklasifikasikan
                dengan benar. Nilai di luar diagonal
                menunjukkan kesalahan klasifikasi ke
                kelas lainnya.
            </div>

        </div>

        <div>

            <div style="
                font-size:15px;
                font-weight:800;
                margin-bottom:10px;
            ">
                Heatmap Confusion Matrix
            </div>

            <div style="overflow-x:auto;">

                <div style="
                    min-width:620px;
                    max-width:760px;
                    margin:0 auto;
                ">

                    <div style="
                        text-align:center;
                        font-size:12px;
                        font-weight:800;
                        margin-bottom:8px;
                    ">
                        Kelas Prediksi →
                    </div>

                    <div style="
                        display:grid;
                        grid-template-columns:
                            145px repeat(
                                <?php echo e(count($classes)); ?>,
                                1fr
                            );
                        gap:6px;
                    ">

                        <div></div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $predicted): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                            <div style="
                                padding:8px 5px;
                                text-align:center;
                                font-size:11px;
                                font-weight:800;
                            ">
                                <?php echo e($predicted); ?>

                            </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actual): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                            <div style="
                                padding:8px 6px;
                                display:flex;
                                align-items:center;
                                font-size:11px;
                                font-weight:800;
                            ">
                                <?php echo e($actual); ?>

                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $predicted): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                                <?php
                                    $value =
                                        (int)
                                        data_get(
                                            $matrix,
                                            $actual
                                                . '.'
                                                . $predicted,
                                            0
                                        );

                                    $strength =
                                        $maxCell > 0
                                            ? (
                                                $value
                                                / $maxCell
                                            )
                                            : 0;

                                    $alpha =
                                        0.08
                                        + (
                                            $strength
                                            * 0.72
                                        );

                                    $textColor =
                                        $strength >= 0.55
                                            ? '#ffffff'
                                            : 'inherit';
                                ?>

                                <div
                                    title="Aktual <?php echo e($actual); ?> → Prediksi <?php echo e($predicted); ?>: <?php echo e($value); ?>"
                                    style="
                                        min-height:86px;
                                        border-radius:10px;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        flex-direction:column;
                                        border:1px solid rgba(37,99,235,.16);
                                        background:rgba(
                                            37,
                                            99,
                                            235,
                                            <?php echo e(number_format(
                                                    $alpha,
                                                    3,
                                                    '.',
                                                    ''
                                                )); ?>

                                        );
                                        color:<?php echo e($textColor); ?>;
                                    "
                                >

                                    <div style="
                                        font-size:26px;
                                        font-weight:900;
                                    ">
                                        <?php echo e($value); ?>

                                    </div>

                                    <div style="
                                        font-size:9px;
                                        margin-top:5px;
                                        opacity:.88;
                                    ">
                                        <?php echo e($actual
                                                === $predicted
                                                    ? 'Benar'
                                                    : 'Salah'); ?>

                                    </div>

                                </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    </div>

                    <div style="
                        font-size:11px;
                        opacity:.70;
                        margin-top:8px;
                    ">
                        Warna yang semakin pekat
                        menunjukkan jumlah data yang
                        semakin besar pada sel tersebut.
                    </div>

                </div>

            </div>

        </div>

        <div>

            <div style="
                font-size:15px;
                font-weight:800;
                margin-bottom:10px;
            ">
                Interpretasi Hasil Setiap Kelas
            </div>

            <div style="
                display:grid;
                gap:10px;
            ">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                    <?php
                        $m =
                            $perClass[
                                $class
                            ];
                    ?>

                    <div style="
                        padding:14px 16px;
                        border:1px solid rgba(156,163,175,.20);
                        border-radius:12px;
                        font-size:13px;
                        line-height:1.8;
                    ">

                        <strong>
                            Kelas <?php echo e($class); ?>

                        </strong>

                        memiliki

                        <strong>
                            <?php echo e($m['support']); ?>

                        </strong>

                        data aktual. Model berhasil
                        mengklasifikasikan

                        <strong>
                            <?php echo e($m['tp']); ?>

                        </strong>

                        data dengan benar sebagai
                        <?php echo e($class); ?>, sedangkan

                        <strong>
                            <?php echo e($m['wrong']); ?>

                        </strong>

                        data mengalami kesalahan
                        klasifikasi.

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
                            count(
                                $m[
                                    'misclassified'
                                ]
                            ) > 0
                        ): ?>

                            Rincian kesalahan:

                            <strong>
                                <?php echo e(implode(
                                        ' dan ',
                                        $m[
                                            'misclassified'
                                        ]
                                    )); ?>

                            </strong>.

                        <?php else: ?>

                            Tidak terdapat data kelas
                            <?php echo e($class); ?> yang salah
                            diprediksi ke kelas lain.

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        Nilai

                        <strong>
                            Recall kelas <?php echo e($class); ?>

                        </strong>

                        sebesar

                        <strong>
                            <?php echo e(number_format(
                                    $m[
                                        'recall'
                                    ],
                                    2
                                )); ?>%
                        </strong>.

                    </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            </div>

        </div>

        <div>

            <div style="
                font-size:15px;
                font-weight:800;
                margin-bottom:10px;
            ">
                Metrik Evaluasi per Kelas
            </div>

            <div style="
                overflow-x:auto;
                border:1px solid rgba(156,163,175,.22);
                border-radius:12px;
            ">

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    text-align:center;
                    min-width:850px;
                ">

                    <thead>
                        <tr>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                                text-align:left;
                            ">
                                Kelas
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                TP
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                FP
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                FN
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                Precision
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                Recall
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                F1-Score
                            </th>

                            <th style="
                                padding:11px;
                                background:rgba(148,163,184,.12);
                            ">
                                Support
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                            <?php
                                $m =
                                    $perClass[
                                        $class
                                    ];
                            ?>

                            <tr>

                                <th style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    background:rgba(148,163,184,.05);
                                    text-align:left;
                                ">
                                    <?php echo e($class); ?>

                                </th>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                ">
                                    <?php echo e($m['tp']); ?>

                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                ">
                                    <?php echo e($m['fp']); ?>

                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                ">
                                    <?php echo e($m['fn']); ?>

                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    font-weight:700;
                                ">
                                    <?php echo e(number_format(
                                            $m[
                                                'precision'
                                            ],
                                            2
                                        )); ?>%
                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    font-weight:700;
                                ">
                                    <?php echo e(number_format(
                                            $m[
                                                'recall'
                                            ],
                                            2
                                        )); ?>%
                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                    font-weight:700;
                                ">
                                    <?php echo e(number_format(
                                            $m['f1'],
                                            2
                                        )); ?>%
                                </td>

                                <td style="
                                    padding:11px;
                                    border-top:1px solid rgba(156,163,175,.18);
                                ">
                                    <?php echo e($m['support']); ?>

                                </td>

                            </tr>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div style="
                margin-top:8px;
                font-size:12px;
                line-height:1.7;
                opacity:.72;
            ">
                <strong>TP</strong> merupakan data pada
                kelas tersebut yang diprediksi dengan
                benar. <strong>FP</strong> merupakan data
                dari kelas lain yang salah diprediksi
                sebagai kelas tersebut, sedangkan
                <strong>FN</strong> merupakan data pada
                kelas tersebut yang salah diprediksi
                sebagai kelas lain.
            </div>

        </div>

        <div>

            <div style="
                font-size:15px;
                font-weight:800;
                margin-bottom:10px;
            ">
                Metrik Evaluasi Keseluruhan
            </div>

            <div style="
                display:grid;
                grid-template-columns:
                    repeat(
                        auto-fit,
                        minmax(150px,1fr)
                    );
                gap:10px;
            ">

                <div style="
                    padding:14px;
                    border-radius:12px;
                    background:rgba(59,130,246,.06);
                    border:1px solid rgba(59,130,246,.18);
                ">
                    <div style="
                        font-size:11px;
                        opacity:.65;
                    ">
                        Accuracy
                    </div>

                    <div style="
                        font-size:21px;
                        font-weight:900;
                    ">
                        <?php echo e(number_format(
                                (float) (
                                    $record
                                        ->akurasi
                                    ?? 0
                                ),
                                2
                            )); ?>%
                    </div>
                </div>

                <div style="
                    padding:14px;
                    border-radius:12px;
                    background:rgba(148,163,184,.07);
                ">
                    <div style="
                        font-size:11px;
                        opacity:.65;
                    ">
                        Macro Precision
                    </div>

                    <div style="
                        font-size:21px;
                        font-weight:900;
                    ">
                        <?php echo e(number_format(
                                (float) (
                                    $record
                                        ->precision
                                    ?? 0
                                ),
                                2
                            )); ?>%
                    </div>
                </div>

                <div style="
                    padding:14px;
                    border-radius:12px;
                    background:rgba(148,163,184,.07);
                ">
                    <div style="
                        font-size:11px;
                        opacity:.65;
                    ">
                        Macro Recall
                    </div>

                    <div style="
                        font-size:21px;
                        font-weight:900;
                    ">
                        <?php echo e(number_format(
                                (float) (
                                    $record
                                        ->recall
                                    ?? 0
                                ),
                                2
                            )); ?>%
                    </div>
                </div>

                <div style="
                    padding:14px;
                    border-radius:12px;
                    background:rgba(148,163,184,.07);
                ">
                    <div style="
                        font-size:11px;
                        opacity:.65;
                    ">
                        Macro F1-Score
                    </div>

                    <div style="
                        font-size:21px;
                        font-weight:900;
                    ">
                        <?php echo e(number_format(
                                (float) (
                                    $record
                                        ->f1_score
                                    ?? 0
                                ),
                                2
                            )); ?>%
                    </div>
                </div>

            </div>

        </div>

        <div style="
            padding:16px 18px;
            border:1px solid rgba(59,130,246,.20);
            border-radius:12px;
            background:rgba(59,130,246,.05);
            font-size:13px;
            line-height:1.9;
        ">

            <div style="
                font-size:14px;
                font-weight:800;
                margin-bottom:6px;
            ">
                Perhitungan Accuracy
            </div>

            <div>
                Accuracy =
                (Jumlah Prediksi Benar /
                Jumlah Data Testing) × 100%
            </div>

            <div>
                Accuracy =
                (
                <?php echo e($correct); ?>

                /
                <?php echo e($total); ?>

                ) × 100%
            </div>

            <div style="
                font-size:17px;
                font-weight:900;
                margin-top:5px;
            ">
                Accuracy =
                <?php echo e(number_format(
                        $computedAccuracy,
                        2
                    )); ?>%
            </div>

        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
            $isOptimized
            && $igResults->isNotEmpty()
        ): ?>

            <div>

                <div style="
                    font-size:15px;
                    font-weight:800;
                    margin-bottom:10px;
                ">
                    Hasil Seleksi Fitur Information Gain
                </div>

                <div style="
                    overflow-x:auto;
                    border:1px solid rgba(156,163,175,.22);
                    border-radius:12px;
                ">

                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        text-align:center;
                        min-width:800px;
                    ">

                        <thead>
                            <tr>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    Ranking
                                </th>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                    text-align:left;
                                ">
                                    Fitur
                                </th>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    Entropy Awal
                                </th>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    Entropy Setelah Split
                                </th>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    Information Gain
                                </th>

                                <th style="
                                    padding:11px;
                                    background:rgba(148,163,184,.12);
                                ">
                                    Status
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $igResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>

                                <tr>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                        font-weight:800;
                                    ">
                                        <?php echo e($ig->ranking); ?>

                                    </td>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                        text-align:left;
                                        font-weight:800;
                                    ">
                                        <?php echo e($ig->fitur); ?>

                                    </td>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                    ">
                                        <?php echo e(number_format(
                                                (float)
                                                $ig
                                                    ->entropy_before,
                                                10
                                            )); ?>

                                    </td>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                    ">
                                        <?php echo e(number_format(
                                                (float)
                                                $ig
                                                    ->entropy_after,
                                                10
                                            )); ?>

                                    </td>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                        font-weight:800;
                                    ">
                                        <?php echo e(number_format(
                                                (float)
                                                $ig->gain,
                                                10
                                            )); ?>

                                    </td>

                                    <td style="
                                        padding:11px;
                                        border-top:1px solid rgba(156,163,175,.18);
                                        font-weight:800;
                                    ">
                                        <?php echo e($ig->selected
                                                ? 'Terpilih'
                                                : 'Tidak Terpilih'); ?>

                                    </td>

                                </tr>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div style="
                    padding:14px 16px;
                    border:1px solid rgba(156,163,175,.20);
                    border-radius:12px;
                    background:rgba(148,163,184,.05);
                    font-size:13px;
                    line-height:1.8;
                    margin-top:10px;
                ">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allGainZero): ?>

                        Seluruh fitur memperoleh nilai
                        Information Gain sebesar 0 pada
                        periode pengujian ini. Sesuai
                        mekanisme sistem, seluruh fitur
                        dipertahankan agar proses
                        klasifikasi Naïve Bayes tetap
                        dapat dijalankan.

                    <?php else: ?>

                        Information Gain digunakan sebagai
                        <strong>metode seleksi fitur</strong>.
                        Fitur dengan status
                        <strong>Terpilih</strong> digunakan
                        dalam pelatihan kembali Naïve Bayes.
                        Nilai Information Gain tidak
                        digunakan sebagai pengali atau
                        pembobot probabilitas pada rumus
                        Naïve Bayes.

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </div>

            </div>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div style="
            padding:16px 18px;
            border:1px solid rgba(156,163,175,.22);
            border-radius:12px;
            background:rgba(148,163,184,.05);
            font-size:13px;
            line-height:1.85;
        ">

            <strong>
                Kesimpulan Model:
            </strong>

            dari total

            <strong>
                <?php echo e($total); ?>

            </strong>

            data testing, model

            <strong>
                <?php echo e($methodName); ?>

            </strong>

            menghasilkan

            <strong>
                <?php echo e($correct); ?>

            </strong>

            prediksi benar dan

            <strong>
                <?php echo e($incorrect); ?>

            </strong>

            prediksi salah. Model memperoleh
            Accuracy sebesar

            <strong>
                <?php echo e(number_format(
                        (float) (
                            $record->akurasi
                            ?? 0
                        ),
                        2
                    )); ?>%
            </strong>,

            Macro Precision

            <strong>
                <?php echo e(number_format(
                        (float) (
                            $record->precision
                            ?? 0
                        ),
                        2
                    )); ?>%
            </strong>,

            Macro Recall

            <strong>
                <?php echo e(number_format(
                        (float) (
                            $record->recall
                            ?? 0
                        ),
                        2
                    )); ?>%
            </strong>,

            dan Macro F1-Score

            <strong>
                <?php echo e(number_format(
                        (float) (
                            $record->f1_score
                            ?? 0
                        ),
                        2
                    )); ?>%
            </strong>.

        </div>

    <?php else: ?>

        <div style="
            padding:30px 18px;
            border:1px dashed rgba(156,163,175,.35);
            border-radius:12px;
            text-align:center;
            opacity:.68;
        ">
            Confusion Matrix belum tersedia.
            Jalankan kembali proses klasifikasi
            Naïve Bayes + Information Gain agar
            hasil pengujian tersimpan.
        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH /Users/mac/Downloads/sistem-monitoring-bk-FIXED/resources/views/filament/partials/confusion-matrix-detail.blade.php ENDPATH**/ ?>