<div class="space-y-4">
  <x-page-header title="Periode Payroll" description="Generate & kelola perhitungan gaji bulanan.">
    <x-slot:action>
      <button wire:click="openForm" class="btn-primary">Generate Periode</button>
    </x-slot:action>
  </x-page-header>

  <x-modal show="showForm" max-width="2xl" title="Generate Periode Payroll">
    <form wire:submit="createPeriod" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Tahun</label>
          <input type="number" wire:model="year" min="2020" max="2099" class="input" placeholder="{{ now()->year }}">
          @error('year')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Bulan</label>
          <select wire:model="month" class="input">
            @foreach (range(1, 12) as $m)
              <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
          @error('month')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Tanggal Pembayaran</label>
          <input type="date" onclick="this.showPicker()" wire:model="payment_date" class="input">
        </div>
      </div>
      <p class="text-xs text-slate-500">
        Sistem akan menghitung gaji untuk semua karyawan aktif berdasarkan kehadiran, cuti, lembur,
        dan komponen yang tersedia. Periode existing dengan status <em>draft</em> akan ditimpa.
      </p>
      <div class="flex flex-wrap gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="createPeriod">Generate Sekarang</span>
          <span wire:loading wire:target="createPeriod">Memproses…</span>
        </button>
      </div>
    </form>
  </x-modal>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Periode</th>
            <th class="px-5 py-3">Tanggal</th>
            <th class="px-5 py-3">Pembayaran</th>
            <th class="px-5 py-3 text-right">Karyawan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($periods as $p)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 font-medium">{{ $p->code }}</td>
              <td class="px-5 py-3">{{ $p->start_date->translatedFormat('d M') }} – {{ $p->end_date->translatedFormat('d M Y') }}</td>
              <td class="px-5 py-3">{{ $p->payment_date?->translatedFormat('d M Y') ?? '-' }}</td>
              <td class="px-5 py-3 text-right">{{ $p->payrolls_count }}</td>
              <td class="px-5 py-3">
                @php
                  $color = match ($p->status) {
                      'paid' => 'emerald',
                      'final' => 'blue',
                      'processed' => 'amber',
                      default => 'slate',
                  };
                @endphp
                <span
                  class="badge bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $p->status }}</span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a wire:navigate href="{{ route('admin.payroll.show', $p) }}"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    Detail
                  </a>
                  @if (!$p->locked_at)
                    <button wire:click="delete({{ $p->id }})"
                      wire:confirm="Hapus periode ini? Semua slip gaji terkait akan ikut terhapus."
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                      Hapus
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada periode payroll.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-5 py-3 border-t border-slate-200">{{ $periods->links() }}</div>
  </div>
</div>
