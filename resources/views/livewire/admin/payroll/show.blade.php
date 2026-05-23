<div>
<div class="space-y-4">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <a wire:navigate href="{{ route('admin.payroll') }}" class="text-slate-600 hover:text-slate-900">
        <x-icon name="arrow-left" class="w-5 h-5" />
      </a>
      <div>
        <h2 class="text-base font-semibold text-slate-900">{{ $period->code }}</h2>
        <p class="text-sm text-slate-500">
          {{ $period->start_date->translatedFormat('d M') }} – {{ $period->end_date->translatedFormat('d M Y') }}
          · Pembayaran {{ $period->payment_date?->translatedFormat('d M Y') ?? '-' }}
        </p>
      </div>
    </div>

    <div class="flex flex-wrap gap-2">
      @if (!$period->locked_at)
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
      <button onclick="window.print()" class="btn-secondary">
        Cetak
      </button>
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
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="card p-4">
      <p class="text-xs uppercase text-slate-500">Total Pendapatan Kotor</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ rupiah($totals['gross']) }}</p>
    </div>
    <div class="card p-4">
      <p class="text-xs uppercase text-slate-500">Total Net Gaji</p>
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
            <th class="px-5 py-3 text-right">Gaji Kotor</th>
            <th class="px-5 py-3 text-right">Gaji Net</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($payrolls as $pr)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3">
                <p class="font-medium text-slate-900">{{ $pr->employee->full_name }}</p>
                <p class="text-xs text-slate-500">{{ $pr->employee->employee_number }}</p>
              </td>
              <td class="px-5 py-3 text-right text-slate-600">{{ $pr->present_days }}/{{ $pr->working_days }}</td>
              <td class="px-5 py-3 text-right text-slate-700">{{ rupiah($pr->gross_salary) }}</td>
              <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ rupiah($pr->net_salary) }}</td>
              <td class="px-5 py-3 text-right">
                <a wire:navigate href="{{ route('admin.payroll.payslip', $pr) }}"
                  class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                  Lihat Slip
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">Tidak ada data slip gaji.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($payrolls->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $payrolls->links() }}</div>
    @endif
  </div>
</div>

{{-- ===== PRINT AREA ===== --}}
<div id="print-area" style="display:none;">
  <div style="font-family: sans-serif; font-size: 13px; color: #1e293b;">

    {{-- Header cetak --}}
    <div style="text-align:center; margin-bottom: 1.5rem; border-bottom: 2px solid #1e293b; padding-bottom: 1rem;">
      <h1 style="font-size: 18px; font-weight: 700; margin: 0;">Laporan Payroll</h1>
      <p style="margin: 4px 0 0; font-size: 13px; color: #475569;">
        Periode: <strong>{{ $period->code }}</strong> -
        {{ $period->start_date->translatedFormat('d M') }} s/d {{ $period->end_date->translatedFormat('d M Y') }}
      </p>
      <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">
        Tanggal cetak: {{ now()->translatedFormat('d F Y, H:i') }}
        @if ($period->payment_date)
          · Tgl Bayar: {{ $period->payment_date->translatedFormat('d M Y') }}
        @endif
      </p>
    </div>

    {{-- Tabel --}}
    <table style="width:100%; border-collapse: collapse; font-size: 12px;">
      <thead>
        <tr style="background: #f1f5f9;">
          <th style="padding: 8px 10px; text-align:left; border: 1px solid #cbd5e1;">No</th>
          <th style="padding: 8px 10px; text-align:left; border: 1px solid #cbd5e1;">NIK</th>
          <th style="padding: 8px 10px; text-align:left; border: 1px solid #cbd5e1;">Nama Karyawan</th>
          <th style="padding: 8px 10px; text-align:center; border: 1px solid #cbd5e1;">Hadir</th>
          <th style="padding: 8px 10px; text-align:right; border: 1px solid #cbd5e1;">Gaji Kotor</th>
          <th style="padding: 8px 10px; text-align:right; border: 1px solid #cbd5e1;">Potongan</th>
          <th style="padding: 8px 10px; text-align:right; border: 1px solid #cbd5e1;">Gaji Net</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($allPayrolls as $i => $pr)
          <tr style="{{ $i % 2 === 0 ? '' : 'background: #f8fafc;' }}">
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; text-align:center;">{{ $i + 1 }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; font-family: monospace;">{{ $pr->employee->employee_number }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; font-weight: 600;">{{ $pr->employee->full_name }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; text-align:center;">{{ $pr->present_days }}/{{ $pr->working_days }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; text-align:right;">{{ rupiah($pr->gross_salary) }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; text-align:right; color: #dc2626;">{{ rupiah($pr->total_deductions + $pr->total_bpjs + $pr->total_tax_pph21) }}</td>
            <td style="padding: 7px 10px; border: 1px solid #e2e8f0; text-align:right; font-weight: 700; color: #059669;">{{ rupiah($pr->net_salary) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background: #1e293b; color: white; font-weight: 700;">
          <td colspan="4" style="padding: 9px 10px; border: 1px solid #334155; text-align:right;">Total ({{ $allPayrolls->count() }} karyawan)</td>
          <td style="padding: 9px 10px; border: 1px solid #334155; text-align:right;">{{ rupiah($totals['gross']) }}</td>
          <td style="padding: 9px 10px; border: 1px solid #334155; text-align:right;">{{ rupiah($allPayrolls->sum(fn($p) => $p->total_deductions + $p->total_bpjs + $p->total_tax_pph21)) }}</td>
          <td style="padding: 9px 10px; border: 1px solid #334155; text-align:right;">{{ rupiah($totals['net']) }}</td>
        </tr>
      </tfoot>
    </table>

    {{-- Tanda tangan --}}
    <div style="margin-top: 3rem; display: flex; justify-content: flex-end;">
      <div style="text-align: center; width: 200px;">
        <p style="font-size: 12px; color: #475569; margin: 0;">Disetujui oleh,</p>
        <div style="height: 60px;"></div>
        <p style="font-size: 12px; font-weight: 700; border-top: 1px solid #94a3b8; padding-top: 4px; margin: 0;">(__________________________)</p>
      </div>
    </div>
  </div>
</div>

<script>
  window.addEventListener('beforeprint', () => {
    document.getElementById('print-area').style.display = 'block';
  });
  window.addEventListener('afterprint', () => {
    document.getElementById('print-area').style.display = 'none';
  });
</script>

</div>{{-- end single root --}}
