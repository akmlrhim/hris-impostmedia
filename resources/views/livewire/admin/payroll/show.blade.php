<div class="space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payroll') }}" class="text-slate-600 hover:text-slate-900">
                <x-icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $period->code }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $period->start_date->format('d M') }} – {{ $period->end_date->format('d M Y') }}
                    · Pembayaran {{ $period->payment_date?->format('d M Y') ?? '—' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if (! $period->locked_at)
                <button wire:click="regenerate" wire:confirm="Hitung ulang semua slip gaji periode ini?" class="btn-secondary">
                    Regenerate
                </button>
                <button wire:click="finalize" wire:confirm="Finalize periode dan kunci slip gaji?" class="btn-primary">
                    Finalize
                </button>
            @elseif ($period->status === 'final')
                <button wire:click="markPaid" wire:confirm="Tandai periode sudah dibayar?" class="btn-primary">
                    Tandai Dibayar
                </button>
            @endif
            @php
                $color = match ($period->status) {
                    'paid' => 'emerald',
                    'final' => 'blue',
                    'processed' => 'amber',
                    default => 'slate',
                };
            @endphp
            <span class="badge bg-{{ $color }}-100 text-{{ $color }}-700 capitalize self-center">{{ $period->status }}</span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Total Gross</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ rupiah($totals['gross']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Total Potongan</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ rupiah($totals['deductions']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Total Net</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">{{ rupiah($totals['net']) }}</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="card p-4">
        <div class="relative">
            <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIK karyawan…" class="input pl-9">
        </div>
    </div>

    {{-- Payroll list --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3 text-right">Hadir</th>
                        <th class="px-5 py-3 text-right">Lembur</th>
                        <th class="px-5 py-3 text-right">Earnings</th>
                        <th class="px-5 py-3 text-right">Potongan</th>
                        <th class="px-5 py-3 text-right">Net</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($payrolls as $pr)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900">{{ $pr->employee->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $pr->employee->employee_number }} · {{ $pr->employee->position?->name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">{{ $pr->present_days }}/{{ $pr->working_days }}</td>
                            <td class="px-5 py-3 text-right">{{ floor($pr->overtime_minutes / 60) }}j {{ $pr->overtime_minutes % 60 }}m</td>
                            <td class="px-5 py-3 text-right">{{ rupiah($pr->total_earnings) }}</td>
                            <td class="px-5 py-3 text-right text-red-600">{{ rupiah($pr->total_deductions) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ rupiah($pr->net_salary) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.payroll.payslip', $pr->id) }}" class="text-brand-600 hover:underline text-sm">Lihat Slip</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">Tidak ada data slip gaji.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
