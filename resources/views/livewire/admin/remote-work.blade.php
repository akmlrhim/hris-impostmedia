<div class="space-y-4">
  <x-page-header title="Pengajuan Kerja Remote" description="Kelola pengajuan WFA / WFH / WFC dari karyawan.">
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

  {{-- Filter --}}
  <div class="flex gap-2">
    @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', '' => 'Semua'] as $val => $label)
      <button wire:click="$set('filterStatus', '{{ $val }}')"
        class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $filterStatus === $val ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
        {{ $label }}
      </button>
    @endforeach
  </div>

  {{-- Tabel --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Karyawan</th>
            <th class="px-5 py-3">Jenis</th>
            <th class="px-5 py-3">Waktu</th>
            <th class="px-5 py-3">Alasan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($requests as $req)
            @php $typeColor = $req->work_type?->color() ?? 'slate'; @endphp
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3">
                <p class="font-medium text-slate-900">{{ $req->employee->full_name }}</p>
                <p class="text-xs text-slate-500">{{ $req->employee->employee_number }}</p>
              </td>
              <td class="px-5 py-3">
                <span class="badge bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                  {{ $req->work_type?->shortLabel() ?? '-' }}
                </span>
              </td>
              <td class="px-5 py-3 text-slate-600 text-xs">
                {{ $req->start_date->translatedFormat('d M Y H:i') }}
                <br>s/d {{ $req->end_date->translatedFormat('d M Y H:i') }}
              </td>
              <td class="px-5 py-3 max-w-xs">
                <p class="text-slate-600 text-xs line-clamp-2">{{ $req->reason }}</p>
                @if ($req->rejection_reason)
                  <p class="text-red-600 text-xs mt-1 italic">Ditolak: {{ $req->rejection_reason }}</p>
                @endif
              </td>
              <td class="px-5 py-3">
                @php $color = $req->status->color(); @endphp
                <span class="badge bg-{{ $color }}-100 text-{{ $color }}-700">{{ $req->status->label() }}</span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-1.5">
                  @if ($req->status === \App\Enums\RemoteWorkStatus::Pending)
                    <button wire:click="approve({{ $req->id }})"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                      Setujui
                    </button>
                    <button type="button" @click="$wire.set('showRejectForm', true, true); $wire.openRejectForm({{ $req->id }})"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                      Tolak
                    </button>
                  @else
                    <span class="text-xs text-slate-400">
                      {{ $req->reviewer?->name ?? '-' }} · {{ $req->reviewed_at?->diffForHumans() }}
                    </span>
                  @endif
                  @if ($req->status === \App\Enums\RemoteWorkStatus::Pending)
                    <button wire:click="delete({{ $req->id }})" wire:confirm="Hapus pengajuan WFA ini?"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                      Hapus
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-12 text-center text-slate-500">Tidak ada pengajuan.</td>
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
