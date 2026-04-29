<div>
	<div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3">
		<a href="{{ route('mobile.leave') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
			<x-icon name="arrow-left" class="w-5 h-5" />
		</a>
		<h1 class="text-xl font-bold text-slate-900">Ajukan Cuti</h1>
	</div>

	<form wire:submit="submit" class="p-5 space-y-4">
		<div>
			<label class="label">Jenis Cuti</label>
			<select wire:model="leave_type_id" class="input">
				<option value="">— Pilih jenis cuti —</option>
				@foreach ($leaveTypes as $t)
					<option value="{{ $t->id }}">{{ $t->name }}</option>
				@endforeach
			</select>
			@error('leave_type_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
		</div>

		<div class="grid grid-cols-2 gap-3">
			<div>
				<label class="label">Mulai</label>
				<input type="date" wire:model="start_date" class="input" min="{{ now()->toDateString() }}">
				@error('start_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
			</div>
			<div>
				<label class="label">Selesai</label>
				<input type="date" wire:model="end_date" class="input" min="{{ now()->toDateString() }}">
				@error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
			</div>
		</div>

		<label class="flex items-center gap-2 text-sm text-slate-700">
			<input type="checkbox" wire:model="is_half_day" class="rounded border-slate-300">
			Setengah hari
		</label>

		<div>
			<label class="label">Alasan</label>
			<textarea wire:model="reason" rows="4" class="input" placeholder="Tuliskan alasan cuti…"></textarea>
			@error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
		</div>

		<button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
			<span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
			<span wire:loading wire:target="submit">Mengirim…</span>
		</button>
	</form>
</div>
