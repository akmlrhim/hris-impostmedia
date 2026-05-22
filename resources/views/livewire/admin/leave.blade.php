<div class="space-y-4">
  <x-page-header title="Pengajuan Cuti & Izin" description="Kelola pengajuan cuti, izin sakit, dan izin karyawan.">
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

  {{-- Filters --}}
  <div class="flex flex-wrap gap-2">
    <div class="flex gap-1.5">
      @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', '' => 'Semua'] as $val => $label)
        <button wire:click="$set('filterStatus', '{{ $val }}')"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition
            {{ $filterStatus === $val ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          {{ $label }}
        </button>
      @endforeach
    </div>

    <div class="flex gap-1.5 ml-auto">
      @foreach (\App\Enums\LeaveType::cases() as $lt)
        <button wire:click="$set('filterType', '{{ $filterType === $lt->value ? '' : $lt->value }}')"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition
            {{ $filterType === $lt->value
                ? 'bg-' . $lt->color() . '-600 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          {{ $lt->label() }}
        </button>
      @endforeach
    </div>
  </div>

  {{-- Tabel --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3">Karyawan</th>
            <th class="px-5 py-3">Jenis</th>
            <th class="px-5 py-3">Tanggal</th>
            <th class="px-5 py-3">Keterangan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          @forelse ($requests as $req)
            @php
              $statusColor = $req->status->color();
              $typeColor   = $req->type->color();
            @endphp
            <tr class="hover:bg-slate-50" wire:key="row-{{ $req->id }}">
              <td class="px-5 py-3">
                <p class="font-medium text-slate-900">{{ $req->employee->full_name }}</p>
                <p class="text-xs text-slate-500">{{ $req->employee->employee_number }}</p>
              </td>
              <td class="px-5 py-3">
                <span class="badge bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                  {{ $req->type->label() }}
                </span>
              </td>
              <td class="px-5 py-3 text-slate-600 text-xs">
                {{ $req->start_date->translatedFormat('d M Y') }}
                @if (! $req->start_date->eq($req->end_date))
                  <br>s/d {{ $req->end_date->translatedFormat('d M Y') }}
                @endif
              </td>
              <td class="px-5 py-3 max-w-xs">
                <p class="text-slate-600 text-xs line-clamp-2">{{ $req->reason }}</p>
                @if ($req->rejection_reason)
                  <p class="text-red-600 text-xs mt-1 italic">Ditolak: {{ $req->rejection_reason }}</p>
                @endif
              </td>
              <td class="px-5 py-3">
                <span class="badge bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                  {{ $req->status->label() }}
                </span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-1.5">
                  @if ($req->status === \App\Enums\LeaveStatus::Pending)
                    <button wire:click="approve({{ $req->id }})"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                      Setujui
                    </button>
                    <button wire:click="openRejectForm({{ $req->id }})"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                      Tolak
                    </button>
                  @else
                    <span class="text-xs text-slate-400">
                      {{ $req->reviewer?->name ?? '-' }} · {{ $req->reviewed_at?->diffForHumans() }}
                    </span>
                  @endif
                  <button wire:click="delete({{ $req->id }})" wire:confirm="Hapus pengajuan ini?"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    Hapus
                  </button>
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
