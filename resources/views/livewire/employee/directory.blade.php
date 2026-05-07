<div>
    {{-- Header --}}
    <div class="bg-gradient-to-br from-brand-600 to-brand-800 text-white px-5 pt-8 pb-16 rounded-b-3xl">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('mobile.home') }}" class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center">
                <x-icon name="arrow-left" class="w-5 h-5 text-white" />
            </a>
            <div>
                <h1 class="text-lg font-bold">Direktori Karyawan</h1>
                <p class="text-xs text-brand-100">Cari & hubungi rekan kerja</p>
            </div>
        </div>
    </div>

    {{-- Search & filter --}}
    <div class="px-4 -mt-10 space-y-3">
        <div class="card p-3 space-y-2">
            <div class="relative">
                <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari nama, panggilan, atau no. telepon…"
                       class="input pl-9">
            </div>
            <select wire:model.live="position" class="input">
                <option value="">Semua Posisi</option>
                @foreach ($positions as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- List --}}
    <div class="px-4 mt-4 pb-32 space-y-2">
        @forelse ($employees as $emp)
            <div class="card p-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center font-semibold shrink-0 overflow-hidden">
                        @if ($emp->avatar_path)
                            <img src="{{ route('files.avatar', $emp) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 text-sm truncate">{{ $emp->full_name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $emp->position?->name ?? '—' }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            @if ($emp->phone)
                                <a href="tel:{{ $emp->phone }}"
                                   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 active:bg-emerald-100">
                                    <x-icon name="phone" class="w-3.5 h-3.5" /> Telepon
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $emp->phone)) }}"
                                   target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-green-50 text-green-700 active:bg-green-100">
                                    <x-icon name="smartphone" class="w-3.5 h-3.5" /> WA
                                </a>
                            @endif
                            @if ($emp->user?->email)
                                <a href="mailto:{{ $emp->user->email }}"
                                   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-sky-50 text-sky-700 active:bg-sky-100">
                                    <x-icon name="mail" class="w-3.5 h-3.5" /> Email
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center">
                <p class="text-sm text-slate-500">Tidak ada karyawan ditemukan.</p>
            </div>
        @endforelse

        @if ($employees->hasPages())
            <div class="pt-2">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
