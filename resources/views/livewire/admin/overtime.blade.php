<div class="space-y-4">
  <x-page-header title="Pengajuan Lembur" description="Verifikasi pengajuan lembur karyawan beserta bukti approve dari head.">
    @if ($pendingCount > 0)
      <x-slot:action>
        <span class="badge bg-amber-100 text-amber-700 text-xs">{{ $pendingCount }} menunggu persetujuan</span>
      </x-slot:action>
    @endif
  </x-page-header>

  {{-- Modal Tolak --}}
  <x-modal show="showRejectForm" max-width="sm" title="Alasan Penolakan">
    <form wire:submit="reject" class="space-y-4">
      <div>
        <label class="label">Alasan penolakan <span class="text-red-500">*</span></label>
        <textarea wire:model="rejectionReason" rows="3" class="input" placeholder="Masukkan alasan penolakan"></textarea>
        @error('rejectionReason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <x-form-actions show="showRejectForm" submit="Tolak Pengajuan" variant="danger" />
    </form>
  </x-modal>

  {{-- Modal Detail --}}
  <x-modal show="showDetail" max-width="lg" title="Detail Pengajuan Lembur">
    @if ($viewing)
      <div class="space-y-4 text-sm">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
            <x-icon name="clock" class="w-5 h-5" />
          </div>
          <div>
            <p class="font-semibold text-slate-900">{{ $viewing->employee?->full_name }}</p>
            <p class="text-xs text-slate-500">
              {{ $viewing->started_at->translatedFormat('d M Y H:i') }} -
              {{ $viewing->ended_at->translatedFormat('H:i') }} · {{ $viewing->durationLabel() }}
            </p>
          </div>
        </div>

        <div>
          <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Alasan</p>
          <p class="text-slate-700 leading-relaxed">{{ $viewing->reason }}</p>
        </div>

        <div>
          <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Bukti Approve Head</p>
          @if ($viewing->head_approval_path)
            <a href="{{ route('files.overtime-approval', $viewing) }}" target="_blank"
              class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
              <x-icon name="external-link" class="w-4 h-4" />
              Buka lampiran
            </a>
          @else
            <p class="text-slate-400 italic text-xs">Tidak ada lampiran.</p>
          @endif
        </div>

        <div>
          <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Dokumentasi Kerja</p>
          @if ($viewing->work_documentation)
            <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $viewing->work_documentation }}</p>
          @else
            <p class="text-slate-400 italic text-xs">Belum diisi karyawan.</p>
          @endif
        </div>

        @if ($viewing->rejection_reason)
          <div class="p-3 rounded-lg bg-red-50 border border-red-100">
            <p class="text-xs text-red-700">Ditolak: {{ $viewing->rejection_reason }}</p>
          </div>
        @endif
      </div>
    @endif
  </x-modal>

  {{-- Filter --}}
  <div class="flex flex-wrap gap-2">
    @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', '' => 'Semua'] as $val => $label)
      <button wire:click="$set('filterStatus', '{{ $val }}')"
        class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $filterStatus === $val ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
        {{ $label }}
      </button>
    @endforeach
  </div>

  {{-- Tabel --}}
  <div class="card-table">
    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none" wire:target="filterStatus">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Karyawan</th>
            <th class="px-5 py-3 whitespace-nowrap">Waktu</th>
            <th class="px-5 py-3 whitespace-nowrap">Durasi</th>
            <th class="px-5 py-3 whitespace-nowrap">Alasan</th>
            <th class="px-5 py-3 whitespace-nowrap">Bukti</th>
            <th class="px-5 py-3 whitespace-nowrap">Status</th>
            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($requests as $req)
            <tr class="hover:bg-slate-50" wire:key="row-{{ $req->id }}">
              <td class="px-5 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center font-semibold text-slate-600 shrink-0">
                    @if ($req->employee->avatar_path)
                      <img src="{{ route('files.avatar', $req->employee) }}" class="w-full h-full object-cover">
                    @else
                      {{ strtoupper(substr($req->employee->full_name, 0, 1)) }}
                    @endif
                  </div>
                  <div class="whitespace-nowrap">
                    <p class="font-medium text-slate-900">{{ $req->employee->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $req->employee->employee_number }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 text-slate-600 text-xs whitespace-nowrap">
                {{ $req->started_at->translatedFormat('d M Y H:i') }}
                <br>s/d {{ $req->ended_at->translatedFormat('d M Y H:i') }}
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                <span class="badge bg-indigo-100 text-indigo-700">{{ $req->durationLabel() }}</span>
              </td>
              <td class="px-5 py-3 max-w-xs">
                <p class="text-slate-600 text-xs line-clamp-2">{{ $req->reason }}</p>
                @if ($req->rejection_reason)
                  <p class="text-red-600 text-xs mt-1 italic">Ditolak: {{ $req->rejection_reason }}</p>
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($req->head_approval_path)
                  <a href="{{ route('files.overtime-approval', $req) }}" target="_blank"
                    class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium">
                    <x-icon name="external-link" class="w-3.5 h-3.5" />
                    Lihat
                  </a>
                @else
                  <span class="text-xs text-slate-400">-</span>
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @php $color = $req->status->color(); @endphp
                <span class="badge bg-{{ $color }}-100 text-{{ $color }}-700">{{ $req->status->label() }}</span>
              </td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <x-action-menu>
                  <button type="button" @click="open = false; $wire.set('showDetail', true, true); $wire.openDetail({{ $req->id }})"
                    class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <x-icon name="eye" class="w-3.5 h-3.5 text-blue-500" /> Detail
                  </button>
                  @if ($req->status === \App\Enums\OvertimeStatus::Pending)
                    <button wire:click="approve({{ $req->id }})" @click="open = false"
                      class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-emerald-50 flex items-center gap-2">
                      <x-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-500" /> Setujui
                    </button>
                    <button type="button" @click="open = false; $wire.set('showRejectForm', true, true); $wire.openRejectForm({{ $req->id }})"
                      class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-red-50 flex items-center gap-2">
                      <x-icon name="x-circle" class="w-3.5 h-3.5 text-red-500" /> Tolak
                    </button>
                  @endif
                </x-action-menu>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-slate-500">Tidak ada pengajuan lembur.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($requests->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $requests->links() }}</div>
    @endif
  </div>
</div>
