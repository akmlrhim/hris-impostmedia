@props(['label', 'text', 'rejectText' => null])

<div x-data="{ open: false }" class="inline">
  <div class="flex items-start gap-2">
    <p class="text-slate-600 text-xs line-clamp-2 flex-1">{{ $text }}</p>
    <button type="button" @click="open = true"
      class="shrink-0 text-xs text-brand-600 hover:text-brand-700 font-medium whitespace-nowrap">
      Lihat
    </button>
  </div>
  @if ($rejectText)
    <p class="text-red-600 text-xs mt-1 italic">Ditolak: {{ $rejectText }}</p>
  @endif

  <template x-teleport="body">
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      @click="open = false" @keydown.escape.window="open = false"
      class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
      <div class="absolute inset-0 bg-slate-900/60"></div>
      <div @click.stop x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-sm font-semibold text-slate-900">{{ $label }}</h4>
          <button type="button" @click="open = false"
            class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 17.94 6M18 18 6.06 6"/>
            </svg>
          </button>
        </div>
        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $text }}</p>
        @if ($rejectText)
          <div class="mt-3 p-3 rounded-lg bg-red-50 border border-red-100">
            <p class="text-xs text-red-700">Ditolak: {{ $rejectText }}</p>
          </div>
        @endif
      </div>
    </div>
  </template>
</div>
