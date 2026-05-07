<div x-data="{
    latitude: @entangle('latitude').live,
    longitude: @entangle('longitude').live,
    address: @entangle('address').live,
    loading: false,
    getLocation() {
        this.loading = true;
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung geolocation.');
            this.loading = false;
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.latitude = pos.coords.latitude;
                this.longitude = pos.coords.longitude;
                this.address = `${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}`;
                this.loading = false;
            },
            (err) => {
                alert('Gagal mengambil lokasi: ' + err.message);
                this.loading = false;
            }, { enableHighAccuracy: true, timeout: 10000 }
        );
    }
}" x-init="getLocation()">
  <div class="px-5 pt-6">
    <div class="flex items-center gap-3 mb-4">
      <a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
        <x-icon name="arrow-left" class="w-5 h-5" />
      </a>
      <h1 class="text-xl font-bold text-slate-900">Absensi</h1>
    </div>

    {{-- Current time --}}
    <div class="card p-6 text-center">
      <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
      <p x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)" x-text="now.toLocaleTimeString('id-ID')"
        class="text-4xl font-bold text-slate-900 mt-2"></p>
    </div>

    {{-- Location --}}
    <div class="card p-4 mt-4 flex items-start gap-3">
      <div class="w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
        <x-icon name="map-pin" class="w-5 h-5" />
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs text-slate-500">Lokasi Saat Ini</p>
        <p class="text-sm text-slate-900 truncate" x-text="address || 'Mendapatkan lokasi…'"></p>
      </div>
      <button @click="getLocation()" class="text-xs text-brand-600 font-medium" :disabled="loading">
        <span x-show="!loading">Refresh</span>
        <span x-show="loading">…</span>
      </button>
    </div>

    {{-- Action buttons --}}
    @if (!$attendance?->check_in_at)
      <button wire:click="checkIn" :disabled="!latitude || loading"
        class="w-full mt-5 bg-emerald-600 text-white rounded-xl py-4 font-semibold flex items-center justify-center gap-2 disabled:opacity-50">
        <x-icon name="check" class="w-5 h-5" />
        Check-in
      </button>
    @elseif (!$attendance->check_out_at)
      <div class="mt-4 p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm flex items-center gap-2">
        <x-icon name="check" class="w-4 h-4" /> Check-in sukses pukul {{ $attendance->check_in_at->format('H:i') }}
      </div>
      <button wire:click="checkOut" :disabled="!latitude || loading"
        class="w-full mt-3 bg-rose-600 text-white rounded-xl py-4 font-semibold flex items-center justify-center gap-2 disabled:opacity-50">
        <x-icon name="log-out" class="w-5 h-5" />
        Check-out
      </button>
    @else
      <div class="mt-4 p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm space-y-1">
        <p>✓ Check-in: {{ $attendance->check_in_at->format('H:i') }}</p>
        <p>✓ Check-out: {{ $attendance->check_out_at->format('H:i') }}</p>
        <p class="font-medium">Total kerja: {{ floor($attendance->work_minutes / 60) }}j
          {{ $attendance->work_minutes % 60 }}m</p>
      </div>
    @endif
  </div>

  {{-- History --}}
  <div class="px-5 mt-6 mb-4">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Riwayat 10 Hari Terakhir</h3>
    <div class="space-y-2">
      @forelse ($history as $h)
        <div class="card p-3 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-900">{{ $h->attendance_date->translatedFormat('d M Y') }}</p>
            <p class="text-xs text-slate-500">
              {{ $h->check_in_at?->format('H:i') ?? '—' }} → {{ $h->check_out_at?->format('H:i') ?? '—' }}
            </p>
          </div>
          <span class="badge bg-{{ $h->status?->color() }}-100 text-{{ $h->status?->color() }}-700">
            {{ $h->status?->label() }}
          </span>
        </div>
      @empty
        <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat.</p>
      @endforelse
    </div>
  </div>
</div>
