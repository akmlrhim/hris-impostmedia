<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title }} | {{ config('app.name') }}</title>

  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#1e293b">

  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="HRIS IM">
  <link rel="apple-touch-icon" href="/icons/icon-180.png?v=7">
  <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152.png?v=7">
  <link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144.png?v=7">
  <link rel="apple-touch-icon" sizes="128x128" href="/icons/icon-128.png?v=7">
  <link rel="apple-touch-icon" sizes="96x96" href="/icons/icon-96.png?v=7">

  <meta name="msapplication-TileImage" content="/icons/icon-144.png?v=7">
  <meta name="msapplication-TileColor" content="#1e293b">
  <meta name="msapplication-tap-highlight" content="no">

  <link rel="icon" type="image/x-icon" href="/favicon.ico?v=7" data-navigate-permanent>
  <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-96.png?v=7" data-navigate-permanent>
  <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png?v=7" data-navigate-permanent>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  @stack('head')
</head>

<body class="h-full">

  {{-- Landscape blocker: hanya aktif di perangkat sentuh (HP/tablet) saat landscape --}}
  <div id="landscape-blocker"
    class="fixed inset-0 z-[9999] bg-slate-900 text-white flex-col items-center justify-center gap-5 px-8 text-center hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-slate-400" fill="none" viewBox="0 0 24 24"
      stroke="currentColor" stroke-width="1.5">
      <rect x="5" y="2" width="14" height="20" rx="2" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 3c0 0 3-3 9-3s9 3 9 3" class="text-brand-400" />
    </svg>
    <div>
      <p class="text-lg font-semibold">Putar Perangkat Anda</p>
      <p class="text-sm text-slate-400 mt-1">Aplikasi ini dioptimalkan untuk tampilan <strong
          class="text-white">portrait</strong> (tegak).</p>
    </div>
  </div>

  <style>
    /* Hanya tampil di HP (bukan laptop/tablet) saat landscape: max-height 500px = ukuran HP di landscape */
    @media (orientation: landscape) and (hover: none) and (pointer: coarse) and (max-height: 500px) {
      #landscape-blocker {
        display: flex !important;
      }
    }
  </style>

  {{ $slot }}

  <x-pwa-install-banner />

  @livewireScripts
  @stack('scripts')

  <script>
    if (screen.orientation && screen.orientation.lock) {
      screen.orientation.lock('portrait').catch(() => {});
    }
  </script>

  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistrations().then(function(regs) {
        regs.forEach(function(reg) {
          reg.update();
        });
      });
      caches.keys().then(function(keys) {
        keys.forEach(function(key) {
          if (key !== 'hris-im-v7') caches.delete(key);
        });
      });
    }
  </script>

  <script data-navigate-once>
    document.addEventListener('livewire:navigated', () => {
      const titleEl = document.querySelector('title');
      if (titleEl) document.title = titleEl.textContent;
    });
  </script>
</body>

</html>
