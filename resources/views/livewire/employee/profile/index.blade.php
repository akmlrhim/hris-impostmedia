<div>
	{{-- Hero --}}
	<div class="hero px-5 pt-8 pb-20">
		<div class="flex flex-col items-center text-center">
			<div class="w-24 h-24 rounded-full bg-white/15 flex items-center justify-center text-3xl font-bold overflow-hidden ring-2 ring-white/25">
				@if ($employee?->avatar_path)
					<img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
				@else
					{{ strtoupper(substr($employee?->full_name ?? auth()->user()->name, 0, 1)) }}
				@endif
			</div>
			<h1 class="text-xl font-bold mt-3">{{ $employee?->full_name ?? auth()->user()->name }}</h1>
			@if ($employee?->position)
				<p class="text-sm font-medium text-white/90 mt-0.5">{{ $employee->position }}</p>
			@endif
			<div class="flex items-center justify-center gap-2 mt-2.5 flex-wrap">
				<span class="badge bg-white/15 text-white">{{ $employee?->work_type?->label() ?? '-' }}</span>
				<span class="badge bg-white/15 text-white">{{ $employee?->employee_number ?? '-' }}</span>
			</div>
		</div>
	</div>

	<div class="px-4 -mt-10 pb-32 space-y-3">
		<div class="card-float divide-y divide-slate-100">
			@php
				$rows = [
					['label' => 'Email', 'value' => auth()->user()->email],
					['label' => 'Jabatan', 'value' => $employee?->position ?? '-'],
					['label' => 'NIK', 'value' => $employee?->nik ?? '-'],
					['label' => 'Tipe Kerja', 'value' => $employee?->work_type?->label() ?? '-'],
					['label' => 'Mulai Kontrak', 'value' => $employee?->contract_start_date?->translatedFormat('d M Y') ?? '-'],
					['label' => 'Telepon', 'value' => $employee?->phone ?? '-'],
				];
			@endphp
			@foreach ($rows as $r)
				<div class="px-4 py-3 flex justify-between gap-3">
					<span class="text-sm text-navy-400">{{ $r['label'] }}</span>
					<span class="text-sm text-navy-800 font-semibold text-right">{{ $r['value'] ?: '-' }}</span>
				</div>
			@endforeach
		</div>

		<a wire:navigate href="{{ route('mobile.profile.edit') }}"
		   class="card-float flex items-center justify-between gap-3 p-4 active:scale-[0.99] transition">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-accent-50 text-accent-500 flex items-center justify-center shrink-0">
					<x-icon name="pencil" class="w-5 h-5" />
				</div>
				<div>
					<p class="text-sm font-bold text-navy-800">Edit Profil</p>
					<p class="text-xs text-navy-400">Data pribadi, biometrik & kata sandi</p>
				</div>
			</div>
			<x-icon name="chevron-right" class="w-4 h-4 text-slate-300 shrink-0" />
		</a>

		@if (auth()->user()?->isAdminPanel())
			<a wire:navigate href="{{ route('admin.dashboard') }}"
			   class="card-float flex items-center justify-between gap-3 p-4 active:scale-[0.99] transition">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-navy-50 text-navy-600 flex items-center justify-center shrink-0">
						<x-icon name="layers" class="w-5 h-5" />
					</div>
					<div>
						<p class="text-sm font-bold text-navy-800">Panel Admin</p>
						<p class="text-xs text-navy-400">Beralih ke tampilan admin</p>
					</div>
				</div>
				<x-icon name="chevron-right" class="w-4 h-4 text-slate-300 shrink-0" />
			</a>
		@endif

		<button wire:click="logout"
		        wire:confirm="Yakin ingin keluar?"
		        class="btn-accent bg-rose-50 text-rose-600 shadow-none hover:bg-rose-100 active:bg-rose-100">
			<x-icon name="log-out" class="w-4 h-4" />
			Keluar
		</button>
	</div>
</div>
