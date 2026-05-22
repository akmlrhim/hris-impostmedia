<div>
    <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3">
        <a wire:navigate href="{{ route('mobile.payslip') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
            <x-icon name="arrow-left" class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-lg font-bold text-slate-900">Slip Gaji</h1>
            <p class="text-xs text-slate-500">{{ $payroll->period->code }}</p>
        </div>
    </div>

    <div class="p-4 space-y-3">
        <div class="card p-5">
            {{-- Periode --}}
            <div class="text-center pb-4 border-b">
                <p class="text-xs uppercase text-slate-500">Periode</p>
                <p class="font-bold text-slate-900 text-lg">
                    {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }} {{ $payroll->period->year }}
                </p>
                @if ($payroll->period->payment_date)
                    <p class="text-xs text-slate-500 mt-0.5">Dibayar: {{ $payroll->period->payment_date->translatedFormat('d M Y') }}</p>
                @endif
            </div>

            {{-- Stats kehadiran --}}
            <div class="grid grid-cols-2 gap-3 py-4 border-b text-sm">
                <div>
                    <p class="text-xs text-slate-500">Hari Kerja</p>
                    <p class="font-semibold">{{ $payroll->present_days }} / {{ $payroll->working_days }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Tidak Hadir</p>
                    <p class="font-semibold">{{ $payroll->absent_days }} hari</p>
                </div>
            </div>

            {{-- Pendapatan --}}
            <div class="py-4 border-b">
                <p class="text-xs uppercase text-slate-500 mb-2">Pendapatan</p>
                @foreach ($earnings as $item)
                    <div class="flex justify-between text-sm py-1">
                        <span class="text-slate-600">{{ $item->component_name }}</span>
                        <span class="font-medium tabular-nums">{{ rupiah($item->amount) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between pt-2 mt-1 border-t font-semibold text-emerald-600 text-sm tabular-nums">
                    <span>Total</span>
                    <span>{{ rupiah($payroll->total_earnings) }}</span>
                </div>
            </div>

            {{-- Potongan (hanya tampil jika ada) --}}
            @if ($deductions->isNotEmpty())
                <div class="py-4 border-b">
                    <p class="text-xs uppercase text-slate-500 mb-2">Potongan</p>
                    @foreach ($deductions as $item)
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-slate-600">
                                {{ $item->component_name }}
                                @if ($item->notes)
                                    <span class="text-xs text-slate-400">({{ $item->notes }})</span>
                                @endif
                            </span>
                            <span class="font-medium text-red-600 tabular-nums">- {{ rupiah($item->amount) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between pt-2 mt-1 border-t font-semibold text-red-600 text-sm tabular-nums">
                        <span>Total Potongan</span>
                        <span>- {{ rupiah($payroll->total_deductions + $payroll->total_tax_pph21 + $payroll->total_bpjs) }}</span>
                    </div>
                </div>
            @endif

            {{-- Diterima bersih --}}
            <div class="pt-4 text-center">
                <p class="text-xs uppercase text-slate-500">Diterima</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1 tabular-nums">{{ rupiah($payroll->net_salary) }}</p>
            </div>
        </div>
    </div>
</div>
