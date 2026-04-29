<x-layouts.app :title="$title ?? 'HRIS'">
    <div class="mobile-shell safe-bottom">
        <div class="px-4 pt-4">
            <x-alert-banner />
        </div>
        {{ $slot }}

        {{-- Bottom nav: floating dark pill with raised center action --}}
        <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-40 px-4"
             style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem);">
            @php
                $tabs = [
                    ['route' => 'mobile.home', 'label' => 'Beranda', 'icon' => 'home'],
                    ['route' => 'mobile.leave', 'label' => 'Cuti', 'icon' => 'calendar'],
                    ['route' => 'mobile.attendance', 'label' => 'Absen', 'icon' => 'clock', 'center' => true],
                    ['route' => 'mobile.payslip', 'label' => 'Slip', 'icon' => 'wallet'],
                    ['route' => 'mobile.profile', 'label' => 'Profil', 'icon' => 'user'],
                ];
            @endphp

            <nav class="relative bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/40">
                <div class="grid grid-cols-5 h-16 items-end">
                    @foreach ($tabs as $tab)
                        @php
                            $active = request()->routeIs($tab['route'].'*');
                            $isCenter = ! empty($tab['center']);
                        @endphp

                        @if ($isCenter)
                            <a href="{{ Route::has($tab['route']) ? route($tab['route']) : '#' }}"
                               class="relative flex flex-col items-center -mt-7">
                                <div class="w-14 h-14 rounded-full bg-white text-slate-900 flex items-center justify-center shadow-lg ring-4 ring-slate-900 transition active:scale-95">
                                    <x-icon :name="$tab['icon']" class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] mt-1 mb-2 {{ $active ? 'text-white font-medium' : 'text-slate-400' }}">{{ $tab['label'] }}</span>
                            </a>
                        @else
                            <a href="{{ Route::has($tab['route']) ? route($tab['route']) : '#' }}"
                               class="group flex flex-col items-center justify-center pt-2 pb-3 transition active:scale-95 {{ $active ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                                <span class="relative flex items-center justify-center">
                                    <x-icon :name="$tab['icon']" class="w-5 h-5" />
                                    @if ($active)
                                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-white"></span>
                                    @endif
                                </span>
                                <span class="text-[10px] mt-1 {{ $active ? 'font-medium' : '' }}">{{ $tab['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <x-confirm-modal />
</x-layouts.app>
