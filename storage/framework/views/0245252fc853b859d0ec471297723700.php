<?php echo $__env->make(
    'filament.partials.confusion-matrix-detail',
    [
        'record' => $record,
        'title' =>
            $record->metode
            ?? 'Confusion Matrix',
    ]
, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /Users/mac/Downloads/sistem-monitoring-bk-FIXED/resources/views/filament/resources/evaluasi-models/confusion-matrix.blade.php ENDPATH**/ ?>