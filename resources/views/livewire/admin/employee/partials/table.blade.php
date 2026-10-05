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
          <tr class="hover:bg-slate-50" wire:key="emp-{{ $emp->id }}">
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
            <td class="px-5 py-3 text-right whitespace-nowrap">
              <x-action-menu>
                <a wire:navigate href="{{ route('admin.employees.show', $emp) }}"
                  class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <x-icon name="eye" class="w-3.5 h-3.5 text-blue-500" /> Detail
                </a>
                <button type="button" @click="open = false; $wire.set('showForm', true, false); $wire.open({{ $emp->id }})"
                  class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <x-icon name="pencil" class="w-3.5 h-3.5 text-amber-500" /> Edit
                </button>
                <button wire:click="toggleActive({{ $emp->id }})"
                  wire:confirm="{{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }} karyawan {{ $emp->full_name }}?" @click="open = false"
                  class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <x-icon name="{{ $emp->is_active ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5 text-slate-400" />
                  {{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </x-action-menu>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-5 py-12 text-center text-slate-500">{{ $emptyMessage }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="px-5 py-3 border-t border-slate-200">{{ $employees->links() }}</div>
</div>
