<div x-data="{
    open: false,
    message: '',
    resolveFn: null,
    confirm() { this.close(true); },
    cancel() { this.close(false); },
    close(value) {
        this.open = false;
        const fn = this.resolveFn;
        this.resolveFn = null;
        if (fn) fn(value);
    },
    init() {
        window.addEventListener('confirm:open', (e) => {
            this.message = e.detail.message ?? 'Anda yakin?';
            this.resolveFn = e.detail.resolve;
            this.open = true;
        });
    },
}" x-show="open" x-on:keydown.escape.window="cancel()" @click.self="cancel()"
  class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4" style="display: none;">

  <div x-show="open" x-transition.scale.origin.center @click.stop
    class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-5 md:p-6">

    <button type="button" @click="cancel()"
      class="absolute top-3 right-3 w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">
      <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 17.94 6M18 18 6.06 6" />
      </svg>
      <span class="sr-only">Tutup</span>
    </button>

    <div class="p-2 md:p-3 text-center">
      <svg class="mx-auto mb-4 text-slate-400 w-12 h-12" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
      <h3 class="mb-6 text-slate-700 text-sm leading-relaxed" x-text="message"></h3>
      <div class="flex items-center justify-center gap-3">
        <button type="button" @click="confirm()"
          class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium leading-5 rounded-lg text-sm px-4 py-2.5 focus:outline-none">
          Ya, lanjut
        </button>
        <button type="button" @click="cancel()"
          class="text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 focus:ring-4 focus:ring-slate-200 font-medium leading-5 rounded-lg text-sm px-4 py-2.5 focus:outline-none">
          Batal
        </button>
      </div>
    </div>
  </div>
</div>
