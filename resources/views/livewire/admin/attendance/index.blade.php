<div class="space-y-4">
	<div class="card p-4 flex flex-col md:flex-row gap-3">
		<input wire:model.live="date" type="date" class="input md:w-52">

		<select wire:model.live="status" class="input md:w-52">
			<option value="">Semua Status</option>
			@foreach (\App\Enums\AttendanceStatus::cases() as $s)
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
						<th class="px-5 py-3">Shift</th>
						<th class="px-5 py-3">Check-in</th>
						<th class="px-5 py-3">Check-out</th>
						<th class="px-5 py-3">Terlambat</th>
						<th class="px-5 py-3">Status</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100 text-sm">
					@forelse ($attendances as $att)
						<tr class="hover:bg-slate-50">
							<td class="px-5 py-3">
								<p class="font-medium text-slate-900">{{ $att->employee->full_name }}</p>
								<p class="text-xs text-slate-500">{{ $att->employee->position?->name }}</p>
							</td>
							<td class="px-5 py-3">{{ $att->attendance_date->format('d M Y') }}</td>
							<td class="px-5 py-3">{{ $att->shift?->name ?? '—' }}</td>
							<td class="px-5 py-3">{{ $att->check_in_at?->format('H:i') ?? '—' }}</td>
							<td class="px-5 py-3">{{ $att->check_out_at?->format('H:i') ?? '—' }}</td>
							<td class="px-5 py-3">
								{{ $att->late_minutes > 0 ? $att->late_minutes . ' mnt' : '—' }}
							</td>
							<td class="px-5 py-3">
								<span class="badge bg-{{ $att->status?->color() }}-100 text-{{ $att->status?->color() }}-700">
									{{ $att->status?->label() }}
								</span>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="px-5 py-12 text-center text-slate-500">
								Tidak ada data absensi.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
		<div class="px-5 py-3 border-t border-slate-200">
			{{ $attendances->links() }}
		</div>
	</div>
</div>
