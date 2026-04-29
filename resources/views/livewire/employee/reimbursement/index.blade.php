<div>
	<div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center justify-between">
		<div class="flex items-center gap-3">
			<a href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
				<x-icon name="arrow-left" class="w-5 h-5" />
			</a>
			<h1 class="text-xl font-bold text-slate-900">Reimbursement</h1>
		</div>
		<a href="{{ route('mobile.reimbursement.create') }}" class="btn-primary text-xs px-3 py-1.5">
			<x-icon name="plus" class="w-3.5 h-3.5" /> Ajukan
		</a>
	</div>

	<div class="p-5 space-y-3">
		@forelse ($reimbursements as $r)
			<div class="card p-4">
				<div class="flex items-start justify-between">
					<div>
						<p class="font-semibold text-slate-900 text-sm">{{ $r->title }}</p>
						<p class="text-xs text-slate-500 mt-0.5">
							{{ $r->category->name }} · {{ $r->expense_date->format('d M Y') }}
						</p>
						<p class="text-base font-bold text-amber-600 mt-1">
							{{ rupiah($r->amount) }}
						</p>
					</div>
					<span class="badge bg-{{ $r->status?->color() }}-100 text-{{ $r->status?->color() }}-700">
						{{ $r->status?->label() }}
					</span>
				</div>
			</div>
		@empty
			<div class="card p-8 text-center text-sm text-slate-500">
				Belum ada pengajuan reimbursement.
			</div>
		@endforelse
	</div>
</div>
