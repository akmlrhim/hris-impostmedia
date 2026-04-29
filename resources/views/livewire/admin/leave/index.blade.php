<div class="space-y-4">
	<div class="card p-4 flex flex-col md:flex-row gap-3">
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
						<th class="px-5 py-3">No.</th>
						<th class="px-5 py-3">Karyawan</th>
						<th class="px-5 py-3">Jenis</th>
						<th class="px-5 py-3">Periode</th>
						<th class="px-5 py-3">Hari</th>
						<th class="px-5 py-3">Status</th>
						<th class="px-5 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100 text-sm">
					@forelse ($requests as $r)
						<tr class="hover:bg-slate-50">
							<td class="px-5 py-3 font-mono text-xs">{{ $r->request_number }}</td>
							<td class="px-5 py-3">
								<p class="font-medium text-slate-900">{{ $r->employee->full_name }}</p>
								<p class="text-xs text-slate-500">{{ $r->employee->position?->name }}</p>
							</td>
							<td class="px-5 py-3">{{ $r->leaveType->name }}</td>
							<td class="px-5 py-3">
								{{ $r->start_date->format('d M') }} – {{ $r->end_date->format('d M Y') }}
							</td>
							<td class="px-5 py-3">{{ $r->total_days }}</td>
							<td class="px-5 py-3">
								<span class="badge bg-{{ $r->status?->color() }}-100 text-{{ $r->status?->color() }}-700">
									{{ $r->status?->label() }}
								</span>
							</td>
							<td class="px-5 py-3 text-right">
								@if ($r->status === \App\Enums\RequestStatus::Pending)
									<button wire:click="approve({{ $r->id }})"
									        wire:confirm="Setujui pengajuan ini?"
									        class="text-emerald-600 hover:underline text-sm mr-3">Setujui</button>
									<button wire:click="reject({{ $r->id }})"
									        wire:confirm="Tolak pengajuan ini?"
									        class="text-red-600 hover:underline text-sm">Tolak</button>
								@else
									<span class="text-xs text-slate-400">—</span>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="px-5 py-12 text-center text-slate-500">
								Tidak ada pengajuan cuti.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
		<div class="px-5 py-3 border-t border-slate-200">
			{{ $requests->links() }}
		</div>
	</div>
</div>
