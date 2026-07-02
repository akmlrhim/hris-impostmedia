<x-layouts.app :title="$title ?? 'Dashboard'">
  <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-50">
    {{-- Backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
      class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden" style="display: none;"></div>

    {{-- Sidebar --}}
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transform transition-transform duration-200 ease-in-out lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200">
        <div class="flex items-center gap-2">
          <img src="{{ asset('logo.webp') }}" class="w-9 h-9 rounded-lg object-contain" alt="Logo">
          <span class="font-semibold text-lg text-slate-900">Impost Media</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded hover:bg-slate-100">
          <x-icon name="x" class="w-5 h-5 text-slate-500" />
        </button>
      </div>

      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
        @php
          $nav = [
              ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home', 'gate' => null],
              ['label' => 'Karyawan', 'route' => 'admin.employees', 'icon' => 'users', 'gate' => 'manage_employees'],
              [
                  'label' => 'Data Absensi',
                  'route' => 'admin.attendance',
                  'icon' => 'clock',
                  'gate' => 'manage_attendance',
              ],
              [
                  'label' => 'Pengajuan Remote',
                  'route' => 'admin.remote-work',
                  'icon' => 'laptop',
                  'gate' => 'manage_remote_work',
              ],
              [
                  'label' => 'Cuti & Izin',
                  'route' => 'admin.leave',
                  'icon' => 'calendar',
                  'gate' => 'manage_leave',
              ],
              ['label' => 'Payroll', 'route' => 'admin.payroll', 'icon' => 'wallet', 'gate' => 'manage_payroll'],
              [
                  'label' => 'Pengumuman',
                  'route' => 'admin.announcements',
                  'icon' => 'megaphone',
                  'gate' => 'manage_announcements',
              ],
              [
                  'label' => 'Lokasi Kantor',
                  'route' => 'admin.office-locations',
                  'icon' => 'map-pin',
                  'gate' => 'manage_office_locations',
              ],
              [
                  'label' => 'Hari Libur',
                  'route' => 'admin.holidays',
                  'icon' => 'flag',
                  'gate' => 'manage_holidays',
              ],
          ];
          $pendingWfa = auth()->user()?->can('manage_remote_work')
              ? \App\Models\RemoteWorkRequest::where('status', 'pending')->count()
              : 0;
          $pendingLeave = auth()->user()?->can('manage_leave')
              ? \App\Models\LeaveRequest::where('status', 'pending')->count()
              : 0;
          $myEmployee = auth()->user()?->employee;
          $myTodayAttendance = $myEmployee
              ? \App\Models\Attendance::where('employee_id', $myEmployee->id)
                  ->whereDate('attendance_date', today())
                  ->first()
              : null;
        @endphp

        @foreach ($nav as $item)
          @if (!$item['gate'] || auth()->user()?->can($item['gate']))
            @php $isActive = request()->routeIs($item['route'].'*'); @endphp
            <a wire:navigate href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
              class="font-medium flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $isActive ? 'bg-brand-600 text-white' : 'text-black hover:bg-slate-100 hover:text-slate-900' }}">
              <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
              <span class="flex-1">{{ $item['label'] }}</span>
              @if ($item['route'] === 'admin.remote-work' && $pendingWfa > 0)
                <span class="text-[10px] font-bold bg-amber-400 text-white rounded-full px-1.5 py-0.5 leading-none">
                  {{ $pendingWfa }}
                </span>
              @endif
              @if ($item['route'] === 'admin.leave' && $pendingLeave > 0)
                <span class="text-[10px] font-bold bg-amber-400 text-white rounded-full px-1.5 py-0.5 leading-none">
                  {{ $pendingLeave }}
                </span>
              @endif
            </a>
          @endif
        @endforeach

        {{-- Pengaturan (Admin only) --}}
        @can('manage_users')
          <div class="pt-3 mt-1 border-t border-slate-200 space-y-1">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Pengaturan</p>
            <a wire:navigate href="{{ route('admin.users') }}"
              class="font-medium flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.users') ? 'bg-brand-600 text-white' : 'text-black hover:bg-slate-100 hover:text-slate-900' }}">
              <x-icon name="users" class="w-5 h-5 shrink-0" />
              <span>Pengguna</span>
            </a>
            <a wire:navigate href="{{ route('admin.access-control') }}"
              class="font-medium flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.access-control') ? 'bg-brand-600 text-white' : 'text-black hover:bg-slate-100 hover:text-slate-900' }}">
              <x-icon name="shield-check" class="w-5 h-5 shrink-0" />
              <span>Hak Akses</span>
            </a>
            <a wire:navigate href="{{ route('admin.activity-log') }}"
              class="font-medium flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.activity-log') ? 'bg-brand-600 text-white' : 'text-black hover:bg-slate-100 hover:text-slate-900' }}">
              <x-icon name="clock" class="w-5 h-5 shrink-0" />
              <span>Log Aktivitas</span>
            </a>
          </div>
        @endcan

      </nav>

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
          <div x-data="{ title: '' }" x-init="title = document.title.split(' | ')[0] ?? '';
          document.addEventListener('livewire:navigate', () => {
              $nextTick(() => { title = document.title.split(' | ')[0] ?? ''; });
          });" class="min-w-0">
            <h1 class="text-sm font-semibold text-slate-800 truncate" x-text="title"></h1>
          </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">

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
              <a wire:navigate href="{{ route('admin.profile') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                <x-icon name="user" class="w-4 h-4" /> Profil Saya
              </a>
              @if (auth()->user()?->employee)
                <a wire:navigate href="{{ route('mobile.home') }}"
                  class="flex items-center gap-2 px-4 py-2 text-sm text-brand-600 hover:bg-brand-50">
                  <x-icon name="smartphone" class="w-4 h-4" /> Mode Karyawan
                </a>
              @endif
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
        {{ $slot }}
      </main>
    </div>

    <x-admin-toast />
    <x-confirm-modal />
  </div>
</x-layouts.app>
