<x-layouts.app :title="$title ?? 'App'">
  <div class="mobile-shell safe-bottom">
    @auth
      @if (!auth()->user()->hasVerifiedEmail())
        <div class="bg-amber-50 border-b border-amber-200 px-4 py-2.5 flex items-center gap-2 text-xs text-amber-800">
          <x-icon name="mail" class="w-4 h-4 shrink-0" />
          <span class="flex-1">Email Anda belum diverifikasi.</span>
          <a href="{{ route('verification.notice') }}" wire:navigate class="font-semibold underline shrink-0">Verifikasi</a>
        </div>
      @endif
    @endauth

    {{ $slot }}

    {{-- Bottom nav: flush bar docked to the screen edge --}}
    <nav
      class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-40 bg-slate-900 text-white border-t border-slate-800"
      style="padding-bottom: env(safe-area-inset-bottom);">
      @php
        $tabs = [
            ['route' => 'mobile.home', 'label' => 'Beranda', 'icon' => 'home'],
            ['route' => 'mobile.directory', 'label' => 'Direktori', 'icon' => 'users'],
            ['route' => 'mobile.calendar', 'label' => 'Kalender', 'icon' => 'calendar-days'],
            ['route' => 'mobile.payslip', 'label' => 'Slip', 'icon' => 'wallet'],
            ['route' => 'mobile.profile', 'label' => 'Profil', 'icon' => 'user'],
        ];
      @endphp

      <div class="grid grid-cols-5 h-16">
        @foreach ($tabs as $tab)
          @php $active = request()->routeIs($tab['route'] . '*'); @endphp
          <a wire:navigate href="{{ Route::has($tab['route']) ? route($tab['route']) : '#' }}"
            class="flex flex-col items-center justify-center gap-1 transition active:scale-95 {{ $active ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
            <span class="relative flex items-center justify-center">
              <x-icon :name="$tab['icon']" class="w-5 h-5" />
              @if ($active)
                <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-white"></span>
              @endif
            </span>
            <span class="text-[10px] {{ $active ? 'font-medium' : '' }}">{{ $tab['label'] }}</span>
          </a>
        @endforeach
      </div>
    </nav>
  </div>

  <x-mobile-toast />
  <x-confirm-modal />
</x-layouts.app>
