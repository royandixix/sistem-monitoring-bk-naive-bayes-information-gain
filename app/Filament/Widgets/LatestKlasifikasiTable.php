<?php

namespace App\Filament\Widgets;

use App\Models\Klasifikasi;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestKlasifikasiTable extends TableWidget
{
    protected static ?string $heading =
        'Hasil Klasifikasi Siswa Terbaru';

    protected static ?string $pollingInterval =
        '10s';

    protected int|string|array $columnSpan =
        'full';

    public static function canView(): bool
    {
        return auth()
            ->user()
            ?->hasAnyRole([
                'super_admin',
                'admin',
                'kepala_sekolah',
                'wali_murid',
            ]) ?? false;
    }

    public function table(
        Table $table
    ): Table {
        $query = Klasifikasi::query()
            ->with([
                'siswa.kelas',
            ])
            ->latest(
                'updated_at'
            );

        $user = auth()->user();

        /*
         * Wali Murid hanya boleh melihat hasil klasifikasi
         * anak yang terhubung dengan akunnya.
         */
        if ($user?->isWaliMurid()) {
            $query->whereHas(
                'siswa.waliMurids',
                fn (Builder $waliQuery): Builder =>
                    $waliQuery->where(
                        'users.id',
                        $user->id
                    )
            );
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('-'),

                TextColumn::make(
                    'siswa.kelas.nama_kelas'
                )
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make(
                    'tahun_ajaran'
                )
                    ->label('Tahun Ajaran')
                    ->placeholder('-'),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make(
                    'jumlah_pelanggaran'
                )
                    ->label('Pelanggaran')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make(
                    'total_poin'
                )
                    ->label('Total Poin')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make(
                    'hasil_ig_naive_bayes'
                )
                    ->label('Hasil NB + IG')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state
                        ): string => match ($state) {
                            'Baik' =>
                                'success',

                            'Butuh Perhatian' =>
                                'warning',

                            'Bermasalah' =>
                                'danger',

                            default =>
                                'gray',
                        }
                    )
                    ->placeholder('-'),

                TextColumn::make(
                    'probabilitas_ig_naive_bayes'
                )
                    ->label('Probabilitas')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state !== null
                            ? number_format(
                                (float) $state * 100,
                                2
                            ).'%'
                            : '-'
                    )
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make(
                    'updated_at'
                )
                    ->label('Diperbarui')
                    ->dateTime(
                        'd M Y H:i'
                    )
                    ->sortable(),
            ])
            ->striped()
            ->defaultPaginationPageOption(5);
    }
}
