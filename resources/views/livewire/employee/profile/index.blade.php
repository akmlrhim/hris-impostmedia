<div>
	<div class="bg-gradient-to-br from-brand-600 to-brand-800 text-white px-5 pt-8 pb-16">
		<div class="flex flex-col items-center text-center">
			<div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold overflow-hidden ring-2 ring-white/30">
				@if ($employee?->avatar_path)
					<img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
				@else
					{{ strtoupper(substr($employee?->full_name ?? auth()->user()->name, 0, 1)) }}
				@endif
			</div>
			<h1 class="text-xl font-bold mt-3">{{ $employee?->full_name ?? auth()->user()->name }}</h1>
			<p class="text-sm text-brand-100">{{ $employee?->employment_status?->label() ?? '-' }}</p>
			<p class="text-xs text-brand-200 mt-0.5">NIK: {{ $employee?->employee_number ?? '-' }}</p>
		</div>
	</div>

	<div class="px-5 -mt-10 pb-32 space-y-3">
		<div class="card divide-y divide-slate-100">
			@php
				$rows = [
					['label' => 'Email', 'value' => auth()->user()->email],
					['label' => 'Status Karyawan', 'value' => $employee?->employment_status?->label() ?? '-'],
					['label' => 'Tanggal Bergabung', 'value' => $employee?->join_date?->translatedFormat('d M Y') ?? '-'],
					['label' => 'Telepon', 'value' => $employee?->phone ?? '-'],
				];
			@endphp
			@foreach ($rows as $r)
				<div class="px-4 py-3 flex justify-between gap-3">
					<span class="text-sm text-slate-500">{{ $r['label'] }}</span>
					<span class="text-sm text-slate-900 font-medium text-right">{{ $r['value'] ?: '-' }}</span>
				</div>
			@endforeach
		</div>

		<a wire:navigate href="{{ route('mobile.profile.edit') }}" class="btn-secondary w-full">
			Edit Profil & Kata Sandi
		</a>

		@if (auth()->user()?->isAdminPanel())
			<a wire:navigate href="{{ route('admin.dashboard') }}"
			   class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-medium bg-brand-50 text-brand-700 border border-brand-100 hover:bg-brand-100 transition">
				<x-icon name="layout-dashboard" class="w-4 h-4" />
				Beralih ke Panel Admin
			</a>
		@endif

		<button wire:click="logout"
		        wire:confirm="Yakin ingin keluar?"
		        class="btn-danger w-full">
			Keluar
		</button>
	</div>
</div>
