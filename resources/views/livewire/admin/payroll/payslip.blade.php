<div class="space-y-4 max-w-3xl">
  <div class="flex items-center gap-3">
    <a wire:navigate href="{{ route('admin.payroll.show', $payroll->period) }}"
      class="text-slate-600 hover:text-slate-900">
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
        <p class="text-sm text-slate-500">{{ $payroll->employee->employee_number }}</p>
      </div>
      <div class="sm:text-right">
        <p class="text-xs uppercase text-slate-500 tracking-wide">Periode</p>
        <p class="font-semibold text-slate-900">
          {{ \Carbon\Carbon::create()->month($payroll->period->month)->translatedFormat('F') }}
          {{ $payroll->period->year }}
        </p>
        <p class="text-sm text-slate-500">
          {{ $payroll->period->start_date->translatedFormat('d M') }} – {{ $payroll->period->end_date->translatedFormat('d M Y') }}
        </p>
        @if ($payroll->period->payment_date)
          <p class="text-sm text-slate-500">Dibayar: {{ $payroll->period->payment_date->translatedFormat('d M Y') }}</p>
        @endif
      </div>
    </div>

    {{-- Stats --}}
    <div class="py-5 border-b text-sm">
      <div>
        <p class="text-xs text-slate-500">Hadir</p>
        <p class="font-semibold text-slate-900">{{ $payroll->present_days }} hari</p>
      </div>
    </div>

    {{-- Earnings --}}
    <div class="py-5 border-b">
      <h3 class="text-sm font-semibold text-slate-900 mb-3">Pendapatan</h3>
      <div class="space-y-2">
        @foreach ($earnings as $item)
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-600">{{ $item->component_name }}</span>
            <div class="flex items-center gap-2">
              <span class="font-medium text-slate-900 tabular-nums">{{ rupiah($item->amount) }}</span>
              @if ($item->component_code !== 'BASIC' && !$payroll->period->locked_at)
                <button wire:click="removeItem({{ $item->id }})"
                  wire:confirm="Hapus item {{ $item->component_name }}?"
                  class="text-slate-300 hover:text-red-500 transition">
                  <x-icon name="x" class="w-3.5 h-3.5" />
                </button>
              @endif
            </div>
          </div>
        @endforeach
        <div class="tabular-nums flex justify-between pt-2 border-t font-semibold text-emerald-600">
          <span>Total Pendapatan</span>
          <span>{{ rupiah($payroll->total_earnings) }}</span>
        </div>
      </div>
    </div>

    {{-- Deductions --}}
    @if ($deductions->isNotEmpty())
      <div class="py-5 border-b">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Potongan</h3>
        <div class="space-y-2">
          @foreach ($deductions as $item)
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-600">
                {{ $item->component_name }}
                @if ($item->notes)
                  <span class="text-xs text-slate-400 ml-1">({{ $item->notes }})</span>
                @endif
              </span>
              <div class="flex items-center gap-2">
                <span class="font-medium text-red-600 tabular-nums">- {{ rupiah($item->amount) }}</span>
                @if (!$payroll->period->locked_at)
                  <button wire:click="removeItem({{ $item->id }})"
                    wire:confirm="Hapus potongan {{ $item->component_name }}?"
                    class="text-slate-300 hover:text-red-500 transition">
                    <x-icon name="x" class="w-3.5 h-3.5" />
                  </button>
                @endif
              </div>
            </div>
          @endforeach
          <div class="tabular-nums flex justify-between pt-2 border-t font-semibold text-red-600">
            <span>Total Potongan</span>
            <span>- {{ rupiah($payroll->total_deductions + $payroll->total_tax_pph21 + $payroll->total_bpjs) }}</span>
          </div>
        </div>
      </div>
    @endif

    {{-- Net --}}
    <div class="pt-5 flex justify-between items-baseline">
      <span class="font-semibold text-slate-900">Diterima Bersih</span>
      <span class="text-2xl font-bold tabular-nums text-emerald-600">{{ rupiah($payroll->net_salary) }}</span>
    </div>
  </div>

  {{-- Tambah Item --}}
  @if ($payroll->period->locked_at)
    <div class="card p-4 bg-amber-50 border border-amber-200 text-sm text-amber-800 flex items-center gap-2">
      <x-icon name="lock" class="w-4 h-4" />
      Periode <strong>{{ $payroll->period->code }}</strong> sudah dikunci. Item slip tidak bisa diubah lagi.
    </div>
  @else
  <div class="card p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-900">Tambah Item</h3>
      <button type="button" @click="$wire.set('showItemForm', !$wire.showItemForm, true)" class="text-xs text-brand-600 hover:underline font-medium">
        {{ $showItemForm ? 'Batal' : '+ Tambah' }}
      </button>
    </div>

    @if ($showItemForm)
      <form wire:submit="addItem" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Tipe</label>
            <select wire:model="itemType" class="input">
              <option value="earning">Pendapatan</option>
              <option value="deduction">Potongan</option>
            </select>
          </div>
          <div>
            <label class="label">Nama Item <span class="text-red-500">*</span></label>
            <input wire:model="itemName" list="item-suggestions" class="input" placeholder="Contoh: Bonus, PPh 21…">
            <datalist id="item-suggestions">
              <option value="Bonus">
              <option value="Tunjangan Transport">
              <option value="Tunjangan Makan">
              <option value="PPh 21">
              <option value="Potongan Ketidakhadiran">
              <option value="BPJS Kesehatan">
            </datalist>
            @error('itemName')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        {{-- Pilihan persen / nominal --}}
        <div>
          <label class="label">Cara Hitung</label>
          <div class="flex rounded-lg border border-slate-300 overflow-hidden">
            <label class="flex-1 flex items-center justify-center gap-2 py-2 text-sm cursor-pointer
              {{ $itemCalcType === 'fixed' ? 'bg-brand-600 text-white font-medium' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
              <input type="radio" wire:model.live="itemCalcType" value="fixed" class="sr-only">
              <x-icon name="banknote" class="w-4 h-4" /> Nominal
            </label>
            <label class="flex-1 flex items-center justify-center gap-2 py-2 text-sm cursor-pointer border-l border-slate-300
              {{ $itemCalcType === 'percent' ? 'bg-brand-600 text-white font-medium' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
              <input type="radio" wire:model.live="itemCalcType" value="percent" class="sr-only">
              <x-icon name="percent" class="w-4 h-4" /> Persentase
            </label>
          </div>
        </div>

        @if ($itemCalcType === 'percent')
          <div class="space-y-3">
            <div>
              <label class="label">Persentase <span class="text-red-500">*</span></label>
              <div class="relative">
                <input type="number" wire:model.live="itemPercent" step="0.01" min="0.01" max="100"
                  class="input pr-10" placeholder="Contoh: 5">
                <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm font-medium">%</span>
              </div>
              @error('itemPercent')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
            {{-- Preview kalkulasi --}}
            @if ($itemPercent > 0)
              <div class="flex items-center justify-between rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                <span class="text-slate-500">
                  {{ $itemPercent }}% × {{ rupiah($payroll->gross_salary) }}
                </span>
                <span class="font-semibold text-slate-900 tabular-nums">= {{ rupiah($itemAmount) }}</span>
              </div>
            @endif
          </div>
        @else
          <div>
            <label class="label">Nominal <span class="text-red-500">*</span></label>
            <x-currency-input wire-model="itemAmount" />
            @error('itemAmount')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
          <button type="button" @click="$wire.set('showItemForm', false, true)" class="btn-secondary">Batal</button>
          <button type="submit" class="btn-primary">Simpan Item</button>
        </div>
      </form>
    @else
      <p class="text-sm text-slate-400 text-center py-4">Klik "+ Tambah" untuk menambah bonus, tunjangan, atau potongan PPh 21.</p>
    @endif
  </div>
  @endif
</div>
