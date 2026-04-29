<div>
	<div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200">
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-3">
				<a href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
					<x-icon name="arrow-left" class="w-5 h-5" />
				</a>
				<h1 class="text-xl font-bold text-slate-900">Cuti & Izin</h1>
			</div>
			<a href="{{ route('mobile.leave.create') }}" class="btn-primary text-xs px-3 py-1.5">
				<x-icon name="plus" class="w-3.5 h-3.5" /> Ajukan
			</a>
		</div>
	</div>

	{{-- Balances --}}
	<div class="px-5 mt-5">
		<h3 class="text-sm font-semibold text-slate-900 mb-3">Saldo Cuti {{ now()->year }}</h3>
		<div class="grid grid-cols-2 gap-3">
			@forelse ($balances as $b)
				<div class="card p-4">
					<div class="w-3 h-3 rounded-full mb-2" style="background-color: {{ $b->leaveType->color }};"></div>
					<p class="text-xs text-slate-500">{{ $b->leaveType->name }}</p>
					<p class="text-lg font-bold text-slate-900 mt-1">
						{{ rtrim(rtrim(number_format($b->remaining_days, 1), '0'), '.') }}
						<span class="text-xs font-normal text-slate-500">/ {{ rtrim(rtrim(number_format($b->quota_days, 1), '0'), '.') }} hari</span>
					</p>
				</div>
			@empty
				<div class="col-span-2 card p-6 text-center text-sm text-slate-500">
					Saldo cuti belum diset.
				</div>
			@endforelse
		</div>
	</div>

	{{-- Requests --}}
	<div class="px-5 mt-6">
		<h3 class="text-sm font-semibold text-slate-900 mb-3">Riwayat Pengajuan</h3>
		<div class="space-y-2">
			@forelse ($requests as $r)
				<div class="card p-4">
					<div class="flex items-start justify-between">
						<div>
							<p class="text-sm font-semibold text-slate-900">{{ $r->leaveType->name }}</p>
							<p class="text-xs text-slate-500 mt-0.5">
								{{ $r->start_date->format('d M') }} – {{ $r->end_date->format('d M Y') }}
								· {{ $r->total_days }} hari
							</p>
							@if ($r->reason)
								<p class="text-xs text-slate-600 mt-2 line-clamp-2">{{ $r->reason }}</p>
							@endif
						</div>
						<span class="badge bg-{{ $r->status?->color() }}-100 text-{{ $r->status?->color() }}-700 shrink-0">
							{{ $r->status?->label() }}
						</span>
					</div>
				</div>
			@empty
				<div class="card p-6 text-center text-sm text-slate-500">
					Belum ada pengajuan.
				</div>
			@endforelse
		</div>
	</div>
</div>
