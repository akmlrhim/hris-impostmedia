<div class="space-y-4">
  <x-page-header title="Daftar Karyawan" description="Kelola data seluruh karyawan.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.open()" class="btn-primary">Tambah
        Karyawan</button>
    </x-slot:action>
  </x-page-header>

  {{-- Filter bar --}}
  <div class="card p-4 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
    <div class="flex-1 relative">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, nomor karyawan, atau NIK…"
        class="input pl-9">
    </div>

    <select wire:model.live="work_type_filter" class="input md:w-44">
      <option value="">Semua Tipe Kerja</option>
      @foreach (\App\Enums\WorkType::cases() as $w)
        <option value="{{ $w->value }}">{{ $w->label() }}</option>
      @endforeach
    </select>

    <select wire:model.live="active_filter" class="input md:w-40">
      <option value="">Semua Status</option>
      <option value="1">Aktif</option>
      <option value="0">Nonaktif</option>
    </select>
  </div>

  {{-- Form modal --}}
  @include('livewire.admin.employee.partials.form-modal')

  {{-- Table --}}
  <div class="card-table">
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Karyawan</th>
            <th class="px-5 py-3 whitespace-nowrap">NIK</th>
            <th class="px-5 py-3 whitespace-nowrap">Tipe Kerja</th>
            <th class="px-5 py-3 whitespace-nowrap">Status</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($employees as $emp)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center font-semibold text-slate-600 shrink-0">
                    @if ($emp->avatar_path)
                      <img src="{{ route('files.avatar', $emp) }}" class="w-full h-full object-cover">
                    @else
                      {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                    @endif
                  </div>
                  <div class="whitespace-nowrap">
                    <p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $emp->employee_number }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $emp->nik ?: '-' }}</td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($emp->work_type)
                  <span class="badge bg-{{ $emp->work_type->color() }}-100 text-{{ $emp->work_type->color() }}-700">
                    {{ $emp->work_type->shortLabel() }}
                  </span>
                @else
                  <span class="text-slate-400">-</span>
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($emp->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                @else
                  <span class="badge bg-red-100 text-red-700">Nonaktif</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                  <a wire:navigate href="{{ route('admin.employees.show', $emp) }}"
                    class="px-2 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    Detail
                  </a>
                  <button type="button" @click="$wire.set('showForm', true, false); $wire.open({{ $emp->id }})"
                    class="px-2 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    Edit
                  </button>
                  <button wire:click="toggleActive({{ $emp->id }})"
                    wire:confirm="{{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }} karyawan {{ $emp->full_name }}?"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $emp->is_active ? 'bg-slate-50 text-slate-600 hover:bg-slate-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition">
                    {{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">Tidak ada data karyawan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-5 py-3 border-t border-slate-200">{{ $employees->links() }}</div>
  </div>
</div>
