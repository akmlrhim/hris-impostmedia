<div>
  {{-- Header --}}
  <x-mobile-header title="Pengajuan Lembur" subtitle="Kerja di luar jam kerja normal" :back="route('mobile.home')">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.openForm()" class="hero-action">
        <x-icon name="plus" class="w-4 h-4" />
        Ajukan
      </button>
    </x-slot:action>
  </x-mobile-header>

  {{-- Form pengajuan --}}
  <x-modal show="showForm" max-width="md" title="Pengajuan Lembur">
    <form wire:submit="requestConfirm" class="space-y-4">

      <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-800 text-xs flex items-start gap-2">
        <x-icon name="info" class="w-4 h-4 shrink-0 mt-0.5" />
        <span>Pengajuan diverifikasi HR. Lampirkan bukti approve dari head Anda agar dapat diproses.</span>
      </div>

      {{-- Tanggal + rentang jam --}}
      <div x-data="{
        date: @entangle('overtime_date'),
        start: @entangle('start_time'),
        end: @entangle('end_time'),
        get minutes() {
          if (!this.start || !this.end) return 0;
          const [sh, sm] = this.start.split(':').map(Number);
          const [eh, em] = this.end.split(':').map(Number);
          let diff = (eh * 60 + em) - (sh * 60 + sm);
          if (diff <= 0) diff += 1440;
          return diff;
        },
        get overnight() { return !!this.start && !!this.end && this.end <= this.start },
        get durationLabel() {
          const m = this.minutes;
          if (!m) return '';
          const h = Math.floor(m / 60), r = m % 60;
          if (!h) return r + 'm';
          return r ? h + 'j ' + r + 'm' : h + 'j';
        },
      }" class="space-y-3">

        <div>
          <label class="label">Tanggal Lembur <span class="text-red-500">*</span></label>
          <input type="date" wire:model="overtime_date" class="input">
          <div class="min-h-[18px] mt-1">
            @error('overtime_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Jam Mulai <span class="text-red-500">*</span></label>
            <input type="time" wire:model="start_time" step="300" class="input">
            <div class="min-h-[18px] mt-1">
              @error('start_time') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
          <div>
            <label class="label">Jam Selesai <span class="text-red-500">*</span></label>
            <input type="time" wire:model="end_time" step="300" class="input">
            <div class="min-h-[18px] mt-1">
              @error('end_time') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        {{-- Ringkasan durasi, langsung update tanpa roundtrip --}}
        <div x-show="minutes > 0" x-cloak
          class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-indigo-50 border border-indigo-100">
          <x-icon name="clock" class="w-4 h-4 text-indigo-600 shrink-0" />
          <p class="text-xs text-indigo-800">
            Total lembur <span class="font-semibold" x-text="durationLabel"></span>
            <span x-show="overnight" x-cloak class="text-indigo-600">· selesai keesokan hari</span>
          </p>
        </div>
      </div>

      <div>
        <label class="label">Alasan Lembur <span class="text-red-500">*</span></label>
        <textarea wire:model="reason" rows="3" class="input"
          placeholder="Masukkan alasan lembur (min. 10 karakter)"></textarea>
        <div class="min-h-[18px] mt-1">
          @error('reason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Bukti approve head --}}
      <div>
        <label class="label">Bukti Approve Head <span class="text-red-500">*</span></label>
        <input type="file" wire:model="head_approval" accept="image/*,application/pdf"
          class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-accent-50 file:text-accent-700 hover:file:bg-accent-100">
        <p class="text-[10px] text-slate-400 mt-1">Screenshot chat / email approval. JPG, PNG, WEBP, atau PDF. Maks 4 MB.</p>
        <div wire:loading wire:target="head_approval" class="text-xs text-slate-500 mt-1">Mengunggah...</div>
        @if ($head_approval)
          <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
            <x-icon name="check-circle" class="w-3.5 h-3.5" />
            {{ $head_approval->getClientOriginalName() }}
          </p>
        @endif
        <div class="min-h-[18px] mt-1">
          @error('head_approval') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Dokumentasi kerja (opsional saat pengajuan) --}}
      <div>
        <label class="label">Dokumentasi Kerja</label>
        <textarea wire:model="work_documentation" rows="3" class="input"
          placeholder="Rincian pekerjaan yang dikerjakan (bisa diisi/diubah nanti)"></textarea>
        <p class="text-[10px] text-slate-400 mt-1">Opsional. Tetap bisa diedit setelah pengajuan disetujui.</p>
        <div class="min-h-[18px] mt-1">
          @error('work_documentation') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
        <button type="button" @click="$wire.set('showForm', false, true)" class="btn-secondary">Batal</button>
        <button type="submit" wire:loading.attr="disabled" wire:target="head_approval" class="btn-accent py-2.5">Lanjutkan</button>
      </div>
    </form>
  </x-modal>

  {{-- Konfirmasi --}}
  <x-modal show="showConfirm" max-width="sm" title="Konfirmasi Pengajuan">
    <div class="space-y-4">
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-sm">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
            <x-icon name="clock" class="w-5 h-5" />
          </div>
          <div>
            @if ($previewStart && $previewEnd)
              @php $totalMinutes = (int) $previewStart->diffInMinutes($previewEnd); @endphp
              <p class="font-semibold text-slate-900">
                {{ intdiv($totalMinutes, 60) }}j{{ $totalMinutes % 60 ? ' ' . $totalMinutes % 60 . 'm' : '' }} lembur
              </p>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ $previewStart->translatedFormat('d M Y H:i') }} - {{ $previewEnd->translatedFormat('H:i') }}
                @if (! $previewStart->isSameDay($previewEnd))
                  <span class="text-indigo-600">(keesokan hari)</span>
                @endif
              </p>
            @endif
          </div>
        </div>
        <div class="pt-3 border-t border-slate-200">
          <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Alasan</p>
          <p class="text-slate-700 leading-relaxed">{{ $reason }}</p>
        </div>
        @if ($head_approval)
          <div class="pt-3 border-t border-slate-200">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Bukti Approve</p>
            <p class="text-slate-700 text-xs">{{ $head_approval->getClientOriginalName() }}</p>
          </div>
        @endif
      </div>

      <p class="text-xs text-slate-500 text-center">
        Pengajuan akan menunggu persetujuan HR. Pastikan data sudah benar.
      </p>

      <div class="flex gap-2">
        <button type="button" @click="$wire.set('showConfirm', false, true)" class="btn-secondary flex-1">
          Ubah Data
        </button>
        <button type="button" wire:click="submit" wire:loading.attr="disabled" class="btn-accent flex-1 py-2.5">
          <span wire:loading.remove wire:target="submit">Ya, Kirim</span>
          <span wire:loading wire:target="submit">Mengirim...</span>
        </button>
      </div>
    </div>
  </x-modal>

  {{-- Edit dokumentasi kerja --}}
  <x-modal show="showDocForm" max-width="md" title="Dokumentasi Kerja">
    <form wire:submit="saveDocumentation" class="space-y-4">
      <div>
        <label class="label">Rincian pekerjaan <span class="text-red-500">*</span></label>
        <textarea wire:model="work_documentation" rows="6" class="input"
          placeholder="Tuliskan apa saja yang dikerjakan selama lembur"></textarea>
        <div class="min-h-[18px] mt-1">
          @error('work_documentation') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>
      <x-form-actions show="showDocForm" submit="Simpan" />
    </form>
  </x-modal>

  <div class="px-4 -mt-10 pb-32 space-y-4">
    {{-- Filters --}}
    <x-request-filters :statuses="$statuses" :total="$requests->count()" />

    <div class="space-y-4 transition-opacity" wire:loading.class="opacity-40" wire:target="statusFilter">
      @forelse ($requests as $req)
      @php $color = $req->status->color(); @endphp
      <div class="card-float p-4" wire:key="ot-{{ $req->id }}">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-2.5 min-w-0 flex-1">
            <div class="w-9 h-9 rounded-full bg-accent-100 text-accent-600 flex items-center justify-center shrink-0">
              <x-icon name="clock" class="w-4 h-4" />
            </div>
            <div class="min-w-0">
              <p class="font-bold text-navy-800">Lembur {{ $req->durationLabel() }}</p>
              <p class="text-xs text-navy-400 mt-0.5">{{ $req->started_at->translatedFormat('l, d M Y') }}</p>
              <p class="text-xs text-navy-400">
                {{ $req->started_at->translatedFormat('H:i') }} – {{ $req->ended_at->translatedFormat('H:i') }}
                @if (! $req->started_at->isSameDay($req->ended_at))
                  (+1 hari)
                @endif
              </p>
            </div>
          </div>
          <div class="text-right shrink-0">
            <p class="text-[11px] text-navy-400">{{ $req->created_at->translatedFormat('d M') }}</p>
            <span class="badge mt-1.5 bg-{{ $color }}-100 text-{{ $color }}-700">{{ $req->status->label() }}</span>
          </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed mt-2.5">{{ $req->reason }}</p>

        @if ($req->head_approval_path)
          <a href="{{ route('files.overtime-approval', $req) }}" target="_blank"
            class="inline-flex items-center gap-1.5 mt-2 text-xs text-accent-600 hover:text-accent-700 font-medium">
            <x-icon name="external-link" class="w-3.5 h-3.5" />
            Lihat bukti approve head
          </a>
        @endif

        {{-- Dokumentasi kerja: tetap bisa diedit setelah disetujui --}}
        <div class="mt-2.5 pt-2.5 border-t border-slate-100 space-y-1.5">
          <div class="flex items-center justify-between gap-2">
            <p class="text-[10px] text-navy-400 font-semibold uppercase tracking-wide">Dokumentasi Kerja</p>
            @if ($req->documentationIsEditable())
              <button type="button" @click="$wire.set('showDocForm', true, false); $wire.openDocForm({{ $req->id }})"
                class="text-xs text-accent-600 hover:text-accent-700 font-semibold">
                {{ $req->work_documentation ? 'Edit' : 'Isi' }}
              </button>
            @endif
          </div>
          @if ($req->work_documentation)
            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $req->work_documentation }}</p>
          @else
            <p class="text-xs text-slate-400 italic">Belum diisi.</p>
          @endif
        </div>

        @if ($req->rejection_reason)
          <p class="text-xs text-red-600 italic mt-2 pt-2 border-t border-slate-100">
            Ditolak: {{ $req->rejection_reason }}
          </p>
        @endif

        @if ($req->status === \App\Enums\OvertimeStatus::Pending)
          <button wire:click="cancel({{ $req->id }})" wire:confirm="Batalkan pengajuan lembur ini?"
            class="mt-2.5 text-xs text-red-500 hover:text-red-700 font-semibold">
            Batalkan pengajuan
          </button>
        @endif
      </div>
    @empty
      <div class="card-float p-8 text-center space-y-2">
        <x-icon name="clock" class="w-10 h-10 text-slate-300 mx-auto" />
        <p class="text-sm text-slate-500">
          {{ $statusFilter ? 'Tidak ada pengajuan pada filter ini.' : 'Belum ada pengajuan lembur.' }}
        </p>
        <p class="text-xs text-slate-400">
          {{ $statusFilter ? 'Coba ubah filter di atas.' : 'Tap "Ajukan" untuk mengajukan lembur.' }}
        </p>
      </div>
      @endforelse
    </div>
  </div>
</div>
