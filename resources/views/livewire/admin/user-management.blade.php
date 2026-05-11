<div class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-base font-semibold text-slate-900">Manajemen Pengguna</h2>
      <p class="text-sm text-slate-500">Kelola akun pengguna dan peran sistem.</p>
    </div>
    <button wire:click="open" class="btn-primary">
      <x-icon name="plus" class="w-4 h-4" /> Tambah Pengguna
    </button>
  </div>

  {{-- Filters --}}
  <div class="flex flex-wrap gap-3">
    <div class="relative flex-1 min-w-48">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce="search" placeholder="Cari nama atau email…" class="input pl-9">
    </div>
    <select wire:model.live="filterRole" class="input w-44">
      <option value="">Semua Peran</option>
      @foreach ($roles as $r)
        <option value="{{ $r->value }}">{{ $r->label() }}</option>
      @endforeach
    </select>
  </div>

  {{-- Create / Edit Modal --}}
  <x-modal show="showForm" max-width="lg" :title="$editingId ? 'Ubah Pengguna' : 'Tambah Pengguna'">
    <form wire:submit="save" class="space-y-4">
      <div>
        <label class="label">Nama</label>
        <input wire:model="name" class="input" placeholder="Budi Santoso">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Email</label>
        <input type="email" wire:model="email" class="input" placeholder="budi@perusahaan.com">
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Peran</label>
        <select wire:model="role" class="input">
          @foreach ($roles as $r)
            <option value="{{ $r->value }}">{{ $r->label() }}</option>
          @endforeach
        </select>
        @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      @if (! $editingId)
        <div>
          <label class="label">Kata Sandi</label>
          <input type="password" wire:model="password" class="input" autocomplete="new-password">
          @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="label">Konfirmasi Kata Sandi</label>
          <input type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
        </div>
      @endif

      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" wire:model="is_active" class="rounded">
        <span class="text-sm text-slate-700">Akun aktif</span>
      </label>

      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
      </div>
    </form>
  </x-modal>

  {{-- Change Password Modal --}}
  <x-modal show="showPasswordForm" max-width="sm" title="Ganti Kata Sandi">
    <form wire:submit="savePassword" class="space-y-4">
      <div>
        <label class="label">Kata Sandi Baru</label>
        <input type="password" wire:model="newPassword" class="input" autocomplete="new-password">
        @error('newPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Konfirmasi Kata Sandi Baru</label>
        <input type="password" wire:model="newPasswordConfirmation" class="input">
      </div>
      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showPasswordForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
      </div>
    </form>
  </x-modal>

  {{-- Table --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Pengguna</th>
            <th class="px-5 py-3">Peran</th>
            <th class="px-5 py-3">Data Karyawan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($users as $u)
            <tr class="hover:bg-slate-50 {{ !$u->is_active ? 'opacity-60' : '' }}">
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-semibold shrink-0">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3">
                @php
                  $roleColors = [
                    'admin'    => 'bg-blue-100 text-blue-700',
                    'hr'       => 'bg-emerald-100 text-emerald-700',
                    'employee' => 'bg-slate-100 text-slate-600',
                  ];
                  $roleColor = $roleColors[$u->role?->value ?? ''] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <span class="badge {{ $roleColor }}">{{ $u->role?->label() ?? '—' }}</span>
              </td>
              <td class="px-5 py-3">
                @if ($u->employee)
                  <a wire:navigate href="{{ route('admin.employees.show', $u->employee) }}"
                    class="text-brand-600 hover:underline text-xs font-medium">
                    {{ $u->employee->employee_number }} — {{ $u->employee->full_name }}
                  </a>
                @else
                  <span class="text-xs text-slate-400">Belum terhubung</span>
                @endif
              </td>
              <td class="px-5 py-3">
                @if ($u->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                @else
                  <span class="badge bg-slate-100 text-slate-500">Nonaktif</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-1.5">
                  <button wire:click="open({{ $u->id }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                  </button>
                  <button wire:click="openPasswordForm({{ $u->id }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-50 text-slate-600 hover:bg-slate-100 transition">
                    <x-icon name="lock" class="w-3.5 h-3.5" /> Sandi
                  </button>
                  <button wire:click="toggleActive({{ $u->id }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $u->is_active ? 'bg-slate-50 text-slate-600 hover:bg-slate-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition">
                    <x-icon name="{{ $u->is_active ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5" />
                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  @if ($u->id !== auth()->id())
                    <button wire:click="delete({{ $u->id }})" wire:confirm="Hapus pengguna {{ $u->name }}?"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                      <x-icon name="trash" class="w-3.5 h-3.5" /> Hapus
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">Tidak ada pengguna ditemukan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($users->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>
