<?php

namespace App\Filament\Resources\Pelanggarans\Tables;

use App\Filament\Resources\Pelanggarans\PelanggaranResource;
use App\Models\Pelanggaran;
use App\Services\KlasifikasiAutoRefreshService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PelanggaransTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'created_at',
                'desc'
            )
            ->striped()
            ->columns([
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(
                    'siswa.kelas.nama_kelas'
                )
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make(
                    'jenisPelanggaran.nama_jenis'
                )
                    ->label(
                        'Jenis Pelanggaran'
                    )
                    ->searchable()
                    ->wrap(),

                TextColumn::make(
                    'jenisPelanggaran.aspek_pelanggaran'
                )
                    ->label('Aspek')
                    ->badge()
                    ->color('info'),

                TextColumn::make(
                    'jenisPelanggaran.tingkat_pelanggaran'
                )
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state ?: 'Tidak Diketahui'
                    )
                    ->color(
                        fn (?string $state): string =>
                            match ($state) {
                                'Berat' => 'danger',
                                'Sedang' => 'warning',
                                'Ringan' => 'success',
                                default => 'gray',
                            }
                    ),

                TextColumn::make(
                    'jenisPelanggaran.poin'
                )
                    ->label('Poin')
                    ->badge()
                    ->color('danger')
                    ->suffix(' poin'),

                TextColumn::make('tanggal')
                    ->label(
                        'Tanggal Kejadian'
                    )
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make(
                    'tahun_ajaran'
                )
                    ->label(
                        'Tahun Ajaran'
                    )
                    ->badge(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge(),

                TextColumn::make(
                    'diajukanOleh.name'
                )
                    ->label('Diajukan Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make(
                    'status_pengajuan'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            ?string $state
                        ): string =>
                            match ($state) {
                                Pelanggaran::STATUS_DISETUJUI =>
                                    'Disetujui',
                                Pelanggaran::STATUS_DITOLAK =>
                                    'Ditolak',
                                default =>
                                    'Menunggu',
                            }
                    )
                    ->color(
                        fn (
                            ?string $state
                        ): string =>
                            match ($state) {
                                Pelanggaran::STATUS_DISETUJUI =>
                                    'success',
                                Pelanggaran::STATUS_DITOLAK =>
                                    'danger',
                                default =>
                                    'warning',
                            }
                    )
                    ->icon(
                        fn (
                            ?string $state
                        ): string =>
                            match ($state) {
                                Pelanggaran::STATUS_DISETUJUI =>
                                    'heroicon-o-check-circle',
                                Pelanggaran::STATUS_DITOLAK =>
                                    'heroicon-o-x-circle',
                                default =>
                                    'heroicon-o-clock',
                            }
                    ),

                TextColumn::make(
                    'catatan_verifikasi'
                )
                    ->label(
                        'Catatan Guru BK'
                    )
                    ->placeholder('-')
                    ->limit(40)
                    ->tooltip(
                        fn (
                            Pelanggaran $record
                        ): ?string =>
                            $record
                                ->catatan_verifikasi
                    )
                    ->wrap(),

                TextColumn::make(
                    'diprosesOleh.name'
                )
                    ->label(
                        'Diproses Oleh'
                    )
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),

                TextColumn::make(
                    'diproses_pada'
                )
                    ->label(
                        'Diproses Pada'
                    )
                    ->dateTime(
                        'd M Y H:i'
                    )
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),
            ])
            ->filters([
                SelectFilter::make(
                    'status_pengajuan'
                )
                    ->label(
                        'Status Pengajuan'
                    )
                    ->options([
                        Pelanggaran::STATUS_MENUNGGU =>
                            'Menunggu',
                        Pelanggaran::STATUS_DISETUJUI =>
                            'Disetujui',
                        Pelanggaran::STATUS_DITOLAK =>
                            'Ditolak',
                    ]),

                SelectFilter::make('semester')
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                    ]),
            ])
            ->recordActions([
                /*
                 * Tombol persetujuan khusus Guru BK.
                 */
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon(
                        'heroicon-o-check-circle'
                    )
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Setujui Laporan Pelanggaran'
                    )
                    ->modalDescription(
                        'Laporan yang disetujui akan menjadi data resmi dan digunakan dalam proses klasifikasi.'
                    )
                    ->modalSubmitActionLabel(
                        'Ya, Setujui'
                    )
                    ->visible(
                        fn (
                            Pelanggaran $record
                        ): bool =>
                            (
                                auth()
                                    ->user()
                                    ?->isGuruBk()
                                ?? false
                            ) &&
                            $record->isMenunggu()
                    )
                    ->action(
                        function (
                            Pelanggaran $record
                        ): void {
                            $updated =
                                Pelanggaran::query()
                                    ->whereKey(
                                        $record
                                            ->getKey()
                                    )
                                    ->where(
                                        'status_pengajuan',
                                        Pelanggaran::STATUS_MENUNGGU
                                    )
                                    ->update([
                                        'status_pengajuan' =>
                                            Pelanggaran::STATUS_DISETUJUI,
                                        'diproses_oleh' =>
                                            auth()->id(),
                                        'diproses_pada' =>
                                            now(),
                                        'catatan_verifikasi' =>
                                            'Laporan disetujui oleh Guru BK',
                                    ]);

                            if ($updated === 0) {
                                Notification::make()
                                    ->title(
                                        'Laporan sudah diproses'
                                    )
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $record->loadMissing([
                                'siswa',
                                'jenisPelanggaran',
                            ]);

                            $poin = (int) (
                                $record
                                    ->jenisPelanggaran
                                    ?->poin ?? 0
                            );

                            $tingkat = (string) (
                                $record
                                    ->jenisPelanggaran
                                    ?->tingkat_pelanggaran ?? ''
                            );

                            $isPelanggaranBerat =
                                strcasecmp(
                                    $tingkat,
                                    'Berat'
                                ) === 0
                                || $poin > 15;

                            if ($isPelanggaranBerat) {
                                Notification::make()
                                    ->title(
                                        'Pelanggaran berat terdeteksi'
                                    )
                                    ->body(
                                        ($record->siswa?->nama ?? 'Siswa') .
                                        ' memiliki pelanggaran berat: ' .
                                        ($record->jenisPelanggaran?->nama_jenis ?? 'Pelanggaran') .
                                        ' (' . $poin . ' poin). Segera lakukan tindak lanjut BK.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }

                            $refresh = app(
                                KlasifikasiAutoRefreshService::class
                            )->refresh(
                                $record->tahun_ajaran,
                                $record->semester
                            );

                            if (! ($refresh['success'] ?? false)) {
                                Notification::make()
                                    ->title(
                                        'Laporan disetujui, klasifikasi belum diperbarui'
                                    )
                                    ->body(
                                        (string) (
                                            $refresh['message']
                                            ?? 'Data resmi tersimpan, tetapi proses klasifikasi perlu dijalankan setelah data label aktual mencukupi.'
                                        )
                                    )
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title(
                                    'Laporan berhasil disetujui'
                                )
                                ->body(
                                    'Data resmi tersimpan dan hasil klasifikasi periode ini telah diperbarui.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                /*
                 * Tombol penolakan khusus Guru BK.
                 */
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon(
                        'heroicon-o-x-circle'
                    )
                    ->color('danger')
                    ->modalHeading(
                        'Tolak Laporan Pelanggaran'
                    )
                    ->modalDescription(
                        'Masukkan alasan agar OSIS dapat memperbaiki laporan.'
                    )
                    ->modalSubmitActionLabel(
                        'Tolak Laporan'
                    )
                    ->schema([
                        Textarea::make(
                            'catatan_verifikasi'
                        )
                            ->label(
                                'Alasan Penolakan'
                            )
                            ->placeholder(
                                'Contoh: Kronologi belum lengkap atau siswa yang dipilih tidak sesuai.'
                            )
                            ->required()
                            ->rows(4)
                            ->maxLength(1000),
                    ])
                    ->visible(
                        fn (
                            Pelanggaran $record
                        ): bool =>
                            (
                                auth()
                                    ->user()
                                    ?->isGuruBk()
                                ?? false
                            ) &&
                            $record->isMenunggu()
                    )
                    ->action(
                        function (
                            Pelanggaran $record,
                            array $data
                        ): void {
                            $updated =
                                Pelanggaran::query()
                                    ->whereKey(
                                        $record
                                            ->getKey()
                                    )
                                    ->where(
                                        'status_pengajuan',
                                        Pelanggaran::STATUS_MENUNGGU
                                    )
                                    ->update([
                                        'status_pengajuan' =>
                                            Pelanggaran::STATUS_DITOLAK,
                                        'diproses_oleh' =>
                                            auth()->id(),
                                        'diproses_pada' =>
                                            now(),
                                        'catatan_verifikasi' =>
                                            $data[
                                                'catatan_verifikasi'
                                            ],
                                    ]);

                            if ($updated === 0) {
                                Notification::make()
                                    ->title(
                                        'Laporan sudah diproses'
                                    )
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title(
                                    'Laporan ditolak'
                                )
                                ->body(
                                    'OSIS dapat memperbaiki dan mengajukan laporan kembali.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                EditAction::make()
                    ->label('Edit')
                    ->visible(
                        fn (
                            Pelanggaran $record
                        ): bool =>
                            PelanggaranResource::canEdit(
                                $record
                            )
                    ),

                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn (
                            Pelanggaran $record
                        ): bool =>
                            PelanggaranResource::canDelete(
                                $record
                            )
                    )
                    ->requiresConfirmation()
                    ->after(
                        function (
                            Pelanggaran $record
                        ): void {
                            if (! $record->isDisetujui()) {
                                return;
                            }

                            $refresh = app(
                                KlasifikasiAutoRefreshService::class
                            )->refresh(
                                $record->tahun_ajaran,
                                $record->semester
                            );

                            if (! ($refresh['success'] ?? false)) {
                                Notification::make()
                                    ->title(
                                        'Data dihapus, klasifikasi perlu diperbarui'
                                    )
                                    ->body(
                                        (string) (
                                            $refresh['message']
                                            ?? 'Jalankan kembali proses klasifikasi untuk periode ini.'
                                        )
                                    )
                                    ->warning()
                                    ->send();
                            }
                        }
                    ),
            ])
            ->emptyStateHeading(
                'Belum ada laporan pelanggaran'
            )
            ->emptyStateDescription(
                'OSIS dapat mengajukan laporan pelanggaran siswa melalui tombol tambah.'
            )
            ->emptyStateIcon(
                'heroicon-o-document-text'
            );
    }
}