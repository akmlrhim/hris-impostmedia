<div>
  {{-- Header --}}
  <x-mobile-header title="Cuti & Izin" subtitle="Pengajuan cuti, izin sakit, dan izin" :back="route('mobile.home')">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.openForm()" class="hero-action">
        <x-icon name="plus" class="w-4 h-4" />
        Ajukan
      </button>
    </x-slot:action>
  </x-mobile-header>

  {{-- Form modal --}}
  <x-modal show="showForm" max-width="md" title="Ajukan Cuti / Izin">
    <form wire:submit="requestConfirm" class="space-y-4">

      {{-- Type selector --}}
      <div x-data="{ tp: @entangle('type') }">
        <label class="label">Jenis Pengajuan <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-3 gap-2 mt-1">
          @foreach (\App\Enums\LeaveType::cases() as $lt)
            <label wire:key="type-{{ $lt->value }}"
              @click="tp = '{{ $lt->value }}'"
              :class="tp === '{{ $lt->value }}'
                ? 'border-accent-500 bg-accent-50'
                : 'border-slate-200 hover:border-slate-300'"
              class="relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all duration-150">
              <input type="radio" x-model="tp" value="{{ $lt->value }}" class="sr-only">
              <div :class="tp === '{{ $lt->value }}' ? 'bg-accent-100 text-accent-600' : 'bg-slate-100 text-slate-500'"
                class="w-9 h-9 rounded-full flex items-center justify-center transition-colors duration-150">
                <x-icon :name="$lt->icon()" class="w-5 h-5" />
              </div>
              <span :class="tp === '{{ $lt->value }}' ? 'text-accent-700' : 'text-slate-600'"
                class="text-xs font-medium text-center leading-tight transition-colors duration-150">
                {{ $lt->label() }}
              </span>
            </label>
          @endforeach
        </div>
        <div class="min-h-[18px] mt-1">
          @error('type') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Datetime range --}}
      <div class="space-y-3">
        <div>
          <label class="label">Mulai <span class="text-red-500">*</span></label>
          <input type="datetime-local" wire:model="start_date" class="input">
          <div class="min-h-[18px] mt-1">
            @error('start_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>
        <div>
          <label class="label">Selesai <span class="text-red-500">*</span></label>
          <input type="datetime-local" wire:model="end_date" class="input">
          <div class="min-h-[18px] mt-1">
            @error('end_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>

      {{-- Reason --}}
      <div>
        <label class="label">Keterangan <span class="text-red-500">*</span></label>
        <textarea wire:model="reason" rows="3" class="input"
          placeholder="Jelaskan alasan pengajuan Anda (min. 10 karakter)"></textarea>
        <div class="min-h-[18px] mt-1">
          @error('reason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" @click="$wire.set('showForm', false, true)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-accent py-2.5">Lanjutkan</button>
      </div>
    </form>
  </x-modal>

  {{-- Confirmation modal --}}
  <x-modal show="showConfirm" max-width="sm" title="Konfirmasi Pengajuan">
    @php
      $leaveType = \App\Enums\LeaveType::tryFrom($type);
      $startDt = $start_date ? \Carbon\Carbon::parse($start_date) : null;
      $endDt = $end_date ? \Carbon\Carbon::parse($end_date) : null;
    @endphp
    <div class="space-y-4">
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-sm">
        <div class="flex items-center gap-3">
          @if ($leaveType)
            <div class="w-10 h-10 rounded-full bg-{{ $leaveType->color() }}-100 text-{{ $leaveType->color() }}-600 flex items-center justify-center shrink-0">
              <x-icon :name="$leaveType->icon()" class="w-5 h-5" />
            </div>
          @endif
          <div>
            <p class="font-semibold text-slate-900">{{ $leaveType?->label() }}</p>
            <p class="text-xs text-slate-500 mt-0.5">
              @if ($startDt && $endDt)
                {{ $startDt->translatedFormat('d M Y H:i') }} - {{ $endDt->translatedFormat('d M Y H:i') }}
              @endif
            </p>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-200">
          <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Keterangan</p>
          <p class="text-slate-700 leading-relaxed">{{ $reason }}</p>
        </div>
      </div>

      <p class="text-xs text-slate-500 text-center">
        Pengajuan akan menunggu persetujuan dari HR. Pastikan data sudah benar.
      </p>

      <div class="flex gap-2">
        <button type="button" @click="$wire.set('showConfirm', false, true)" class="btn-secondary flex-1">
          Ubah Data
        </button>
        <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-accent flex-1 py-2.5">
          <span wire:loading.remove wire:target="submit">Ya, Kirim</span>
          <span wire:loading wire:target="submit">Mengirim…</span>
        </button>
      </div>
    </div>
  </x-modal>

  {{-- Content --}}
  <div class="px-4 -mt-10 pb-32 space-y-4">

    {{-- Filters --}}
    <x-request-filters :statuses="$statuses" :total="$requests->count()">
      <x-slot:leading>
        <select wire:model.live="typeFilter" class="chip-select">
          <option value="">Semua Jenis</option>
          @foreach ($types as $lt)
            <option value="{{ $lt->value }}">{{ $lt->label() }}</option>
          @endforeach
        </select>
      </x-slot:leading>
    </x-request-filters>

    {{-- Request history --}}
    <div class="space-y-4 transition-opacity" wire:loading.class="opacity-40"
      wire:target="statusFilter,typeFilter">
      @forelse ($requests as $req)
      @php
        $statusColor = $req->status->color();
        $typeColor = $req->type->color();
        $days = (int) $req->start_date->copy()->startOfDay()->diffInDays($req->end_date->copy()->startOfDay()) + 1;
      @endphp
      <div class="card-float p-4" wire:key="req-{{ $req->id }}">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-2.5 min-w-0 flex-1">
            <div
              class="w-9 h-9 rounded-full bg-{{ $typeColor }}-100 text-{{ $typeColor }}-600 flex items-center justify-center shrink-0">
              <x-icon :name="$req->type->icon()" class="w-4 h-4" />
            </div>
            <div class="min-w-0">
              <p class="font-bold text-navy-800">{{ $req->type->label() }}</p>
              <p class="text-xs text-navy-400 mt-0.5">{{ $days }} hari</p>
              <p class="text-xs text-navy-400">
                Untuk: {{ $req->start_date->translatedFormat('d M') }} -
                {{ $req->end_date->translatedFormat('d M Y') }}
              </p>
            </div>
          </div>
          <div class="text-right shrink-0">
            <p class="text-[11px] text-navy-400">{{ $req->created_at->translatedFormat('d M') }}</p>
            <span class="badge mt-1.5 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
              {{ $req->status->label() }}
            </span>
          </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed mt-2.5">{{ $req->reason }}</p>

        @if ($req->rejection_reason)
          <p class="text-xs text-red-600 italic mt-2 pt-2 border-t border-slate-100">
            Ditolak: {{ $req->rejection_reason }}
          </p>
        @endif

        @if ($req->status === \App\Enums\LeaveStatus::Pending)
          <button wire:click="cancel({{ $req->id }})" wire:confirm="Batalkan pengajuan ini?"
            class="mt-2.5 text-xs text-red-500 hover:text-red-700 font-semibold">
            Batalkan pengajuan
          </button>
        @endif
      </div>
    @empty
      <div class="card-float p-8 text-center space-y-2">
        <x-icon name="calendar" class="w-10 h-10 text-slate-300 mx-auto" />
        <p class="text-sm text-slate-500">
          {{ $statusFilter || $typeFilter ? 'Tidak ada pengajuan pada filter ini.' : 'Belum ada pengajuan cuti atau izin.' }}
        </p>
        <p class="text-xs text-slate-400">
          {{ $statusFilter || $typeFilter ? 'Coba ubah filter di atas.' : 'Tap "Ajukan" untuk membuat pengajuan baru.' }}
        </p>
      </div>
      @endforelse
    </div>

  </div>
</div>
