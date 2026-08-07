<div class="space-y-6">
  <x-page-header title="Konfigurasi Hak Akses" description="Tentukan fitur apa yang dapat diakses oleh peran HR.">
    <x-slot:action>
      <button wire:click="save" class="btn-primary">Simpan Perubahan</button>
    </x-slot:action>
  </x-page-header>

  {{-- Role legend --}}
  <div class="flex flex-wrap gap-3 text-xs">
    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-medium">
      <x-icon name="shield-check" class="w-3.5 h-3.5" /> Admin - Semua akses (tidak dapat diubah)
    </div>
    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-medium">
      <x-icon name="sliders" class="w-3.5 h-3.5" /> HR - Dapat dikonfigurasi
    </div>
    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-slate-500 font-medium">
      <x-icon name="lock" class="w-3.5 h-3.5" /> Karyawan - Tidak ada akses panel (hanya mobile)
    </div>
  </div>

  {{-- Matrix Table --}}
  <div class="card-table">
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
              Fitur / Izin
            </th>
            {{-- Admin (always all, locked) --}}
            <th class="px-6 py-3.5 text-center w-32">
              <span class="badge bg-blue-100 text-blue-700">Admin</span>
            </th>
            {{-- HR (configurable) --}}
            <th class="px-6 py-3.5 text-center w-32">
              <span class="badge bg-emerald-100 text-emerald-700">HR</span>
            </th>
            {{-- Employee (always none, locked) --}}
            <th class="px-6 py-3.5 text-center w-32">
              <span class="badge bg-slate-100 text-slate-500">Karyawan</span>
            </th>
          </tr>
        </thead>
        <tbody>
          @foreach ($permissions as $permission)
            <tr class="hover:bg-slate-50/60 transition">
              <td class="px-5 py-4">
                <p class="font-medium text-slate-900 text-sm">{{ $permission->label() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $permission->description() }}</p>
              </td>

              {{-- Admin: always ✓ --}}
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center">
                  <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                    <x-icon name="check" class="w-3.5 h-3.5 text-blue-600" />
                  </div>
                </div>
              </td>

              {{-- HR: toggleable --}}
              <td class="px-6 py-4 text-center">
                <button
                  wire:click="toggle('hr', '{{ $permission->value }}')"
                  type="button"
                  title="{{ ($matrix['hr'][$permission->value] ?? false) ? 'Klik untuk cabut izin' : 'Klik untuk beri izin' }}"
                  class="mx-auto flex items-center justify-center w-6 h-6 rounded-full transition
                    {{ ($matrix['hr'][$permission->value] ?? false)
                        ? 'bg-emerald-100 hover:bg-emerald-200'
                        : 'bg-slate-100 hover:bg-slate-200' }}">
                  @if ($matrix['hr'][$permission->value] ?? false)
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-600" />
                  @else
                    <x-icon name="minus" class="w-3.5 h-3.5 text-slate-400" />
                  @endif
                </button>
              </td>

              {{-- Karyawan: always ✗ --}}
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center">
                  <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                    <x-icon name="minus" class="w-3.5 h-3.5 text-slate-300" />
                  </div>
                </div>
              </td>
            </tr>
          @endforeach

          {{-- manage_users row - Admin only, all others locked off --}}
          <tr class="bg-amber-50/30 hover:bg-amber-50/50 transition">
            <td class="px-5 py-4">
              <p class="font-medium text-slate-900 text-sm">{{ \App\Enums\Permission::ManageUsers->label() }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ \App\Enums\Permission::ManageUsers->description() }}</p>
            </td>
            {{-- Admin --}}
            <td class="px-6 py-4 text-center">
              <div class="flex justify-center">
                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                  <x-icon name="check" class="w-3.5 h-3.5 text-blue-600" />
                </div>
              </div>
            </td>
            {{-- HR: locked off --}}
            <td class="px-6 py-4 text-center">
              <div class="flex justify-center" title="Tidak dapat dikonfigurasi">
                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                  <x-icon name="lock" class="w-3 h-3 text-slate-400" />
                </div>
              </div>
            </td>
            {{-- Karyawan: locked off --}}
            <td class="px-6 py-4 text-center">
              <div class="flex justify-center">
                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                  <x-icon name="minus" class="w-3.5 h-3.5 text-slate-300" />
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex justify-end">
    <button wire:click="save" class="btn-primary">Simpan Perubahan</button>
  </div>
</div>
