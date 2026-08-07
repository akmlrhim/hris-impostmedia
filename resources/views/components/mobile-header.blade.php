@props(['title', 'subtitle' => null, 'back' => null])

{{-- Navy hero header for employee pages. Page content follows with `-mt-10`
     so the first card floats over the bottom of the hero. --}}
<div {{ $attributes->merge(['class' => 'hero px-5 pt-7 pb-16']) }}>
  <div class="flex items-center gap-3">
    @if ($back)
      <a wire:navigate href="{{ $back }}"
        class="w-9 h-9 -ml-1 rounded-full bg-white/15 flex items-center justify-center active:bg-white/25 transition shrink-0">
        <x-icon name="arrow-left" class="w-5 h-5" />
      </a>
    @endif
    <div class="flex-1 min-w-0">
      <h1 class="text-lg font-bold leading-tight truncate">{{ $title }}</h1>
      @if ($subtitle)
        <p class="text-xs text-navy-200 mt-0.5 truncate">{{ $subtitle }}</p>
      @endif
    </div>
    @isset($action)
      {{ $action }}
    @endisset
  </div>
</div>
