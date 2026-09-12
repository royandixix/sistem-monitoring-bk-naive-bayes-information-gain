<?php

use Illuminate\Support\Facades\Route;
use App\Services\PelanggaranReportService;

Route::redirect('/', '/admin');

Route::get('/export-pelanggaran/pdf', function () {
    $filters = session('export_pelanggaran_filters', []);

    return app(PelanggaranReportService::class)
        ->downloadPdf($filters);
})->name('export.pelanggaran.pdf');

Route::get('/export-pelanggaran/excel', function () {
    $filters = session('export_pelanggaran_filters', []);

    return app(PelanggaranReportService::class)
        ->downloadExcel($filters);
})->name('export.pelanggaran.excel');