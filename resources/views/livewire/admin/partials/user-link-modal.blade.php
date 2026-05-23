<x-modal show="showLinkForm" max-width="md" title="Hubungkan ke Data Karyawan">
  <div class="space-y-4">
    @if ($linkingUserId)
      @php $linkUser = $users->firstWhere('id', $linkingUserId) ?? \App\Models\User::find($linkingUserId) @endphp
      <p class="text-sm text-slate-600">
        Pilih karyawan yang akan dihubungkan ke akun <strong>{{ $linkUser?->name }}</strong>.
      </p>
    @endif

    <div class="relative">
      <input wire:model.live.debounce="employeeSearch" placeholder="Cari nama atau nomor karyawan…" class="input pl-4" />
    </div>

    <div class="border border-slate-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100">
      @forelse ($availableEmployees as $emp)
        <label class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer">
          <input type="radio" wire:model="selectedEmployeeId" value="{{ $emp->id }}" class="text-brand-600 shrink-0">
          <div>
            <p class="text-sm font-medium text-slate-900">{{ $emp->full_name }}</p>
            <p class="text-xs text-slate-500">{{ $emp->employee_number }}</p>
          </div>
        </label>
      @empty
        <div class="px-4 py-8 text-center text-sm text-slate-400">
          @if ($employeeSearch)
            Tidak ada karyawan yang cocok.
          @else
            Semua karyawan sudah terhubung ke akun.
          @endif
        </div>
      @endforelse
    </div>

    @error('selectedEmployeeId')
      <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror

    <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
      <button type="button" @click="$wire.set('showLinkForm', false, true)" class="btn-secondary">Batal</button>
      <button type="button" wire:click="linkEmployee" class="btn-primary">Hubungkan</button>
    </div>
  </div>
</x-modal>
