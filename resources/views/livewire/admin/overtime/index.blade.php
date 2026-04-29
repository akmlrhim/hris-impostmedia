<div class="space-y-4">
    <div class="card p-4">
        <select wire:model.live="status" class="input md:w-52">
            <option value="">Semua Status</option>
            @foreach (\App\Enums\RequestStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Total Jam</th>
                        <th class="px-5 py-3">Alasan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($overtimes as $ot)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900">{{ $ot->employee->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $ot->employee->position?->name }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $ot->overtime_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">{{ \Illuminate\Support\Str::of($ot->start_time)->limit(5, '') }} – {{ \Illuminate\Support\Str::of($ot->end_time)->limit(5, '') }}</td>
                            <td class="px-5 py-3">{{ $ot->total_hours }} jam</td>
                            <td class="px-5 py-3 max-w-xs truncate">{{ $ot->reason }}</td>
                            <td class="px-5 py-3">
                                <span class="badge bg-{{ $ot->status?->color() }}-100 text-{{ $ot->status?->color() }}-700">
                                    {{ $ot->status?->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if ($ot->status === \App\Enums\RequestStatus::Pending)
                                    <button wire:click="approve({{ $ot->id }})" wire:confirm="Setujui lembur ini?"
                                            class="text-emerald-600 hover:underline text-sm mr-3">Setujui</button>
                                    <button wire:click="reject({{ $ot->id }})" wire:confirm="Tolak lembur ini?"
                                            class="text-red-600 hover:underline text-sm">Tolak</button>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
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
        <div class="px-5 py-3 border-t border-slate-200">{{ $overtimes->links() }}</div>
    </div>
</div>
