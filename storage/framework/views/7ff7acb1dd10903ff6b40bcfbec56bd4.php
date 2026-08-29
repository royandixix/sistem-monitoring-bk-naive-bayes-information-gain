<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


         <?php $__env->slot('heading', null, []); ?> 
            Confusion Matrix dan Evaluasi Model Klasifikasi
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('description', null, []); ?> 
            Perbandingan hasil pengujian Naïve Bayes baseline dan
            Naïve Bayes setelah seleksi fitur Information Gain
            menggunakan data testing yang sama.
         <?php $__env->endSlot(); ?>

        <div style="
            display:grid;
            gap:30px;
        ">

            <?php echo $__env->make(
                'filament.partials.confusion-matrix-detail',
                [
                    'record' => $baseline,
                    'title' => '1. Naïve Bayes Baseline',
                ]
            , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php echo $__env->make(
                'filament.partials.confusion-matrix-detail',
                [
                    'record' => $optimized,
                    'title' =>
                        '2. Naïve Bayes + Information Gain',
                ]
            , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
                $baseline
                && $optimized
            ): ?>

                <?php
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
                ?>

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
                            3. Perbandingan Model Utama
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
                                            <?php echo e(number_format(
                                                    $baselineAccuracy,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $baselinePrecision,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $baselineRecall,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $baselineF1,
                                                    2
                                                )); ?>%
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
                                            <?php echo e(number_format(
                                                    $optimizedAccuracy,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $optimizedPrecision,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $optimizedRecall,
                                                    2
                                                )); ?>%
                                        </td>

                                        <td style="
                                            padding:12px;
                                            border-top:1px solid rgba(156,163,175,.18);
                                        ">
                                            <?php echo e(number_format(
                                                    $optimizedF1,
                                                    2
                                                )); ?>%
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
                                <?php echo e(number_format(
                                        $baselineAccuracy,
                                        2
                                    )); ?>%
                            </strong>,

                            sedangkan model Naïve Bayes
                            + Information Gain memperoleh
                            Accuracy sebesar

                            <strong>
                                <?php echo e(number_format(
                                        $optimizedAccuracy,
                                        2
                                    )); ?>%
                            </strong>.

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
                                $accuracyDelta > 0
                            ): ?>

                                Penerapan seleksi fitur
                                Information Gain meningkatkan
                                Accuracy sebesar

                                <strong>
                                    <?php echo e(number_format(
                                            $accuracyDelta,
                                            2
                                        )); ?>

                                    poin persentase
                                </strong>

                                pada pengujian ini.

                            <?php elseif(
                                $accuracyDelta < 0
                            ): ?>

                                Accuracy setelah penerapan
                                seleksi fitur Information Gain
                                mengalami penurunan sebesar

                                <strong>
                                    <?php echo e(number_format(
                                            abs(
                                                $accuracyDelta
                                            ),
                                            2
                                        )); ?>

                                    poin persentase
                                </strong>.

                            <?php else: ?>

                                Kedua model menghasilkan
                                Accuracy yang sama. Dengan
                                demikian, pada pengujian ini
                                seleksi fitur Information Gain
                                belum menghasilkan perubahan
                                terhadap tingkat Accuracy
                                model.

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($experimental): ?>

                <div style="
                    padding:16px 18px;
                    border:1px solid rgba(59,130,246,.35);
                    border-radius:14px;
                    background:rgba(59,130,246,.08);
                    line-height:1.7;
                ">
                    <div style="
                        font-size:16px;
                        font-weight:900;
                    ">
                        4. Eksperimen Dataset CSV
                    </div>

                    <div style="
                        font-size:12px;
                        opacity:.75;
                        margin-top:4px;
                    ">
                        Hasil ini berasal dari pengujian
                        Naive Bayes + Information Gain
                        menggunakan fitur Dataset CSV
                        dengan 421 sampel, pembagian
                        70:30, dan random seed 42.
                    </div>
                </div>

                <?php echo $__env->make(
                    'filament.partials.confusion-matrix-detail',
                    [
                        'record' => $experimental,
                        'title' =>
                            'Naïve Bayes + Information Gain – Eksperimen Dataset CSV',
                    ]
                , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH /Users/mac/Downloads/sistem-monitoring-bk-FIXED/resources/views/filament/widgets/confusion-matrix-widget.blade.php ENDPATH**/ ?>