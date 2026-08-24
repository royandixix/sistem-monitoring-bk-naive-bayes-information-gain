@include(
    'filament.partials.confusion-matrix-detail',
    [
        'record' => $record,
        'title' =>
            $record->metode
            ?? 'Confusion Matrix',
    ]
)
