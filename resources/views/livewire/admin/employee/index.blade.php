<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Daftar Karyawan</h2>
            <p class="text-sm text-slate-500">Kelola data seluruh karyawan.</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> Tambah Karyawan
        </a>
    </div>

	{{-- Filter bar --}}
	<div class="card p-4 flex flex-col md:flex-row gap-3 items-stretch md:items-center">
		<div class="flex-1 relative">
			<x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
			<input wire:model.live.debounce.300ms="search" type="text"
			       placeholder="Cari nama atau NIK karyawan…"
			       class="input pl-9">
		</div>

		<select wire:model.live="position" class="input md:w-52">
			<option value="">Semua Posisi</option>
			@foreach ($positions as $p)
				<option value="{{ $p->id }}">{{ $p->name }}</option>
			@endforeach
		</select>

		<select wire:model.live="status" class="input md:w-44">
			<option value="">Semua Status</option>
			@foreach (\App\Enums\EmploymentStatus::cases() as $s)
				<option value="{{ $s->value }}">{{ $s->label() }}</option>
			@endforeach
		</select>
	</div>

	{{-- Table --}}
	<div class="card overflow-hidden">
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-slate-200">
				<thead class="bg-slate-50">
					<tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
						<th class="px-5 py-3">Karyawan</th>
						<th class="px-5 py-3">NIK</th>
						<th class="px-5 py-3">Posisi</th>
						<th class="px-5 py-3">Status</th>
						<th class="px-5 py-3 text-right">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100 text-sm">
					@forelse ($employees as $emp)
						<tr class="hover:bg-slate-50">
							<td class="px-5 py-3">
								<div class="flex items-center gap-3">
									<div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-slate-600">
										{{ substr($emp->full_name, 0, 1) }}
									</div>
									<div>
										<p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
										<p class="text-xs text-slate-500">{{ $emp->position?->name ?? '—' }}</p>
									</div>
								</div>
							</td>
							<td class="px-5 py-3 text-slate-600">{{ $emp->employee_number }}</td>
							<td class="px-5 py-3 text-slate-600">{{ $emp->position?->name ?? '—' }}</td>
							<td class="px-5 py-3">
								<span class="badge bg-{{ $emp->employment_status?->color() }}-100 text-{{ $emp->employment_status?->color() }}-700">
									{{ $emp->employment_status?->label() }}
								</span>
							</td>
							<td class="px-5 py-3 text-right whitespace-nowrap">
								<a href="{{ route('admin.employees.show', $emp->id) }}" class="text-brand-600 hover:underline text-sm mr-3">Detail</a>
								<a href="{{ route('admin.employees.edit', $emp->id) }}" class="text-slate-600 hover:underline text-sm">Edit</a>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="6" class="px-5 py-12 text-center text-slate-500">
								Tidak ada data karyawan.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="px-5 py-3 border-t border-slate-200">
			{{ $employees->links() }}
		</div>
	</div>
</div>
