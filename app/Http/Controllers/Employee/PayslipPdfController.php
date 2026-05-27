<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PayslipPdfController extends Controller
{
    public function __invoke(Payroll $payroll): Response
    {
        $employee = auth()->user()?->employee;
        abort_if(! $employee || $payroll->employee_id !== $employee->id, 403);

        $payroll->load(['employee', 'period', 'items']);

        $earnings = $payroll->items->where('type', 'earning');
        $deductions = $payroll->items->where('type', 'deduction');

        $logoPath = public_path('icons/icon-128.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.payslip', compact('payroll', 'earnings', 'deductions', 'logo'))
            ->setPaper('a4', 'portrait');

        $filename = 'slip-gaji-'.$payroll->period->code.'.pdf';

        return $pdf->download($filename);
    }
}
