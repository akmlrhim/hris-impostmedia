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
  <link rel="apple-touch-icon" href="/icons/icon-180.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144.png">
  <link rel="apple-touch-icon" sizes="128x128" href="/icons/icon-128.png">
  <link rel="apple-touch-icon" sizes="96x96" href="/icons/icon-96.png">

  <meta name="msapplication-TileImage" content="/icons/icon-144.png">
  <meta name="msapplication-TileColor" content="#1e293b">
  <meta name="msapplication-tap-highlight" content="no">

  <link rel="icon" type="image/x-icon" href="/favicon.ico" data-navigate-permanent>
  <link rel="icon" type="image/png" sizes="96x96" href="/icons/icon-96.png" data-navigate-permanent>
  <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png" data-navigate-permanent>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  @stack('head')
</head>

<body class="h-full">
  {{ $slot }}

  {{-- PWA install banner --}}
  <x-pwa-install-banner />

  @livewireScripts
  @stack('scripts')

  {{-- Paksa update service worker + hapus semua cache lama --}}
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistrations().then(function(regs) {
        regs.forEach(function(reg) { reg.update(); });
      });
      caches.keys().then(function(keys) {
        keys.forEach(function(key) {
          if (key !== 'hris-im-v6') caches.delete(key);
        });
      });
    }
  </script>

  {{-- Pastikan browser tab title ter-update saat wire:navigate --}}
  <script data-navigate-once>
    document.addEventListener('livewire:navigated', () => {
      const titleEl = document.querySelector('title');
      if (titleEl) document.title = titleEl.textContent;
    });
  </script>
</body>

</html>
