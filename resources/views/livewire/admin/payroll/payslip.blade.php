<div class="space-y-4 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.payroll.show', $payroll->payroll_period_id) }}" class="text-slate-600 hover:text-slate-900">
            <x-icon name="arrow-left" class="w-5 h-5" />
        </a>
        <div>
            <h2 class="text-base font-semibold text-slate-900">Slip Gaji</h2>
            <p class="text-sm text-slate-500">{{ $payroll->period->code }}</p>
        </div>
    </div>

    <div class="card p-6 sm:p-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4 pb-5 border-b">
            <div>
                <p class="text-xs uppercase text-slate-500 tracking-wide">Karyawan</p>
                <p class="font-semibold text-slate-900">{{ $payroll->employee->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $payroll->employee->employee_number }} · {{ $payroll->employee->position?->name ?? '—' }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs uppercase text-slate-500 tracking-wide">Periode</p>
                <p class="font-semibold text-slate-900">
                    {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }} {{ $payroll->period->year }}
                </p>
                <p class="text-sm text-slate-500">
                    {{ $payroll->period->start_date->format('d M') }} – {{ $payroll->period->end_date->format('d M Y') }}
                </p>
                @if ($payroll->period->payment_date)
                    <p class="text-sm text-slate-500">Dibayar: {{ $payroll->period->payment_date->format('d M Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-5 border-b text-sm">
            <div>
                <p class="text-xs text-slate-500">Hari Kerja</p>
                <p class="font-semibold text-slate-900">{{ $payroll->working_days }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Hadir</p>
                <p class="font-semibold text-slate-900">{{ $payroll->present_days }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Cuti</p>
                <p class="font-semibold text-slate-900">{{ $payroll->leave_days }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Lembur</p>
                <p class="font-semibold text-slate-900">{{ floor($payroll->overtime_minutes / 60) }}j {{ $payroll->overtime_minutes % 60 }}m</p>
            </div>
        </div>

        {{-- Earnings --}}
        <div class="py-5 border-b">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Pendapatan</h3>
            <div class="space-y-2">
                @foreach ($earnings as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">{{ $item->component_name }}</span>
                        <span class="font-medium text-slate-900">{{ rupiah($item->amount) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between pt-2 border-t font-semibold text-emerald-600">
                    <span>Total Pendapatan</span>
                    <span>{{ rupiah($payroll->total_earnings) }}</span>
                </div>
            </div>
        </div>

        {{-- Deductions --}}
        <div class="py-5 border-b">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Potongan</h3>
            <div class="space-y-2">
                @forelse ($deductions as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">{{ $item->component_name }}</span>
                        <span class="font-medium text-slate-900">- {{ rupiah($item->amount) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada potongan.</p>
                @endforelse
                @if ($deductions->count())
                    <div class="flex justify-between pt-2 border-t font-semibold text-red-600">
                        <span>Total Potongan</span>
                        <span>- {{ rupiah($payroll->total_deductions) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Total --}}
        <div class="pt-5 flex justify-between items-baseline">
            <span class="font-semibold text-slate-900">Diterima Bersih</span>
            <span class="text-2xl font-bold text-emerald-600">{{ rupiah($payroll->net_salary) }}</span>
        </div>
    </div>
</div>
