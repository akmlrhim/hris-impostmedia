<div>
	<div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3">
		<a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
			<x-icon name="arrow-left" class="w-5 h-5" />
		</a>
		<h1 class="text-xl font-bold text-slate-900">Slip Gaji</h1>
	</div>

	<div class="p-5 space-y-3">
		@forelse ($payslips as $p)
			<a wire:navigate href="{{ route('mobile.payslip.show', $p) }}" class="card p-4 flex items-center justify-between hover:bg-slate-50 transition">
				<div>
					<p class="font-semibold text-slate-900">
						{{ \Carbon\Carbon::create()->month($p->period->month)->translatedFormat('F') }} {{ $p->period->year }}
					</p>
					<p class="text-xs text-slate-500 mt-0.5">
						Dibayar: {{ $p->period->payment_date?->translatedFormat('d M Y') ?? '-' }}
					</p>
					<p class="text-base font-bold text-emerald-600 mt-1">
						{{ rupiah($p->net_salary) }}
					</p>
				</div>
				<x-icon name="chevron-right" class="w-5 h-5 text-brand-600" />
			</a>
		@empty
			<div class="card p-8 text-center text-sm text-slate-500">
				Belum ada slip gaji.
			</div>
		@endforelse
	</div>
</div>
