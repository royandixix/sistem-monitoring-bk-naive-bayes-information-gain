<?php

use Illuminate\Support\Facades\Route;
use App\Services\PelanggaranReportService;

Route::redirect('/', '/admin');


Route::get('/admin/export-pelanggaran-pdf', function(){

    $filters = session(
        'export_pelanggaran_filters',
        []
    );

    return app(PelanggaranReportService::class)
        ->downloadPdf($filters);

});


Route::get('/admin/export-pelanggaran-excel', function(){

    $filters = session(
        'export_pelanggaran_filters',
        []
    );

    return app(PelanggaranReportService::class)
        ->downloadExcel($filters);

});