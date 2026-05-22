<x-modal show="showForm" max-width="lg" :title="$editingId ? 'Ubah Pengguna' : 'Tambah Pengguna'">
  <form wire:submit="save" class="space-y-4">
    <div>
      <label class="label">Nama</label>
      <input wire:model="name" class="input" placeholder="Masukkan nama lengkap">
      @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="label">Email</label>
      <input type="email" wire:model="email" class="input" placeholder="Masukkan alamat email">
      @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Multi-role checkboxes --}}
    <div>
      <label class="label">Peran <span class="text-red-500">*</span></label>
      <div class="grid grid-cols-1 gap-2">
        @foreach ($allRoles as $r)
          <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50 cursor-pointer transition
            {{ in_array($r->value, $selectedRoles) ? 'border-brand-400 bg-brand-50' : '' }}">
            <input type="checkbox" wire:model="selectedRoles" value="{{ $r->value }}"
              class="rounded border-slate-300 text-brand-600">
            <div>
              <p class="text-sm font-medium text-slate-900">{{ $r->label() }}</p>
              <p class="text-xs text-slate-400">
                @if ($r->value === 'admin') Akses penuh ke semua fitur
                @elseif ($r->value === 'hr') Kelola karyawan, payroll, absensi
                @else Absensi, slip gaji, profil pribadi
                @endif
              </p>
            </div>
          </label>
        @endforeach
      </div>
      @error('selectedRoles') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    @if (! $editingId)
      <div>
        <label class="label">Kata Sandi</label>
        <input type="password" wire:model="password" class="input" autocomplete="new-password" placeholder="Masukkan kata sandi">
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Konfirmasi Kata Sandi</label>
        <input type="password" wire:model="password_confirmation" class="input" autocomplete="new-password" placeholder="Masukkan ulang kata sandi">
      </div>
    @endif

    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" wire:model="is_active" class="rounded">
      <span class="text-sm text-slate-700">Akun aktif</span>
    </label>

    <x-form-actions />
  </form>
</x-modal>
