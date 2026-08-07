// ── PWA: Service Worker ──────────────────────────────────────────────────────
// Service worker HANYA aktif di build produksi. Di development SW membuat aset
// (CSS/JS) tersaji dari cache lama sehingga perubahan tidak pernah kelihatan,
// jadi registrasi lama dibersihkan total.
if ("serviceWorker" in navigator) {
    if (import.meta.env.PROD) {
        window.addEventListener("load", () => {
            navigator.serviceWorker
                .register("/sw.js", { scope: "/" })
                .catch((err) => console.warn("SW registration failed:", err));
        });
    } else {
        unregisterServiceWorkerForDev();
    }
}

async function unregisterServiceWorkerForDev() {
    try {
        const registrations =
            await navigator.serviceWorker.getRegistrations();

        if (registrations.length === 0) {
            return;
        }

        await Promise.all(registrations.map((r) => r.unregister()));

        if (window.caches) {
            const keys = await caches.keys();
            await Promise.all(keys.map((k) => caches.delete(k)));
        }

        // Halaman saat ini masih memakai respons dari SW lama (CSS bisa kosong),
        // jadi muat ulang sekali setelah pembersihan selesai.
        if (!sessionStorage.getItem("pwa:dev-sw-cleaned")) {
            sessionStorage.setItem("pwa:dev-sw-cleaned", "1");
            window.location.reload();
        }
    } catch (err) {
        console.warn("SW dev cleanup failed:", err);
    }
}

// ── PWA: Install Prompt ──────────────────────────────────────────────────────
let _deferredPrompt = null;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    _deferredPrompt = e;
    window.dispatchEvent(new CustomEvent("pwa:installable"));
});

window.addEventListener("appinstalled", () => {
    _deferredPrompt = null;
    window.dispatchEvent(new CustomEvent("pwa:installed"));
});

window.pwaCanInstall = () => Boolean(_deferredPrompt);

window.pwaInstall = async () => {
    if (!_deferredPrompt) return false;
    _deferredPrompt.prompt();
    const { outcome } = await _deferredPrompt.userChoice;
    _deferredPrompt = null;
    return outcome === "accepted";
};
