<div>
  {{-- Header --}}
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <div class="flex-1">
      <h1 class="text-lg font-bold text-slate-900">Cuti & Izin</h1>
      <p class="text-xs text-slate-400 mt-0.5">Pengajuan cuti, izin sakit, dan izin</p>
    </div>
    <button wire:click="openForm" class="btn-primary text-sm px-3 py-1.5">Ajukan</button>
  </div>

  {{-- Form modal --}}
  <x-modal show="showForm" max-width="md" title="Ajukan Cuti / Izin">
    <form wire:submit="requestConfirm" class="space-y-4">

      {{-- Type selector --}}
      <div>
        <label class="label">Jenis Pengajuan <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-3 gap-2 mt-1">
          @foreach (\App\Enums\LeaveType::cases() as $lt)
            <label wire:key="type-{{ $lt->value }}"
              class="relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition
                {{ $type === $lt->value
                    ? 'border-brand-500 bg-brand-50'
                    : 'border-slate-200 hover:border-slate-300' }}">
              <input type="radio" wire:model="type" value="{{ $lt->value }}" class="sr-only">
              <div class="w-9 h-9 rounded-full flex items-center justify-center
                {{ $type === $lt->value ? 'bg-brand-100 text-brand-600' : 'bg-slate-100 text-slate-500' }}">
                <x-icon :name="$lt->icon()" class="w-5 h-5" />
              </div>
              <span class="text-xs font-medium text-center leading-tight
                {{ $type === $lt->value ? 'text-brand-700' : 'text-slate-600' }}">
                {{ $lt->label() }}
              </span>
            </label>
          @endforeach
        </div>
        @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Date range --}}
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Tanggal Mulai <span class="text-red-500">*</span></label>
          <input type="date" onclick="this.showPicker()" wire:model="start_date" class="input">
          @error('start_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="label">Tanggal Selesai <span class="text-red-500">*</span></label>
          <input type="date" onclick="this.showPicker()" wire:model="end_date" class="input">
          @error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Reason --}}
      <div>
        <label class="label">Keterangan <span class="text-red-500">*</span></label>
        <textarea wire:model="reason" rows="3" class="input"
          placeholder="Jelaskan alasan pengajuan Anda (min. 10 karakter)"></textarea>
        @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary">Lanjutkan</button>
      </div>
    </form>
  </x-modal>

  {{-- Confirmation modal --}}
  <x-modal show="showConfirm" max-width="sm" title="Konfirmasi Pengajuan">
    <div class="space-y-4">
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-sm">
        @php $leaveType = \App\Enums\LeaveType::tryFrom($type); @endphp

        <div class="flex items-center gap-3">
          @if ($leaveType)
            <div class="w-10 h-10 rounded-full bg-{{ $leaveType->color() }}-100 text-{{ $leaveType->color() }}-600 flex items-center justify-center shrink-0">
              <x-icon :name="$leaveType->icon()" class="w-5 h-5" />
            </div>
          @endif
          <div>
            <p class="font-semibold text-slate-900">{{ $leaveType?->label() }}</p>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') }}
              @if ($start_date !== $end_date)
                — {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') }}
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
        <button type="button" wire:click="$set('showConfirm', false)" class="btn-secondary flex-1">
          Ubah Data
        </button>
        <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-primary flex-1">
          <span wire:loading.remove wire:target="submit">Ya, Kirim</span>
          <span wire:loading wire:target="submit">Mengirim…</span>
        </button>
      </div>
    </div>
  </x-modal>

  {{-- Content --}}
  <div class="px-5 pt-4 pb-32 space-y-4">

    {{-- Type legend --}}
    <div class="grid grid-cols-3 gap-2">
      @foreach (\App\Enums\LeaveType::cases() as $lt)
        <div class="card p-3 flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-{{ $lt->color() }}-100 text-{{ $lt->color() }}-600 flex items-center justify-center shrink-0">
            <x-icon :name="$lt->icon()" class="w-4 h-4" />
          </div>
          <span class="text-xs font-medium text-slate-700">{{ $lt->label() }}</span>
        </div>
      @endforeach
    </div>

    {{-- Request history --}}
    @forelse ($requests as $req)
      @php
        $statusColor = $req->status->color();
        $typeColor   = $req->type->color();
      @endphp
      <div class="card p-4 space-y-2" wire:key="req-{{ $req->id }}">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-{{ $typeColor }}-100 text-{{ $typeColor }}-600 flex items-center justify-center shrink-0">
              <x-icon :name="$req->type->icon()" class="w-4 h-4" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="badge bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700 text-xs">
                  {{ $req->type->label() }}
                </span>
                <span class="badge bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 text-xs">
                  {{ $req->status->label() }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1">
                {{ $req->start_date->translatedFormat('d M Y') }}
                @if (! $req->start_date->eq($req->end_date))
                  — {{ $req->end_date->translatedFormat('d M Y') }}
                @endif
              </p>
            </div>
          </div>
          @if ($req->status === \App\Enums\LeaveStatus::Pending)
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
        <x-icon name="calendar" class="w-10 h-10 text-slate-300 mx-auto" />
        <p class="text-sm text-slate-500">Belum ada pengajuan cuti atau izin.</p>
        <p class="text-xs text-slate-400">Tap "Ajukan" untuk membuat pengajuan baru.</p>
      </div>
    @endforelse

  </div>
</div>
