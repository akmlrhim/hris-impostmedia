<div class="space-y-4">
  <x-page-header title="Manajemen Pengguna" description="Kelola akun pengguna dan peran sistem.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.open()" class="btn-primary">Tambah Pengguna</button>
    </x-slot:action>
  </x-page-header>

  {{-- Filters --}}
  <div class="flex flex-wrap gap-3">
    <div class="relative flex-1 min-w-48">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce="search" placeholder="Cari nama atau email…" class="input pl-9">
    </div>
    <select wire:model.live="filterRole" class="input w-44">
      <option value="">Semua Peran</option>
      @foreach ($allRoles as $r)
        <option value="{{ $r->value }}">{{ $r->label() }}</option>
      @endforeach
    </select>
  </div>

  @include('livewire.admin.partials.user-form-modal')
  @include('livewire.admin.partials.user-link-modal')
  @include('livewire.admin.partials.user-password-modal')

  {{-- Table --}}
  <div class="card-table">
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Pengguna</th>
            <th class="px-5 py-3 whitespace-nowrap">Peran</th>
            <th class="px-5 py-3 whitespace-nowrap">Data Karyawan</th>
            <th class="px-5 py-3 whitespace-nowrap">Status</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($users as $u)
            <tr class="hover:bg-slate-50 {{ !$u->is_active ? 'opacity-60' : '' }}">
              <td class="px-5 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-semibold shrink-0">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                  </div>
                  <div class="whitespace-nowrap">
                    <p class="font-medium text-slate-900">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @php
                  $roleColors = [
                    'admin'    => 'bg-blue-100 text-blue-700',
                    'hr'       => 'bg-emerald-100 text-emerald-700',
                    'employee' => 'bg-slate-100 text-slate-600',
                  ];
                @endphp
                <div class="flex flex-wrap gap-1">
                  @forelse ($u->getRoleObjects() as $r)
                    <span class="badge {{ $roleColors[$r->value] ?? 'bg-slate-100 text-slate-600' }}">{{ $r->label() }}</span>
                  @empty
                    <span class="text-xs text-slate-400">-</span>
                  @endforelse
                </div>
              </td>
              <td class="px-5 py-3">
                @if ($u->employee)
                  <div class="flex items-center gap-2 whitespace-nowrap">
                    <a wire:navigate href="{{ route('admin.employees.show', $u->employee) }}"
                      class="text-brand-600 hover:underline text-xs font-medium">
                      {{ $u->employee->employee_number }} - {{ $u->employee->full_name }}
                    </a>
                    <button wire:click="unlinkEmployee({{ $u->id }})"
                      wire:confirm="Lepas koneksi akun {{ $u->name }} dari karyawan {{ $u->employee->full_name }}?"
                      class="shrink-0 px-2 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                      Lepas
                    </button>
                  </div>
                @else
                  <div class="flex items-center gap-2 whitespace-nowrap">
                    <span class="text-xs text-slate-400 italic">Belum terhubung</span>
                    <button type="button" @click="$wire.set('showLinkForm', true, true); $wire.openLinkForm({{ $u->id }})"
                      class="shrink-0 px-2 py-0.5 rounded-md text-[11px] font-medium bg-brand-50 text-brand-600 hover:bg-brand-100 transition">
                      Hubungkan
                    </button>
                  </div>
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($u->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                @else
                  <span class="badge bg-slate-100 text-slate-500">Nonaktif</span>
                @endif
              </td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <x-action-menu>
                  <button type="button" @click="open = false; $wire.set('showForm', true, false); $wire.open({{ $u->id }})"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <x-icon name="pencil" class="w-3.5 h-3.5 text-amber-500" /> Edit
                  </button>
                  <button type="button" @click="open = false; $wire.set('showPasswordForm', true, true); $wire.openPasswordForm({{ $u->id }})"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <x-icon name="lock" class="w-3.5 h-3.5 text-slate-400" /> Sandi
                  </button>
                  <button wire:click="toggleActive({{ $u->id }})" @click="open = false"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <x-icon name="{{ $u->is_active ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5 text-slate-400" />
                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  @if ($u->id !== auth()->id())
                    <button wire:click="delete({{ $u->id }})" wire:confirm="Hapus pengguna {{ $u->name }}?" @click="open = false"
                      class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
                      <x-icon name="trash" class="w-3.5 h-3.5" /> Hapus
                    </button>
                  @endif
                </x-action-menu>
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
