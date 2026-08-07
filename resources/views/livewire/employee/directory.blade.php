<div>
    {{-- Header --}}
    <x-mobile-header title="Direktori Karyawan" subtitle="Cari & hubungi rekan kerja" :back="route('mobile.home')" />

    {{-- Search --}}
    <div class="px-4 -mt-10">
        <div class="card-float p-3">
            <div class="relative">
                <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari nama, panggilan, atau no. telepon…"
                       class="input pl-9">
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="px-4 mt-4 pb-32">
        @if ($employees->isEmpty())
            <div class="card-float p-8 text-center">
                <p class="text-sm text-slate-500">Tidak ada karyawan ditemukan.</p>
            </div>
        @else
            <div class="card-float overflow-hidden divide-y divide-slate-100">
                @foreach ($employees as $emp)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-navy-700 text-white flex items-center justify-center font-semibold shrink-0 overflow-hidden">
                            @if ($emp->avatar_path)
                                <img src="{{ route('files.avatar', $emp) }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 text-sm truncate">{{ $emp->full_name }}</p>
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
                @endforeach
            </div>

            @if ($employees->hasPages())
                <div class="pt-3">{{ $employees->links() }}</div>
            @endif
        @endif
    </div>
</div>
