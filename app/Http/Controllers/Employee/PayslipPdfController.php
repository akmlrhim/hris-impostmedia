<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

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
            ->setPaper($this->paperSize($payroll, $earnings, $deductions), 'portrait');

        $filename = 'slip-gaji-'.$payroll->period->code.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * A4 is far bigger than a real payslip needs. Keep a small, fixed width
     * (A5) but scale the page height to how many line items there actually
     * are, so a slip with few components isn't mostly blank paper and one
     * with many components doesn't get cramped.
     *
     * @param  Collection<int, PayrollItem>  $earnings
     * @param  Collection<int, PayrollItem>  $deductions
     * @return array<int, float>
     */
    private function paperSize(Payroll $payroll, Collection $earnings, Collection $deductions): array
    {
        $width = 419.55; // A5 width in points — keeps the "small paper" feel

        $rowCount = $earnings->count()
            + max($deductions->count(), 1)
            + ($payroll->overtime_amount > 0 ? 1 : 0);

        $hasBankInfo = (bool) ($payroll->employee->bank_name || $payroll->employee->bank_account_number);

        $fixedChrome = 375; // header, employee info, section headers/totals, net box, footer
        $rowHeight = 17;
        $bankBoxHeight = $hasBankInfo ? 46 : 0;

        $height = $fixedChrome + ($rowCount * $rowHeight) + $bankBoxHeight;

        return [0, 0, $width, max(400, min($height, 900))];
    }
}
