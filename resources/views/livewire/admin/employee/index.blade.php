<div class="space-y-4">
  <x-page-header title="Daftar Karyawan" description="Kelola data seluruh karyawan.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, true); $wire.open()" class="btn-primary">Tambah Karyawan</button>
    </x-slot:action>
  </x-page-header>

  {{-- Filter bar --}}
  <div class="card p-4 flex flex-col md:flex-row gap-3 items-stretch md:items-center">
    <div class="flex-1 relative">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIK karyawan…"
        class="input pl-9">
    </div>

    <select wire:model.live="status" class="input md:w-44">
      <option value="">Semua Status</option>
      @foreach (\App\Enums\EmploymentStatus::cases() as $s)
        <option value="{{ $s->value }}">{{ $s->label() }}</option>
      @endforeach
    </select>
  </div>

  {{-- Form modal --}}
  @include('livewire.admin.employee.partials.form-modal')

  {{-- Table --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-x divide-slate-200 border-l border-r border-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Karyawan</th>
            <th class="px-5 py-3">NIK</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($employees as $emp)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-slate-600">
                    {{ substr($emp->full_name, 0, 1) }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $emp->employee_number }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 text-slate-600">{{ $emp->employee_number }}</td>
              <td class="px-5 py-3">
                <span
                  class="badge bg-{{ $emp->employment_status?->color() }}-100 text-{{ $emp->employment_status?->color() }}-700">
                  {{ $emp->employment_status?->label() }}
                </span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a wire:navigate href="{{ route('admin.employees.show', $emp) }}"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    Detail
                  </a>
                  <button type="button" @click="$wire.set('showForm', true, true); $wire.open({{ $emp->id }})"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
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
              <td colspan="6" class="px-5 py-12 text-center text-slate-500">Tidak ada data karyawan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-5 py-3 border-t border-slate-200">{{ $employees->links() }}</div>
  </div>
</div>
