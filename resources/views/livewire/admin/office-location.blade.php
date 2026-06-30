<div class="space-y-4">
  <x-page-header title="Lokasi Kantor" description="Kelola titik lokasi & radius geofencing untuk absensi WFO.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, true); $wire.open()" class="btn-primary">Tambah Lokasi</button>
    </x-slot:action>
  </x-page-header>

  <x-modal show="showForm" max-width="2xl" :title="$editingId ? 'Ubah Lokasi' : 'Tambah Lokasi'">
    <form wire:submit="save" class="space-y-4">
      <div>
        <label class="label">Nama Lokasi</label>
        <input wire:model="name" class="input" placeholder="Masukkan nama lokasi">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label">Alamat</label>
        <input wire:model="address" class="input" placeholder="Masukkan alamat lengkap">
        @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Latitude</label>
          <input wire:model="latitude" class="input font-mono" placeholder="-6.2088">
          @error('latitude') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="label">Longitude</label>
          <input wire:model="longitude" class="input font-mono" placeholder="106.8456">
          @error('longitude') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
      <div>
        <label class="label">Radius (meter)</label>
        <input type="number" wire:model="radius_meters" class="input" min="10" max="5000" placeholder="Masukkan radius (meter)">
        <p class="text-[11px] text-slate-500 mt-1">Jarak maksimum dari titik koordinat yang masih dianggap WFO.</p>
        @error('radius_meters') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Helper: use current browser GPS --}}
      <div x-data="{ loading: false }" class="flex items-center gap-2">
        <button type="button"
          @click="
            loading = true;
            navigator.geolocation.getCurrentPosition(
              pos => {
                $wire.latitude = pos.coords.latitude.toFixed(7);
                $wire.longitude = pos.coords.longitude.toFixed(7);
                loading = false;
              },
              () => { alert('Gagal mendapatkan lokasi.'); loading = false; },
              { enableHighAccuracy: true }
            )
          "
          class="text-xs flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition"
          :disabled="loading">
          <x-icon name="map-pin" class="w-3.5 h-3.5" />
          <span x-show="!loading">Gunakan Lokasi Saya Sekarang</span>
          <span x-show="loading">Mendapatkan lokasi…</span>
        </button>
        <span class="text-[11px] text-slate-400">Klik saat berada di lokasi kantor</span>
      </div>

      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" wire:model="is_active" class="rounded">
        <span class="text-sm text-slate-700">Lokasi aktif</span>
      </label>

      <x-form-actions />
    </form>
  </x-modal>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Lokasi</th>
            <th class="px-5 py-3 whitespace-nowrap">Koordinat</th>
            <th class="px-5 py-3 whitespace-nowrap">Radius</th>
            <th class="px-5 py-3 whitespace-nowrap">Status</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($locations as $loc)
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 whitespace-nowrap">
                <p class="font-medium text-slate-900">{{ $loc->name }}</p>
                @if ($loc->address)
                  <p class="text-xs text-slate-500">{{ $loc->address }}</p>
                @endif
              </td>
              <td class="px-5 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                {{ number_format($loc->latitude, 6) }}, {{ number_format($loc->longitude, 6) }}
              </td>
              <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $loc->radius_meters }} m</td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($loc->is_active)
                  <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                @else
                  <span class="badge bg-slate-100 text-slate-500">Nonaktif</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                  <button type="button" @click="$wire.set('showForm', true, true); $wire.open({{ $loc->id }})"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    Edit
                  </button>
                  <button wire:click="toggleActive({{ $loc->id }})"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $loc->is_active ? 'bg-slate-50 text-slate-600 hover:bg-slate-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition">
                    {{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  <button wire:click="delete({{ $loc->id }})" wire:confirm="Hapus lokasi ini?"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                Belum ada lokasi kantor. Tambahkan lokasi untuk mengaktifkan geofencing absensi WFO.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($locations->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $locations->links() }}</div>
    @endif
  </div>

  @if ($locations->isNotEmpty())
    <div class="card p-4 bg-blue-50 border border-blue-100 text-sm text-blue-800">
      <p class="font-medium mb-1">Cara penggunaan geofencing</p>
      <p>Pada jadwal kerja karyawan, atur <strong>Work Type</strong> ke <strong>WFO</strong>. Sistem akan otomatis memvalidasi GPS karyawan saat absensi - check-in hanya diizinkan jika berada dalam radius salah satu lokasi aktif di atas.</p>
    </div>
  @endif
</div>
