@props([
    'show' => 'showForm',
    'title' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $widths = [
        'sm'  => 'sm:max-w-sm',
        'md'  => 'sm:max-w-md',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
    ];
    $widthClass = $widths[$maxWidth] ?? 'sm:max-w-lg';
@endphp

{{--
  Mobile: bottom sheet flush against the screen edge (justify-end, no outer
  spacing, background bleeds into the safe area).
  Desktop (sm+): centered dialog.
--}}
<div x-data="{ open: @entangle($show) }"
     x-show="open"
     x-on:keydown.escape.window="@if ($closeable) open = false @endif"
     class="fixed inset-0 z-50 flex flex-col justify-end sm:items-center sm:justify-center"
     style="display: none;">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @if ($closeable) @click="open = false" @endif
         class="absolute inset-0 bg-slate-900/60"></div>

    {{-- Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="translate-y-full sm:translate-y-0 sm:opacity-0 sm:scale-95"
         x-transition:enter-end="translate-y-0 sm:opacity-100 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 sm:opacity-100 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:translate-y-0 sm:opacity-0 sm:scale-95"
         class="relative flex flex-col bg-white w-full {{ $widthClass }}
                rounded-t-2xl sm:rounded-xl sm:mx-4
                max-h-[92dvh] sm:max-h-[85dvh]">

        {{-- Drag handle (mobile only) --}}
        <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0">
            <div class="w-10 h-1 rounded-full bg-slate-300"></div>
        </div>

        {{-- Header --}}
        @if ($title || $closeable)
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 shrink-0">
                <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                @if ($closeable)
                    <button type="button" @click="open = false"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 17.94 6M18 18 6.06 6"/>
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        {{-- Body: scrolls on its own so the header stays put on long forms --}}
        <div class="flex-1 overflow-y-auto overscroll-contain px-5 pt-5 pb-sheet">
            {{ $slot }}
        </div>
    </div>
</div>
