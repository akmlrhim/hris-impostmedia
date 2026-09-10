@props(['align' => 'right'])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
  <button type="button" @click="open = !open"
    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
    <x-icon name="more-vertical" class="w-4 h-4" />
  </button>
  <div x-show="open" x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
    style="display: none;"
    class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-1 w-44 bg-white rounded-xl border border-slate-200 py-1 shadow-lg z-30">
    {{ $slot }}
  </div>
</div>
