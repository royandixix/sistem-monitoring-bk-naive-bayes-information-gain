<?php

namespace App\Http\Controllers;

use App\Services\PelanggaranReportService;

class ExportPelanggaranController extends Controller
{
    public function pdf()
    {
        $filters=session()->get('export_pelanggaran_filters',[]);

        return app(PelanggaranReportService::class)
            ->downloadPdf($filters);
    }

    public function excel()
    {
        $filters=session()->get('export_pelanggaran_filters',[]);

        return app(PelanggaranReportService::class)
            ->downloadExcel($filters);
    }
}