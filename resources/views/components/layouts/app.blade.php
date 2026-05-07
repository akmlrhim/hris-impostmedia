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
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="h-full">
  {{ $slot }}

  {{-- PWA install banner --}}
  <x-pwa-install-banner />

  @livewireScripts
</body>

</html>
