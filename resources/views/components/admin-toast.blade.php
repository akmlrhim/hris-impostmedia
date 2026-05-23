@php
  // Fallback: petakan session flash → toast (jika ada controller yang masih pakai session flash).
  // Aliases: 'error' → 'danger'.
  $flash = null;
  foreach (['success', 'danger', 'warning', 'info', 'dark'] as $type) {
      if (session()->has($type)) {
          $flash = ['type' => $type, 'message' => (string) session($type)];
          break;
      }
  }
  if (!$flash && session()->has('error')) {
      $flash = ['type' => 'danger', 'message' => (string) session('error')];
  }
@endphp

<div x-data="{
    visible: @js($flash !== null),
    type: @js($flash['type'] ?? 'info'),
    message: @js($flash['message'] ?? ''),
    timer: null,
    classes: {
        success: 'bg-emerald-600 text-white',
        danger: 'bg-red-600 text-white',
        warning: 'bg-amber-500 text-white',
        info: 'bg-slate-800 text-white',
        dark: 'bg-slate-900 text-white',
    },
    icons: {
        success: 'check',
        danger: 'x',
        warning: 'alert',
        info: 'info',
        dark: 'info',
    },
    show(rawType, message) {
        const aliases = { error: 'danger' };
        const type = aliases[rawType] ?? rawType;
        this.type = ['success', 'danger', 'warning', 'info', 'dark'].includes(type) ? type : 'info';
        this.message = message ?? '';
        this.visible = true;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => { this.visible = false; }, 3500);
    },
    init() {
        if (this.visible) {
            this.timer = setTimeout(() => { this.visible = false; }, 3500);
        }
        Livewire.on('notify', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            if (!data) return;
            this.show(data.type ?? 'success', data.message ?? '');
        });
    },
}" x-show="visible" x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
  x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
  x-transition:leave-end="opacity-0 translate-y-2" @click="visible = false; clearTimeout(timer)" role="status"
  aria-live="polite"
  class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 w-[calc(100%-2rem)] sm:w-auto sm:max-w-md"
  style="display: none;">
  <div :class="classes[type]"
    class="flex items-center gap-2.5 pl-3 pr-4 py-3 rounded-lg shadow-lg shadow-slate-900/20 text-sm cursor-pointer">
    <template x-if="icons[type] === 'check'">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
    </template>
    <template x-if="icons[type] === 'x'">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
      </svg>
    </template>
    <template x-if="icons[type] === 'alert'">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3m0 3h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
      </svg>
    </template>
    <template x-if="icons[type] === 'info'">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2.5">
        <circle cx="12" cy="12" r="9" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1" />
      </svg>
    </template>
    <span class="flex-1 font-medium" x-text="message"></span>
  </div>
</div>
