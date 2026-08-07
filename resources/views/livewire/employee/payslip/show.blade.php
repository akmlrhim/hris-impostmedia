<div>
    <x-mobile-header title="Slip Gaji" :subtitle="$payroll->period->code" :back="route('mobile.payslip')">
        <x-slot:action>
            <a href="{{ route('mobile.payslip.pdf', $payroll) }}" target="_blank" class="hero-action">
                <x-icon name="download" class="w-4 h-4" />
                PDF
            </a>
        </x-slot:action>
    </x-mobile-header>

    <div class="px-4 -mt-10 pb-32">
        <div class="card-float p-5">
            {{-- Periode --}}
            <div class="text-center pb-4 border-b border-slate-100">
                <p class="text-xs uppercase text-navy-400 tracking-wide">Periode</p>
                <p class="font-bold text-navy-800 text-lg">
                    {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }} {{ $payroll->period->year }}
                </p>
                @if ($payroll->period->payment_date)
                    <p class="text-xs text-slate-500 mt-0.5">Dibayar: {{ $payroll->period->payment_date->translatedFormat('d M Y') }}</p>
                @endif
            </div>

{{-- Pendapatan --}}
            <div class="py-4 border-b border-slate-100">
                <p class="text-xs uppercase text-navy-400 tracking-wide mb-2">Pendapatan</p>
                @foreach ($earnings as $item)
                    <div class="flex justify-between text-sm py-1">
                        <span class="text-slate-600">{{ $item->component_name }}</span>
                        <span class="font-medium">{{ rupiah($item->amount) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between pt-2 mt-1 border-t border-slate-100 font-semibold text-emerald-600 text-sm">
                    <span>Total</span>
                    <span>{{ rupiah($payroll->total_earnings) }}</span>
                </div>
            </div>

            {{-- Potongan (hanya tampil jika ada) --}}
            @if ($deductions->isNotEmpty())
                <div class="py-4 border-b border-slate-100">
                    <p class="text-xs uppercase text-navy-400 tracking-wide mb-2">Potongan</p>
                    @foreach ($deductions as $item)
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-slate-600">
                                {{ $item->component_name }}
                                @if ($item->notes)
                                    <span class="text-xs text-slate-400">({{ $item->notes }})</span>
                                @endif
                            </span>
                            <span class="font-medium text-red-600">- {{ rupiah($item->amount) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between pt-2 mt-1 border-t border-slate-100 font-semibold text-red-600 text-sm">
                        <span>Total Potongan</span>
                        <span>- {{ rupiah($payroll->total_deductions + $payroll->total_tax_pph21 + $payroll->total_bpjs) }}</span>
                    </div>
                </div>
            @endif

            {{-- Diterima bersih --}}
            <div class="pt-4 text-center">
                <p class="text-xs uppercase text-navy-400 tracking-wide">Diterima</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ rupiah($payroll->net_salary) }}</p>
            </div>
        </div>
    </div>
</div>
