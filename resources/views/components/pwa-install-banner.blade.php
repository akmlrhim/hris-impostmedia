<div
    x-data="{
        show: false,
        ios: false,
        init() {
            // Detect iOS (Safari doesn't fire beforeinstallprompt)
            this.ios = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;

            // Already running as installed PWA — hide banner
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) return;

            // Don't show if dismissed within last 14 days
            const dismissed = localStorage.getItem('pwa-banner-dismissed');
            if (dismissed && Date.now() - parseInt(dismissed) < 14 * 86400000) return;

            if (this.ios) {
                // Show iOS tip after 3 seconds
                setTimeout(() => { this.show = true; }, 3000);
            } else {
                window.addEventListener('pwa:installable', () => { this.show = true; });
            }

            window.addEventListener('pwa:installed', () => { this.show = false; });
        },
        async install() {
            const accepted = await window.pwaInstall();
            if (accepted) this.show = false;
        },
        dismiss() {
            this.show = false;
            localStorage.setItem('pwa-banner-dismissed', Date.now().toString());
        }
    }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-0 left-0 right-0 z-50 p-4 md:bottom-6 md:left-auto md:right-6 md:max-w-sm"
>
    <div class="bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/50 p-4 flex items-start gap-3">
        <img src="/icons/icon-72.png" alt="HRIS" class="w-12 h-12 rounded-xl shrink-0">

        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">Install HRIS IM</p>

            <template x-if="!ios">
                <div>
                    <p class="text-xs text-slate-400 mt-0.5">Buka sebagai aplikasi tanpa browser.</p>
                    <div class="flex gap-2 mt-3">
                        <button @click="install"
                            class="flex-1 bg-white text-slate-900 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                            Install
                        </button>
                        <button @click="dismiss"
                            class="text-xs text-slate-400 px-3 py-1.5 rounded-lg hover:text-white transition">
                            Nanti
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="ios">
                <div>
                    <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                        Ketuk <strong class="text-white">
                            <svg class="inline w-3.5 h-3.5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </strong> lalu pilih <strong class="text-white">"Add to Home Screen"</strong>.
                    </p>
                    <button @click="dismiss"
                        class="mt-3 text-xs text-slate-400 hover:text-white transition">
                        Tutup
                    </button>
                </div>
            </template>
        </div>

        <button @click="dismiss" class="text-slate-500 hover:text-white transition shrink-0 -mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
