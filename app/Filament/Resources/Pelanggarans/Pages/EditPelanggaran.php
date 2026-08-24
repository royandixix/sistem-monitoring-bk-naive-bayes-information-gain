<?php

namespace App\Filament\Resources\Pelanggarans\Pages;

use App\Filament\Resources\Pelanggarans\PelanggaranResource;
use App\Models\Pelanggaran;
use App\Services\KlasifikasiAutoRefreshService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPelanggaran extends EditRecord
{
    protected static string $resource =
        PelanggaranResource::class;

    protected ?string $originalTahunAjaran = null;

    protected ?string $originalSemester = null;

    protected bool $wasApprovedBeforeSave = false;

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        $this->originalTahunAjaran =
            $record->tahun_ajaran;
        $this->originalSemester =
            $record->semester;
        $this->wasApprovedBeforeSave =
            $record->isDisetujui();
    }

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {
        $user = auth()->user();

        /*
         * Saat OSIS memperbaiki laporan,
         * status dikembalikan menjadi menunggu
         * agar Guru BK memvalidasi ulang.
         */
        if ($user?->isOsis()) {
            $data['status_pengajuan'] =
                Pelanggaran::STATUS_MENUNGGU;

            $data['diproses_oleh'] = null;
            $data['diproses_pada'] = null;
            $data['catatan_verifikasi'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = auth()->user();
        $record = $this->getRecord()->refresh();

        if (! $user?->isGuruBk()) {
            return;
        }

        /*
         * Perubahan pada pelanggaran resmi harus langsung
         * menghitung ulang klasifikasi periode terkait.
         */
        if ($record->isDisetujui()) {
            $this->refreshClassification(
                $record->tahun_ajaran,
                $record->semester
            );
        }

        /*
         * Jika periode pelanggaran resmi dipindahkan, periode lama
         * juga harus dihitung ulang agar hasilnya tidak menyisakan
         * akumulasi poin lama.
         */
        if (
            $this->wasApprovedBeforeSave
            && (
                $this->originalTahunAjaran !== $record->tahun_ajaran
                || $this->originalSemester !== $record->semester
            )
        ) {
            $this->refreshClassification(
                $this->originalTahunAjaran,
                $this->originalSemester
            );
        }
    }

    private function refreshClassification(
        ?string $tahunAjaran,
        ?string $semester
    ): void {
        $refresh = app(
            KlasifikasiAutoRefreshService::class
        )->refresh(
            $tahunAjaran,
            $semester
        );

        if ($refresh['success'] ?? false) {
            return;
        }

        Notification::make()
            ->title(
                'Data tersimpan, klasifikasi belum diperbarui'
            )
            ->body(
                (string) (
                    $refresh['message']
                    ?? 'Jalankan kembali proses klasifikasi setelah data label aktual mencukupi.'
                )
            )
            ->warning()
            ->send();
    }

    protected function getSavedNotificationTitle():
        ?string {
        if (
            auth()
                ->user()
                ?->isOsis()
        ) {
            return
                'Laporan diperbarui dan diajukan kembali kepada Guru BK';
        }

        return
            'Data pelanggaran berhasil diperbarui';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(
                    fn (): bool =>
                        PelanggaranResource::canDelete(
                            $this->getRecord()
                        )
                )
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

                        if ($refresh['success'] ?? false) {
                            return;
                        }

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
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl(
            'index'
        );
    }
}
