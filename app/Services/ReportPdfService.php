<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportPdfService
{
    public function download(
        string $view,
        array $data,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait',
    ): Response {
        return Pdf::setOption([
            'dpi' => 120,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ])
            ->loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setWarnings(false)
            ->download($filename);
    }
}
