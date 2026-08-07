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
        <button type="button" @click="$wire.set('showAddForm', true, true); $wire.openAddForm()" class="btn-secondary">
          Tambah Karyawan
        </button>
        <button wire:click="regenerate" wire:confirm="Hitung ulang slip gaji karyawan di periode ini?" class="btn-secondary">
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
      @can(\App\Enums\Permission::ViewSalary->value)
        <a href="{{ route('admin.payroll.period.pdf', $period) }}" target="_blank" class="btn-secondary flex items-center gap-2">
          <x-icon name="printer" class="w-4 h-4" />
          Cetak PDF
        </a>
      @endcan
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

  {{-- Modal: Tambah Karyawan --}}
  <x-modal show="showAddForm" max-width="2xl" title="Tambah Karyawan ke Periode">
    <form wire:submit="addEmployees" class="space-y-4">
      <div class="relative">
        <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
        <input wire:model.live.debounce.300ms="employeeSearch" type="text"
          placeholder="Cari nama atau nomor karyawan…" class="input pl-9">
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Karyawan Tersedia</label>
          <span class="text-xs text-slate-500">{{ count($selectedEmployees) }} dipilih</span>
        </div>
        @if ($availableEmployees->isEmpty())
          <p class="text-sm text-slate-500 rounded-lg border border-slate-200 p-3">
            Tidak ada karyawan aktif lain yang bisa ditambahkan.
          </p>
        @else
          <div class="rounded-lg border border-slate-200 divide-y divide-slate-100 max-h-64 overflow-y-auto">
            <label class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 cursor-pointer sticky top-0">
              <input type="checkbox" wire:model.live="addSelectAll"
                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
              <span class="text-sm font-semibold text-slate-700">Pilih Semua</span>
            </label>
            @foreach ($availableEmployees as $emp)
              <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" value="{{ $emp->id }}" wire:model.live="selectedEmployees"
                  class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm text-slate-900">{{ $emp->full_name }}</span>
                <span class="text-xs text-slate-400 ml-auto">{{ $emp->employee_number }}</span>
              </label>
            @endforeach
          </div>
        @endif
        @error('selectedEmployees')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex flex-wrap gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" @click="$wire.set('showAddForm', false, true)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="addEmployees">
          <span wire:loading.remove wire:target="addEmployees">Tambahkan</span>
          <span wire:loading wire:target="addEmployees">Memproses…</span>
        </button>
      </div>
    </form>
  </x-modal>

  {{-- Summary --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="card p-4">
      <p class="text-xs uppercase text-slate-500">Total Pendapatan Kotor</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ rupiah_masked($totals->gross ?? 0) }}</p>
    </div>
    <div class="card p-4">
      <p class="text-xs uppercase text-slate-500">Total Net Gaji</p>
      <p class="text-xl font-bold text-emerald-600 mt-1">{{ rupiah_masked($totals->net ?? 0) }}</p>
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
  <div class="card-table">
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Karyawan</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Hadir (hari)</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Gaji Kotor</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Gaji Net</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($payrolls as $pr)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 whitespace-nowrap">
                <p class="font-medium text-slate-900">{{ $pr->employee->full_name }}</p>
                <p class="text-xs text-slate-500">{{ $pr->employee->employee_number }}</p>
              </td>
              <td class="px-5 py-3 text-right text-slate-600 whitespace-nowrap">{{ $pr->present_days }}</td>
              <td class="px-5 py-3 text-right text-slate-700 whitespace-nowrap">{{ rupiah_masked($pr->gross_salary) }}</td>
              <td class="px-5 py-3 text-right font-semibold text-emerald-600 whitespace-nowrap">{{ rupiah_masked($pr->net_salary) }}</td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <a wire:navigate href="{{ route('admin.payroll.payslip', $pr) }}"
                    class="px-2 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    {{ $period->locked_at ? 'Lihat Slip' : 'Edit Slip' }}
                  </a>
                  @if (!$period->locked_at)
                    <button wire:click="removePayroll({{ $pr->id }})"
                      wire:confirm="Hapus slip {{ $pr->employee->full_name }} dari periode ini?"
                      class="px-2 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                      Hapus
                    </button>
                  @endif
                </div>
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


</div>{{-- end single root --}}
