@props([
    'show' => 'showForm',
    'title' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
    ];
    $widthClass = $widths[$maxWidth] ?? 'max-w-lg';
@endphp

<div x-data="{ open: @entangle($show) }"
     x-show="open"
     x-on:keydown.escape.window="@if ($closeable) open = false @endif"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60"
     style="display: none;">
    <div class="min-h-full flex items-start sm:items-center justify-center p-4 sm:p-6">
        <div x-show="open"
             @if ($closeable) @click.outside="open = false" @endif
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative bg-white rounded-xl shadow-xl w-full {{ $widthClass }}">

            @if ($title || $closeable)
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                    @if ($closeable)
                        <button type="button" @click="open = false"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 17.94 6M18 18 6.06 6"/>
                            </svg>
                            <span class="sr-only">Tutup</span>
                        </button>
                    @endif
                </div>
            @endif

            <div class="p-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
