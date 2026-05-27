<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PayrollPdfController extends Controller
{
    public function __invoke(PayrollPeriod $period): Response
    {
        Gate::authorize('manage_payroll');

        $payrolls = $period->payrolls()
            ->with(['employee'])
            ->orderBy('id')
            ->get();

        $logoPath = public_path('icons/icon-128.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.payroll-period', compact('period', 'payrolls', 'logo'))
            ->setPaper('a4', 'landscape');

        $filename = 'payroll-'.$period->code.'.pdf';

        return $pdf->download($filename);
    }
}
