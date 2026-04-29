<x-layouts.app :title="$title ?? 'Dashboard'">
  <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-50">
    {{-- Backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
      class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden" style="display: none;"></div>

    {{-- Sidebar --}}
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 flex flex-col transform transition-transform duration-200 ease-in-out lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center font-bold text-white">H</div>
          <span class="font-semibold text-lg">{{ config('app.name', 'HRIS') }}</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded hover:bg-slate-800">
          <x-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
        @php
          $nav = [
              ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
              ['label' => 'Karyawan', 'route' => 'admin.employees', 'icon' => 'users'],
              ['label' => 'Absensi', 'route' => 'admin.attendance', 'icon' => 'clock'],
              ['label' => 'Cuti & Izin', 'route' => 'admin.leave', 'icon' => 'calendar'],
              ['label' => 'Lembur', 'route' => 'admin.overtime', 'icon' => 'sun'],
              ['label' => 'Reimbursement', 'route' => 'admin.reimbursement', 'icon' => 'receipt'],
              ['label' => 'Shift & Jadwal', 'route' => 'admin.shift', 'icon' => 'layers'],
              ['label' => 'Payroll', 'route' => 'admin.payroll', 'icon' => 'wallet'],
              ['label' => 'Pengumuman', 'route' => 'admin.announcements', 'icon' => 'megaphone'],
          ];
        @endphp

        @foreach ($nav as $item)
          @php $isActive = request()->routeIs($item['route'].'*'); @endphp
          <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $isActive ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
            <span>{{ $item['label'] }}</span>
          </a>
        @endforeach
      </nav>

      <a href="{{ route('admin.profile') }}"
        class="p-4 border-t border-slate-800 hover:bg-slate-800 transition flex items-center gap-3 {{ request()->routeIs('admin.profile') ? 'bg-slate-800' : '' }}">
        <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold shrink-0">
          {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate">{{ auth()->user()?->name }}</p>
          <p class="text-xs text-slate-400 truncate">{{ auth()->user()?->role?->label() }}</p>
        </div>
        <x-icon name="chevron-right" class="w-4 h-4 text-slate-500 shrink-0" />
      </a>
    </aside>

    {{-- Content --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">
      <header
        class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded hover:bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <button class="relative p-2 rounded-lg hover:bg-slate-100">
            <x-icon name="bell" class="w-5 h-5 text-slate-600" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>

          <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false"
              class="flex items-center gap-2 p-1 pr-2 rounded-lg hover:bg-slate-100">
              <div
                class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-semibold">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
              </div>
              <span class="hidden sm:inline text-sm text-slate-700">{{ auth()->user()?->name }}</span>
            </button>

            <div x-show="open" x-transition
              class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-30"
              style="display: none;">
              <div class="px-4 py-2 border-b border-slate-100">
                <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ auth()->user()?->email }}</p>
              </div>
              <a href="{{ route('admin.profile') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                <x-icon name="user" class="w-4 h-4" /> Profil Saya
              </a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                  class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <x-icon name="log-out" class="w-4 h-4" /> Keluar
                </button>
              </form>
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 p-4 sm:p-6">
        <x-alert-banner />
        {{ $slot }}
      </main>
    </div>

    <x-confirm-modal />
  </div>
</x-layouts.app>
