<div>
	<x-mobile-header title="Slip Gaji" subtitle="Riwayat penerimaan gaji" :back="route('mobile.home')" />

	<div class="px-4 -mt-10 pb-32">
		@if ($payslips->isEmpty())
			<div class="card-float p-8 text-center text-sm text-navy-400">
				Belum ada slip gaji.
			</div>
		@else
			<div class="space-y-3">
				@foreach ($payslips as $p)
					<a wire:navigate href="{{ route('mobile.payslip.show', $p) }}"
					   class="card-float flex items-center justify-between gap-3 p-4 active:scale-[0.99] transition">
						<div class="w-11 h-11 rounded-2xl bg-accent-50 text-accent-500 flex items-center justify-center shrink-0">
							<x-icon name="wallet" class="w-5 h-5" />
						</div>
						<div class="flex-1 min-w-0">
							<p class="font-bold text-navy-800">
								{{ \Carbon\Carbon::create()->month($p->period->month)->translatedFormat('F') }} {{ $p->period->year }}
							</p>
							<p class="text-xs text-navy-400 mt-0.5">
								Dibayar: {{ $p->period->payment_date?->translatedFormat('d M Y') ?? '-' }}
							</p>
							<p class="text-base font-bold text-emerald-600 mt-1">
								{{ rupiah($p->net_salary) }}
							</p>
						</div>
						<x-icon name="chevron-right" class="w-5 h-5 text-slate-300 shrink-0" />
					</a>
				@endforeach
			</div>
		@endif
	</div>
</div>
