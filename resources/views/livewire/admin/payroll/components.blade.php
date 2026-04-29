<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Komponen Payroll</h2>
            <p class="text-sm text-slate-500">Atur tunjangan, potongan, & rate yang digunakan saat generate slip gaji.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.payroll') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="w-4 h-4" /> Kembali
            </a>
            <button wire:click="open" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" /> Komponen Baru
            </button>
        </div>
    </div>

    @if ($showForm)
        <div class="card p-5">
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Kode</label>
                    <input wire:model="code" class="input uppercase" maxlength="30" placeholder="TRANS-ALW" {{ $editingId ? 'readonly' : '' }}>
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Nama</label>
                    <input wire:model="name" class="input" placeholder="Tunjangan Transport">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Tipe</label>
                    <select wire:model="type" class="input">
                        <option value="earning">Pendapatan</option>
                        <option value="deduction">Potongan</option>
                        <option value="tax">Pajak</option>
                    </select>
                </div>
                <div>
                    <label class="label">Cara Hitung</label>
                    <select wire:model.live="calculation_type" class="input">
                        <option value="fixed">Nominal Tetap</option>
                        <option value="percentage">Persentase dari Gaji Pokok</option>
                    </select>
                </div>
                <div class="md:col-span-2" wire:key="default-amount-{{ $calculation_type }}-{{ $editingId ?? 'new' }}">
                    <label class="label">Nilai Default</label>
                    @if ($calculation_type === 'percentage')
                        <div class="relative">
                            <input type="number" wire:model="default_amount" class="input pr-10" min="0" max="100" step="0.01" placeholder="0">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none">%</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Persentase dari gaji pokok (mis. 2 = 2%).</p>
                    @else
                        <x-currency-input wire-model="default_amount" />
                        <p class="text-xs text-slate-500 mt-1">Nominal tetap dalam rupiah.</p>
                    @endif
                    @error('default_amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_taxable" class="rounded border-slate-300">
                        Kena pajak (PPh 21)
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_bpjs_subject" class="rounded border-slate-300">
                        Subjek BPJS
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300">
                        Aktif
                    </label>
                </div>
                <div class="md:col-span-2 flex gap-2 justify-end">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Hitung</th>
                        <th class="px-5 py-3 text-right">Nilai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($components as $c)
                        @php
                            $reserved = in_array($c->code, ['BASIC', 'OT', 'ABSENT', 'PPH21']);
                            $typeColor = match ($c->type) {
                                'earning' => 'emerald',
                                'deduction' => 'rose',
                                'tax' => 'amber',
                                default => 'slate',
                            };
                            $typeLabel = match ($c->type) {
                                'earning' => 'Pendapatan',
                                'deduction' => 'Potongan',
                                'tax' => 'Pajak',
                                default => $c->type,
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs">
                                {{ $c->code }}
                                @if ($reserved)
                                    <span class="ml-1 text-[10px] text-amber-600">(sistem)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium">{{ $c->name }}</td>
                            <td class="px-5 py-3">
                                <span class="badge bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">{{ $typeLabel }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600 capitalize">
                                {{ $c->calculation_type === 'percentage' ? 'Persentase' : 'Nominal' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($c->calculation_type === 'percentage')
                                    {{ rtrim(rtrim(number_format((float) $c->default_amount, 2), '0'), '.') }}%
                                @else
                                    {{ rupiah($c->default_amount) }}
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($c->is_active)
                                    <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <button wire:click="open({{ $c->id }})" class="text-brand-600 hover:underline text-sm mr-3">Edit</button>
                                @if (! $reserved)
                                    <button wire:click="delete({{ $c->id }})" wire:confirm="Hapus komponen {{ $c->code }}?" class="text-red-600 hover:underline text-sm">Hapus</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada komponen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-xs text-slate-500">
        <strong>Catatan:</strong> Komponen sistem (<code>BASIC</code>, <code>OT</code>, <code>ABSENT</code>, <code>PPH21</code>) digunakan oleh generator dengan logika khusus dan tidak bisa dihapus.
        Komponen lain dengan <em>tipe pendapatan / potongan</em> dan status aktif akan otomatis ditambahkan saat generate periode payroll.
    </div>
</div>
