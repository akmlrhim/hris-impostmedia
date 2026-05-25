<div>
  {{-- Header --}}
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <div class="flex-1">
      <h1 class="text-lg font-bold text-slate-900">Pengajuan WFA</h1>
      <p class="text-xs text-slate-400 mt-0.5">Kerja dari luar kantor</p>
    </div>
    @if ($isWfo)
      <button type="button" @click="$wire.set('showForm', true, true); $wire.openForm()" class="btn-primary text-sm px-3 py-1.5">Ajukan</button>
    @endif
  </div>

  {{-- Form modal (hanya untuk WFO) --}}
  @if ($isWfo)
    <x-modal show="showForm" max-width="md" title="Pengajuan WFA">
      <form wire:submit="requestConfirm" class="space-y-4">

        {{-- Work type selector --}}
        <div x-data="{ wt: @entangle('work_type') }">
          <label class="label">Jenis <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-2 mt-1">
            @foreach ($requestableTypes as $wtype)
              <label wire:key="wt-{{ $wtype->value }}"
                @click="wt = '{{ $wtype->value }}'"
                :class="wt === '{{ $wtype->value }}'
                  ? 'border-brand-500 bg-brand-50'
                  : 'border-slate-200 hover:border-slate-300'"
                class="relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all duration-150">
                <input type="radio" x-model="wt" value="{{ $wtype->value }}" class="sr-only">
                <div :class="wt === '{{ $wtype->value }}' ? 'bg-brand-100 text-brand-600' : 'bg-slate-100 text-slate-500'"
                  class="w-9 h-9 rounded-full flex items-center justify-center transition-colors duration-150">
                  <x-icon :name="$wtype->icon()" class="w-5 h-5" />
                </div>
                <span :class="wt === '{{ $wtype->value }}' ? 'text-brand-700' : 'text-slate-600'"
                  class="text-xs font-medium text-center leading-tight transition-colors duration-150">
                  {{ $wtype->shortLabel() }}
                </span>
              </label>
            @endforeach
          </div>
          <div class="min-h-[18px] mt-1">
            @error('work_type') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-800 text-xs flex items-start gap-2">
          <x-icon name="info" class="w-4 h-4 shrink-0 mt-0.5" />
          <span>Jika disetujui, Anda tidak perlu berada di radius kantor saat absen pada rentang waktu tersebut.</span>
        </div>

        {{-- Datetime range --}}
        <div class="space-y-3">
          <div>
            <label class="label">Mulai <span class="text-red-500">*</span></label>
            <input type="datetime-local" onclick="this.showPicker()" wire:model="start_date" class="input">
            <div class="min-h-[18px] mt-1">
              @error('start_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
          <div>
            <label class="label">Selesai <span class="text-red-500">*</span></label>
            <input type="datetime-local" onclick="this.showPicker()" wire:model="end_date" class="input">
            <div class="min-h-[18px] mt-1">
              @error('end_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <div>
          <label class="label">Alasan <span class="text-red-500">*</span></label>
          <textarea wire:model="reason" rows="3" class="input"
            placeholder="Masukkan alasan pengajuan (min. 10 karakter)"></textarea>
          <div class="min-h-[18px] mt-1">
            @error('reason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
          <button type="button" @click="$wire.set('showForm', false, true)" class="btn-secondary">Batal</button>
          <button type="submit" class="btn-primary">Lanjutkan</button>
        </div>
      </form>
    </x-modal>

    {{-- Confirmation modal --}}
    <x-modal show="showConfirm" max-width="sm" title="Konfirmasi Pengajuan">
      @php
        $selectedType = \App\Enums\WorkType::tryFrom($work_type);
        $startDt = $start_date ? \Carbon\Carbon::parse($start_date) : null;
        $endDt = $end_date ? \Carbon\Carbon::parse($end_date) : null;
      @endphp
      <div class="space-y-4">
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-sm">
          <div class="flex items-center gap-3">
            @if ($selectedType)
              <div class="w-10 h-10 rounded-full bg-{{ $selectedType->color() }}-100 text-{{ $selectedType->color() }}-600 flex items-center justify-center shrink-0">
                <x-icon :name="$selectedType->icon()" class="w-5 h-5" />
              </div>
            @endif
            <div>
              <p class="font-semibold text-slate-900">{{ $selectedType?->label() }}</p>
              <p class="text-xs text-slate-500 mt-0.5">
                @if ($startDt && $endDt)
                  {{ $startDt->translatedFormat('d M Y H:i') }} – {{ $endDt->translatedFormat('d M Y H:i') }}
                @endif
              </p>
            </div>
          </div>
          <div class="pt-3 border-t border-slate-200">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Alasan</p>
            <p class="text-slate-700 leading-relaxed">{{ $reason }}</p>
          </div>
        </div>

        <p class="text-xs text-slate-500 text-center">
          Pengajuan akan menunggu persetujuan HR/Admin. Pastikan data sudah benar.
        </p>

        <div class="flex gap-2">
          <button type="button" @click="$wire.set('showConfirm', false, true)" class="btn-secondary flex-1">
            Ubah Data
          </button>
          <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-primary flex-1">
            <span wire:loading.remove wire:target="submit">Ya, Kirim</span>
            <span wire:loading wire:target="submit">Mengirim…</span>
          </button>
        </div>
      </div>
    </x-modal>
  @endif

  <div class="px-5 pt-4 pb-32 space-y-4">

    @if (! $isWfo)
      <div class="card p-6 text-center space-y-3 mt-2">
        <div class="w-14 h-14 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center mx-auto">
          <x-icon name="laptop" class="w-7 h-7" />
        </div>
        <div>
          <p class="font-semibold text-slate-900">Fitur ini khusus karyawan WFO</p>
          <p class="text-sm text-slate-500 mt-1">
            Tipe kerja Anda sudah <strong>{{ auth()->user()?->employee?->work_type?->label() }}</strong> -
            tidak perlu pengajuan untuk absen dari luar kantor.
          </p>
        </div>
      </div>

    @else

      @if ($todayApproved)
        @php $todayType = $todayApproved->work_type; @endphp
        <div class="card p-4 bg-{{ $todayType->color() }}-50 border-{{ $todayType->color() }}-100 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-{{ $todayType->color() }}-100 text-{{ $todayType->color() }}-600 flex items-center justify-center shrink-0">
            <x-icon name="check-circle" class="w-5 h-5" />
          </div>
          <div>
            <p class="text-sm font-semibold text-{{ $todayType->color() }}-900">{{ $todayType->shortLabel() }} aktif hari ini</p>
            <p class="text-xs text-{{ $todayType->color() }}-700 mt-0.5">Berlaku s/d {{ $todayApproved->end_date->translatedFormat('d M Y H:i') }}</p>
          </div>
        </div>
      @endif

      @forelse ($requests as $req)
        @php $color = $req->status->color(); $typeColor = $req->work_type->color(); @endphp
        <div class="card p-4 space-y-2">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="badge bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700 text-xs">{{ $req->work_type->shortLabel() }}</span>
                <span class="badge bg-{{ $color }}-100 text-{{ $color }}-700 text-xs">
                  {{ $req->status->label() }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1.5">
                {{ $req->start_date->translatedFormat('d M Y H:i') }}
                – {{ $req->end_date->translatedFormat('d M Y H:i') }}
              </p>
            </div>
            @if ($req->status === \App\Enums\RemoteWorkStatus::Pending)
              <button wire:click="cancel({{ $req->id }})" wire:confirm="Batalkan pengajuan ini?"
                class="text-xs text-red-500 hover:text-red-700 font-medium shrink-0">
                Batalkan
              </button>
            @endif
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">{{ $req->reason }}</p>

          @if ($req->rejection_reason)
            <div class="pt-2 border-t border-slate-100">
              <p class="text-xs text-red-600 italic">Ditolak: {{ $req->rejection_reason }}</p>
            </div>
          @endif

          <p class="text-[10px] text-slate-400">Diajukan {{ $req->created_at->diffForHumans() }}</p>
        </div>
      @empty
        <div class="card p-8 text-center space-y-2">
          <x-icon name="laptop" class="w-10 h-10 text-slate-300 mx-auto" />
          <p class="text-sm text-slate-500">Belum ada pengajuan.</p>
          <p class="text-xs text-slate-400">Tap "Ajukan" jika ingin bekerja dari luar kantor.</p>
        </div>
      @endforelse

    @endif

  </div>
</div>
