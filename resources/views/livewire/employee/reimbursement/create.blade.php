<div>
    <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3">
        <a href="{{ route('mobile.reimbursement') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
            <x-icon name="arrow-left" class="w-5 h-5" />
        </a>
        <h1 class="text-xl font-bold text-slate-900">Ajukan Klaim</h1>
    </div>

    <form wire:submit="submit" class="p-5 space-y-4">
        <div>
            <label class="label">Kategori</label>
            <select wire:model="category_id" class="input">
                <option value="">— Pilih kategori —</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->name }}@if ($c->max_amount) (maks {{ rupiah($c->max_amount) }})@endif
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Judul</label>
            <input wire:model="title" class="input" placeholder="Contoh: Bensin meeting client">
            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Tanggal Pengeluaran</label>
            <input type="date" wire:model="expense_date" class="input" max="{{ now()->toDateString() }}">
            @error('expense_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Jumlah</label>
            <x-currency-input wire-model="amount" />
            @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="label">Deskripsi (opsional)</label>
            <textarea wire:model="description" rows="3" class="input" placeholder="Detail klaim…"></textarea>
        </div>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Kirim Klaim</span>
            <span wire:loading wire:target="submit">Mengirim…</span>
        </button>
    </form>
</div>
