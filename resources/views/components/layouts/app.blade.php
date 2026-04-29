<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'HRIS' }} — {{ config('app.name') }}</title>

  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0f172a">
  <link rel="apple-touch-icon" href="/icons/icon-192.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="h-full">
  {{ $slot }}

  @livewireScripts
</body>

</html>
