<div class="space-y-4">
  <x-page-header title="Log Aktivitas" description="Riwayat tindakan sensitif: hapus, finalisasi, perubahan akses." />

  {{-- Filter --}}
  <div class="card p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="relative">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari deskripsi..."
        class="input pl-9">
    </div>

    <select wire:model.live="action" class="input">
      <option value="">Semua Aksi</option>
      @foreach ($availableActions as $a)
        <option value="{{ $a }}">{{ $a }}</option>
      @endforeach
    </select>

    <select wire:model.live="userId" class="input">
      <option value="">Semua Pengguna</option>
      @foreach ($availableUsers as $u)
        <option value="{{ $u->id }}">{{ $u->name }}</option>
      @endforeach
    </select>
  </div>

  {{-- Table --}}
  <div class="card-table">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
      <span>Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri</span>
    </div>
    <div class="overflow-x-auto">
      <table class="table-grid">
        <thead class="bg-slate-50">
          <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <th class="px-5 py-3 whitespace-nowrap">Waktu</th>
            <th class="px-5 py-3 whitespace-nowrap">Pengguna</th>
            <th class="px-5 py-3 whitespace-nowrap">Aksi</th>
            <th class="px-5 py-3 whitespace-nowrap">Deskripsi</th>
            <th class="px-5 py-3 whitespace-nowrap">IP</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          @forelse ($logs as $log)
            <tr class="hover:bg-slate-50 align-top">
              <td class="px-5 py-3 whitespace-nowrap">
                <p class="font-medium text-slate-900 text-xs">{{ $log->created_at->translatedFormat('d M Y') }}</p>
                <p class="text-[11px] text-slate-500">{{ $log->created_at->format('H:i') }}</p>
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                @if ($log->user)
                  <p class="font-medium text-slate-900">{{ $log->user->name }}</p>
                  <p class="text-xs text-slate-500">{{ $log->user->email }}</p>
                @else
                  <span class="text-xs italic text-slate-400">(pengguna dihapus)</span>
                @endif
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                <span class="badge bg-slate-100 text-slate-700 font-mono text-xs">{{ $log->action }}</span>
              </td>
              <td class="px-5 py-3 text-slate-700 min-w-[200px]">
                <p class="text-sm">{{ $log->description }}</p>
                @if (!empty($log->properties))
                  <details class="mt-1">
                    <summary class="text-xs text-slate-400 cursor-pointer hover:text-slate-600">Properti</summary>
                    <pre class="text-[11px] bg-slate-50 border border-slate-200 rounded p-2 mt-1 overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                  </details>
                @endif
              </td>
              <td class="px-5 py-3 text-xs text-slate-500 font-mono whitespace-nowrap">{{ $log->ip_address ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada aktivitas tercatat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($logs->hasPages())
      <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
    @endif
  </div>
</div>
